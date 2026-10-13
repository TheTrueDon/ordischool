<?php
declare(strict_types=1);

namespace OrdiSchool\School;

use DateTimeImmutable;
use OrdiSchool\Auth\PasswordHasher;
use OrdiSchool\Http\Html;
use OrdiSchool\Security\Csrf;
use PDO;
use RuntimeException;
use Throwable;

final class AdminController
{
    private SchoolRepository $schools;
    private int $userId;
    private string $globalRole;
    private string $username;
    private string $token;
    private string $prefix;

    public function __construct(private readonly PDO $pdo, string $prefix, array $user)
    {
        $this->prefix = $prefix;
        $this->schools = new SchoolRepository($pdo, $prefix);
        $this->userId = (int) $user['id'];
        $this->globalRole = (string) $user['role'];
        $this->username = (string) $user['username'];
        $this->token = Html::escape(Csrf::token());
    }

    public function handle(string $path, string $method): never
    {
        if ($method === 'POST') {
            $this->handlePost($path);
        }
        if ($method !== 'GET') {
            $this->fail(405, 'Methode nicht erlaubt', 'Diese Methode wird nicht unterstützt.');
        }
        if ($path === '/admin/schools') {
            $this->schoolsPage();
        }
        if ($path === '/admin/members') { $this->membersPage(); }
        if ($path === '/admin/setup') { $this->setupPage(); }
        if ($path === '/admin/students') { $this->studentsPage(); }
        if ($path === '/admin/roles') { $this->rolesPage(); }
        $this->fail(404, 'Seite nicht gefunden', 'Diese Verwaltungsseite ist nicht vorhanden.');
    }

    private function handlePost(string $path): never
    {
        if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
            $this->fail(400, 'Anfrage abgelaufen', 'Bitte lade die Seite neu und versuche es erneut.');
        }
        try {
            if ($path === '/admin/schools/create') {
                if ($this->globalRole !== 'system_admin') {
                    $this->fail(403, 'Zugriff verweigert', 'Für diese Aktion fehlt die Berechtigung.');
                }
                $code = strtolower(trim($this->input('code')));
                $name = trim($this->input('name'));
                $types = ['gymnasium', 'regelschule', 'grundschule', 'gemeinschaftsschule', 'berufsbildende_schule', 'sonstige'];
                $selectedTypes = is_array($_POST['school_offering_keys'] ?? null) ? array_values(array_filter($_POST['school_offering_keys'], 'is_string')) : [];
                $selectedTypes = array_values(array_unique($selectedTypes));
                $type = $selectedTypes[0] ?? '';
                $state = trim($this->input('federal_state'));
                if (!preg_match('/\A[a-z0-9_-]{2,64}\z/', $code) || $name === '' || strlen($name) > 190 || $selectedTypes === [] || array_diff($selectedTypes, $types) !== [] || $state === '' || strlen($state) > 100) {
                    throw new AdminInputException('Bitte prüfe Schulkennung, Schulname, Schulformen/Bildungsgänge und Bundesland.');
                }
                $schoolId = $this->schools->createSchool($code, $name, $type, $state, $this->userId, $selectedTypes);
                $this->redirect('/admin/setup?school_id=' . $schoolId . '&welcome=1');
            }
            if ($path === '/admin/years/create') {
                $schoolId = $this->int('school_id');
                $this->authorizeSchool($schoolId);
                $label = trim($this->input('label'));
                $start = $this->input('starts_on');
                $end = $this->input('ends_on');
                $status = $this->input('status');
                $dateValid = static function (string $date): bool {
                    if (!preg_match('/\A\d{4}-\d{2}-\d{2}\z/', $date)) return false;
                    $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
                    return $parsed instanceof DateTimeImmutable && $parsed->format('Y-m-d') === $date;
                };
                if ($label === '' || strlen($label) > 32 || !$dateValid($start) || !$dateValid($end) || $end < $start || !in_array($status, ['planned', 'current', 'archived'], true)) {
                    throw new AdminInputException('Bitte prüfe Bezeichnung, Zeitraum und Status des Schuljahres.');
                }
                $this->schools->createSchoolYear($schoolId, $label, $start, $end, $status, $this->userId);
                $this->redirect('/admin/setup?school_id=' . $schoolId);
            }
            if ($path === '/admin/members/create') {
                $schoolId = $this->int('school_id');
                $this->authorizeSchool($schoolId);
                $this->authorizePermission($schoolId, 'members.manage');
                $this->authorizePermission($schoolId, 'roles.manage');
                $newUsername = strtolower(trim($this->input('username'))); $password = $this->input('password');
                $first = trim($this->input('first_name')); $last = trim($this->input('last_name'));
                if (!preg_match('/\A[a-z0-9._-]{3,64}\z/', $newUsername) || !PasswordHasher::meetsMinimum($password) || $first === '' || $last === '' || strlen($first)>100 || strlen($last)>100) throw new AdminInputException('Bitte prüfe Vorname, Nachname, Benutzername und Startpasswort (mindestens 14 Zeichen).');
                $assignments = $this->readAssignments(); if ($assignments === []) throw new AdminInputException('Wähle mindestens eine Rolle und einen Geltungsbereich.');
                $this->schools->createMember($newUsername, PasswordHasher::hash($password), $schoolId, $first, $last, $assignments, $this->userId);
                $this->redirect('/admin/members?school_id=' . $schoolId . '&created=1');
            }
            if ($path === '/admin/members/roles') {
                $schoolId=$this->int('school_id'); $this->authorizePermission($schoolId,'roles.manage');
                $this->schools->setMemberRoles($schoolId,$this->int('membership_id'),$this->readAssignments(),$this->userId);
                $this->redirect('/admin/members?school_id='.$schoolId.'&roles_saved=1');
            }
            if ($path === '/admin/roles/create') {
                $schoolId=$this->int('school_id'); $this->authorizePermission($schoolId,'roles.manage');
                $key=strtolower(trim($this->input('role_key'))); $label=trim($this->input('role_name')); $description=trim($this->input('description'));
                if(!preg_match('/\A[a-z][a-z0-9_-]{1,31}\z/',$key)||$label===''||strlen($label)>120||strlen($description)>500) throw new AdminInputException('Bitte prüfe Rollenkennung, Bezeichnung und Beschreibung.');
                $this->schools->createRole($schoolId,$key,$label,$description,$this->userId); $this->redirect('/admin/roles?school_id='.$schoolId.'&created=1');
            }
            if ($path === '/admin/roles/permissions') {
                $schoolId=$this->int('school_id'); $this->authorizePermission($schoolId,'roles.manage');
                $role=$this->input('role_key'); $permissions=is_array($_POST['permissions']??null)?array_values(array_filter($_POST['permissions'],'is_string')):[];
                $this->schools->saveRolePermissions($schoolId,$role,$permissions,$this->userId); $this->redirect('/admin/roles?school_id='.$schoolId.'&saved=1');
            }
            if ($path === '/admin/students/create') {
                $schoolId=$this->int('school_id'); $this->authorizePermission($schoolId,'students.manage');
                $first=trim($this->input('first_name'));$last=trim($this->input('last_name'));$classRaw=$this->input('class_id');$classId=$classRaw===''?null:filter_var($classRaw,FILTER_VALIDATE_INT);
                if($first===''||$last===''||strlen($first)>100||strlen($last)>100||($classRaw!==''&&($classId===false||$classId<1)))throw new AdminInputException('Bitte prüfe Vorname, Nachname und Klasse.');
                $this->schools->createPerson($schoolId,'student',$first,$last,$classId===false?null:$classId,$this->userId);$this->redirect('/admin/students?school_id='.$schoolId.'&created=1');
            }
            if ($path === '/admin/members/status') {
                $schoolId = $this->int('school_id');
                $membershipId = $this->int('membership_id');
                $active = $this->input('active') === '1';
                $this->authorizeSchool($schoolId);
                $this->authorizePermission($schoolId, 'members.manage');
                $this->schools->setMembershipActive($membershipId, $active, $this->userId, $schoolId);
                $this->redirect('/admin/members?school_id=' . $schoolId);
            }
            $this->fail(404, 'Seite nicht gefunden', 'Diese Aktion ist nicht vorhanden.');
        } catch (AdminInputException $exception) {
            $this->fail(400, 'Eingabe konnte nicht gespeichert werden', $exception->getMessage());
        } catch (Throwable $exception) {
            $reference = bin2hex(random_bytes(6));
            error_log('OrdiSchool admin operation failure ' . $reference . ' (' . get_class($exception) . ')');
            $this->fail(500, 'Änderung fehlgeschlagen', 'Die Daten konnten nicht gespeichert werden. Prüfe Eingaben und Serverprotokoll.', $reference);
        }
    }

    private function schoolsPage(): never
    {
        if ($this->globalRole === 'system_admin') {
            $schoolRows = $this->schools->listSchools();
        } else {
            $schoolRows = $this->schools->schoolsForUser($this->userId, $this->globalRole);
            if ($schoolRows === []) {
                $this->fail(403, 'Zugriff verweigert', 'Du hast keine Schuladministration.');
            }
        }
        $body = '<section class="card"><p class="eyebrow">Stammdaten</p><h1>Schulverwaltung</h1><p><a href="/">← Dashboard</a></p>';
        if ($this->globalRole === 'system_admin') {
            $body .= '<h2>Schule anlegen</h2><form method="post" action="/admin/schools/create"><input type="hidden" name="csrf_token" value="' . $this->token . '"><label>Schulkennung</label><input name="code" maxlength="64" pattern="[a-z0-9_-]{2,64}" required><label>Name</label><input name="name" maxlength="190" required><fieldset><legend>Angebotene Schulformen/Bildungsgänge</legend><label><input class="inline-check" type="checkbox" name="school_offering_keys[]" value="gymnasium">Gymnasium</label><label><input class="inline-check" type="checkbox" name="school_offering_keys[]" value="regelschule">Regelschule</label><label><input class="inline-check" type="checkbox" name="school_offering_keys[]" value="grundschule">Grundschule</label><label><input class="inline-check" type="checkbox" name="school_offering_keys[]" value="gemeinschaftsschule">Gemeinschaftsschule</label><label><input class="inline-check" type="checkbox" name="school_offering_keys[]" value="berufsbildende_schule">Berufsbildende Schule</label><label><input class="inline-check" type="checkbox" name="school_offering_keys[]" value="sonstige">Sonstige</label></fieldset><label>Bundesland</label><input name="federal_state" value="Thüringen" maxlength="100" required><button type="submit">Schule anlegen</button></form>';
        }
        foreach ($schoolRows as $school) {
            $schoolId = (int) $school['id'];
            $offerings = $this->schools->offeringNames($schoolId);
            $body .= '<hr><h2>' . $this->e((string) $school['name']) . '</h2><p class="muted">' . $this->e((string) $school['code']) . ' · ' . $this->e(implode(', ', $offerings) ?: 'Noch keine Schulformangebote') . ' · ' . $this->e((string) $school['federal_state']) . '</p><p><a href="/admin/setup?school_id=' . $schoolId . '">Einrichtung öffnen</a> · <a href="/admin/planning?school_id=' . $schoolId . '">Stundenplanung</a> · <a href="/admin/members?school_id=' . $schoolId . '">Personal und Konten</a> · <a href="/admin/students?school_id=' . $schoolId . '">Schüler</a> · <a href="/admin/roles?school_id=' . $schoolId . '">Rollen und Rechte</a></p><h3>Schuljahre</h3><ul>';
            foreach ($this->schools->listYears($schoolId) as $year) {
                $body .= '<li>' . $this->e((string) $year['label']) . ' (' . $this->e((string) $year['starts_on']) . ' bis ' . $this->e((string) $year['ends_on']) . ') · ' . $this->e((string) $year['status']) . '</li>';
            }
            $body .= '</ul><details><summary>Schuljahr anlegen</summary><form method="post" action="/admin/years/create"><input type="hidden" name="csrf_token" value="' . $this->token . '"><input type="hidden" name="school_id" value="' . $schoolId . '"><label>Bezeichnung</label><input name="label" maxlength="32" placeholder="2026/2027" required><label>Beginn</label><input type="date" name="starts_on" required><label>Ende</label><input type="date" name="ends_on" required><label>Status</label><select name="status"><option value="planned">Geplant</option><option value="current">Aktuell</option><option value="archived">Archiviert</option></select><button type="submit">Schuljahr speichern</button></form></details>';
        }
        $body .= '<p class="muted">Zugriffe sind serverseitig auf Systemadministration oder die eigene aktive Schuladministration beschränkt.</p></section>';
        Html::page('Schulverwaltung', $body);
        exit;
    }

    private function membersPage(): never
    {
        $schoolId = filter_var($_GET['school_id'] ?? null, FILTER_VALIDATE_INT);
        if ($schoolId === false || $schoolId === null || $schoolId < 1) {
            $this->fail(400, 'Eingabe ungültig', 'Die Schulkennung fehlt.');
        }
        $this->authorizeSchool((int) $schoolId);
        $this->authorizePermission((int)$schoolId, 'members.manage');
        $this->authorizePermission((int)$schoolId, 'roles.manage');
        $school = $this->schools->findSchool((int) $schoolId);
        if ($school === null) {
            $this->fail(404, 'Schule nicht gefunden', 'Die angeforderte Schule ist nicht vorhanden.');
        }
        $roles=$this->schools->roleDefinitions((int)$schoolId); $offerings=$this->offerings((int)$schoolId); $assignGrid=$this->assignmentGrid($roles,$offerings,[]);
        $created = ($_GET['created'] ?? '') === '1' ? '<p class="success" role="status">Konto wurde angelegt und der Schule zugeordnet.</p>' : '';
        $saved = ($_GET['roles_saved'] ?? '') === '1' ? '<p class="success" role="status">Rollen wurden aktualisiert.</p>' : '';
        $body = '<section class="card"><p class="eyebrow">Personal und Konten</p><h1>' . $this->e((string)$school['name']) . '</h1><p><a href="/admin/setup?school_id='.(int)$schoolId.'">← Einrichtung</a></p>' . $created.$saved . '<h2>Person mit Konto anlegen</h2><p class="muted">Eine Person kann mehrere Rollen erhalten. Rollen lassen sich schulweit oder für einzelne Schulformen zuordnen. Gebe Startpasswörter sicher weiter.</p><form method="post" action="/admin/members/create"><input type="hidden" name="csrf_token" value="'.$this->token.'"><input type="hidden" name="school_id" value="'.(int)$schoolId.'"><label>Vorname</label><input name="first_name" maxlength="100" required><label>Nachname</label><input name="last_name" maxlength="100" required><label>Benutzername</label><input name="username" minlength="3" maxlength="64" pattern="[a-zA-Z0-9._-]{3,64}" autocomplete="off" required><label>Startpasswort (mindestens 14 Zeichen)</label><input type="password" name="password" minlength="14" autocomplete="new-password" required>'.$assignGrid.'<button>Konto und Person anlegen</button></form><h2>Mitglieder</h2><div class="table-wrap"><table><thead><tr><th>Person</th><th>Benutzername</th><th>Rollen bearbeiten</th><th>Status</th><th>Aktion</th></tr></thead><tbody>';
        foreach($this->schools->listMembers((int)$schoolId) as $member){$active=$member['membership_status']==='active';$selected=[];foreach(array_filter(explode(',',(string)($member['roles']??''))) as $r){[$rk,$sc]=array_pad(explode(':',$r,2),2,'0');$selected[$rk][]=(int)$sc;}$body.='<tr><td>'.$this->e(trim((string)$member['first_name'].' '.(string)$member['last_name'])).'</td><td>'.$this->e((string)$member['username']).'</td><td><details><summary>Rollen ändern</summary><form method="post" action="/admin/members/roles"><input type="hidden" name="csrf_token" value="'.$this->token.'"><input type="hidden" name="school_id" value="'.(int)$schoolId.'"><input type="hidden" name="membership_id" value="'.(int)$member['membership_id'].'">'.$this->assignmentGrid($roles,$offerings,$selected).'<button class="secondary">Rollen speichern</button></form></details></td><td>'.($active?'Aktiv':'Inaktiv').'</td><td><form method="post" action="/admin/members/status"><input type="hidden" name="csrf_token" value="'.$this->token.'"><input type="hidden" name="school_id" value="'.(int)$schoolId.'"><input type="hidden" name="membership_id" value="'.(int)$member['membership_id'].'"><input type="hidden" name="active" value="'.($active?'0':'1').'"><button class="secondary">'.($active?'Sperren':'Reaktivieren').'</button></form></td></tr>';}
        $body.='</tbody></table></div><p class="muted">Konten und schulische Personendaten werden getrennt geführt; es werden hier nur notwendige Namens- und Kontodaten erfasst.</p></section>';
        Html::page('Konten und Rollen', $body);
        exit;
    }

    private function setupPage(): never
    {
        $schoolId=filter_var($_GET['school_id']??null,FILTER_VALIDATE_INT);if(!$schoolId||$schoolId<1)$this->fail(400,'Schule fehlt','Wähle zuerst eine Schule.');
        $this->authorizeSchool((int)$schoolId);$school=$this->schools->findSchool((int)$schoolId);if($school===null)$this->fail(404,'Schule nicht gefunden','Die Schule wurde nicht gefunden.');
        $counts=[];$tablePrefix=$this->prefix;foreach(['buildings'=>'school_buildings','rooms'=>'school_rooms','subjects'=>'school_subjects','staff'=>'school_people','classes'=>'school_classes','students'=>'school_people'] as $key=>$table){$sql="SELECT COUNT(*) FROM {$tablePrefix}{$table} WHERE school_id=:school_id".($key==='classes'?" AND class_leader_user_id IS NOT NULL AND deputy_class_leader_user_id IS NOT NULL":'').($key==='students'?" AND person_kind='student'":($key==='staff'?" AND person_kind='staff'":""));$st=$this->pdo->prepare($sql);$st->execute(['school_id'=>$schoolId]);$counts[$key]=(int)$st->fetchColumn();}
        $years=$this->schools->listYears((int)$schoolId);$body='<section class="card"><p class="eyebrow">Ersteinrichtung · v0.6.0</p><h1>'.$this->e((string)$school['name']).'</h1><p><a href="/admin/schools">← Schulverwaltung</a></p>'.(($_GET['welcome']??'')==='1'?'<p class="success" role="status">Die Schule ist angelegt. Dein Konto hat automatisch die Schuladministration erhalten.</p>':'').'<p>Arbeite die Punkte in dieser Reihenfolge ab. Die Stundenplanung wird erst nach den erforderlichen Stammdaten freigeschaltet.</p>' . (count($years)===0 ? '<details open><summary>Schuljahr anlegen (für Klassen erforderlich)</summary><form method="post" action="/admin/years/create"><input type="hidden" name="csrf_token" value="'.$this->token.'"><input type="hidden" name="school_id" value="'.$schoolId.'"><label>Bezeichnung</label><input name="label" maxlength="32" placeholder="2026/2027" required><label>Beginn</label><input type="date" name="starts_on" required><label>Ende</label><input type="date" name="ends_on" required><input type="hidden" name="status" value="current"><button>Schuljahr speichern</button></form></details>' : '<p>Schuljahr: '.$this->e((string)$years[0]['label']).'</p>') . '<ol><li>'.($counts['buildings']?'✓':'○').' Gebäude und Räume ('.$counts['buildings'].' Gebäude, '.$counts['rooms'].' Räume) · <a href="/admin/planning/settings?school_id='.$schoolId.'#rooms">Gebäude/Räume</a></li><li>'.($counts['subjects']?'✓':'○').' Fächer ('.$counts['subjects'].') · <a href="/admin/planning/settings?school_id='.$schoolId.'#subjects">Fächer</a></li><li>'.($counts['staff']?'✓':'○').' Personal und Rollen ('.$counts['staff'].' Personen) · <a href="/admin/members?school_id='.$schoolId.'">Personal und Konten</a></li><li>'.(count($years)?'✓':'○').' Schuljahr · <a href="/admin/schools">Schuljahre verwalten</a></li><li>'.($counts['classes']?'✓':'○').' Klassen mit Klassenleitung und Stellvertretung ('.$counts['classes'].') · <a href="/admin/planning/settings?school_id='.$schoolId.'">Klassen anlegen</a></li><li>'.($counts['students']?'✓':'○').' Schüler ('.$counts['students'].') · <a href="/admin/students?school_id='.$schoolId.'">Schüler zuordnen</a></li></ol><nav><a class="button-link" href="/admin/members?school_id='.$schoolId.'">Personalverwaltung</a> <a class="button-link secondary" href="/admin/roles?school_id='.$schoolId.'">Rollen und Rechte</a> <a class="button-link secondary" href="/admin/students?school_id='.$schoolId.'">Schülerverwaltung</a> <a class="button-link secondary" href="/admin/planning?school_id='.$schoolId.'">Stundenplan</a></nav><p class="muted">Räume, Fächer und Rollen sind schulweite Stammdaten. Sie müssen für die normale Planbearbeitung nicht geöffnet werden.</p></section>';
        Html::page('Schule einrichten',$body);exit;
    }

    private function studentsPage(): never
    {
        $schoolId=filter_var($_GET['school_id']??null,FILTER_VALIDATE_INT);if(!$schoolId||$schoolId<1)$this->fail(400,'Schule fehlt','Wähle zuerst eine Schule.');$this->authorizePermission((int)$schoolId,'students.manage');
        $school=$this->schools->findSchool((int)$schoolId);$years=$this->schools->listYears((int)$schoolId);$classes=[];$tablePrefix=$this->prefix;if($years!==[]){$yearId=(int)($years[0]['id']);$st=$this->pdo->prepare("SELECT id,class_code,grade_level FROM {$tablePrefix}school_classes WHERE school_id=:school_id AND school_year_id=:year_id AND is_active=1 ORDER BY grade_level,class_code");$st->execute(['school_id'=>$schoolId,'year_id'=>$yearId]);$classes=$st->fetchAll();}
        $body='<section class="card"><p class="eyebrow">Schülerverwaltung</p><h1>'.$this->e((string)($school['name']??'')).'</h1><p><a href="/admin/setup?school_id='.$schoolId.'">← Einrichtung</a></p>'.(($_GET['created']??'')==='1'?'<p class="success">Schüler/in wurde angelegt.</p>':'').'<p>Schüler können ohne eigenes Benutzerkonto angelegt und einer Klasse zugeordnet werden.</p><form method="post" action="/admin/students/create"><input type="hidden" name="csrf_token" value="'.$this->token.'"><input type="hidden" name="school_id" value="'.$schoolId.'"><label>Vorname</label><input name="first_name" maxlength="100" required><label>Nachname</label><input name="last_name" maxlength="100" required><label>Klasse (optional)</label><select name="class_id"><option value="">Noch keiner Klasse zugeordnet</option>';foreach($classes as $c)$body.='<option value="'.(int)$c['id'].'">'.$this->e((string)$c['grade_level'].' · '.$c['class_code']).'</option>';$body.='</select><button>Schüler/in anlegen</button></form><h2>Schülerinnen und Schüler</h2><ul>';foreach($this->schools->listPeople((int)$schoolId,'student') as $p)$body.='<li>'.$this->e(trim((string)$p['first_name'].' '.(string)$p['last_name'])).' · '.($p['class_code']?$this->e((string)$p['class_code']):'keiner Klasse zugeordnet').'</li>';$body.='</ul><p class="muted">Erfasst werden nur Name und optionale Klassenzuordnung; weitere Schülerdaten werden in dieser Version nicht erhoben.</p></section>';Html::page('Schülerverwaltung',$body);exit;
    }

    private function rolesPage(): never
    {
        $schoolId=filter_var($_GET['school_id']??null,FILTER_VALIDATE_INT);if(!$schoolId||$schoolId<1)$this->fail(400,'Schule fehlt','Wähle zuerst eine Schule.');$this->authorizePermission((int)$schoolId,'roles.manage');$school=$this->schools->findSchool((int)$schoolId);$roles=$this->schools->roleDefinitions((int)$schoolId);$catalog=$this->schools->permissionCatalog();$map=$this->schools->rolePermissionMap((int)$schoolId);
        $body='<section class="card"><p class="eyebrow">Rollen und Berechtigungen</p><h1>'.$this->e((string)($school['name']??'')).'</h1><p><a href="/admin/setup?school_id='.$schoolId.'">← Einrichtung</a></p><h2>Rolle anlegen</h2><form method="post" action="/admin/roles/create"><input type="hidden" name="csrf_token" value="'.$this->token.'"><input type="hidden" name="school_id" value="'.$schoolId.'"><label>Kennung</label><input name="role_key" pattern="[a-z][a-z0-9_-]{1,31}" required><label>Rollenname</label><input name="role_name" maxlength="120" required><label>Beschreibung (optional)</label><input name="description" maxlength="500"><button>Rolle anlegen</button></form><h2>Berechtigungsmatrix</h2><p>Häkchen bestimmen die Rechte einer Rolle. Änderungen gelten sofort für aktive Mitglieder.</p>';
        foreach($roles as $role){$key=(string)$role['role_key'];$body.='<details><summary>'.$this->e((string)$role['role_name']).($role['is_system']?' · Standardsystemrolle':' · eigene Rolle').'</summary><form method="post" action="/admin/roles/permissions"><input type="hidden" name="csrf_token" value="'.$this->token.'"><input type="hidden" name="school_id" value="'.$schoolId.'"><input type="hidden" name="role_key" value="'.$this->e($key).'"><div class="table-wrap"><table><thead><tr><th>Modul</th><th>Berechtigung</th><th>Erlaubt</th></tr></thead><tbody>';foreach($catalog as $perm){$checked=!empty($map[$key][$perm['permission_key']])?' checked':'';$body.='<tr><td>'.$this->e((string)$perm['module_key']).'</td><td><strong>'.$this->e((string)$perm['permission_name']).'</strong><br><small>'.$this->e((string)$perm['description']).'</small></td><td><input class="inline-check" type="checkbox" name="permissions[]" value="'.$this->e((string)$perm['permission_key']).'"'.$checked.' aria-label="'.$this->e((string)$role['role_name'].' '.$perm['permission_name']).'"></td></tr>';}$body.='</tbody></table></div><button>Berechtigungen speichern</button></form></details>';}
        $body.='<p class="muted">Das Installationskonto bleibt technische Systemadministration und erhält beim Anlegen einer Schule automatisch die schulweite Rolle „Schuladministration“.</p></section>';Html::page('Rollen und Rechte',$body);exit;
    }

    private function assignmentGrid(array $roles,array $offerings,array $selected): string
    {
        $html='<fieldset><legend>Rollen und Geltungsbereich (Mehrfachauswahl)</legend><div class="table-wrap"><table><thead><tr><th>Rolle</th><th>Gesamte Schule</th>';foreach($offerings as $off)$html.='<th>'.$this->e((string)$off['offering_name']).'</th>';$html.='</tr></thead><tbody>';foreach($roles as $role){$key=(string)$role['role_key'];$html.='<tr><th>'.$this->e((string)$role['role_name']).'</th>'.$this->assignmentCell($key,0,$selected);foreach($offerings as $off)$html.=$this->assignmentCell($key,(int)$off['id'],$selected);$html.='</tr>';}$html.='</tbody></table></div></fieldset>';return $html;
    }
    private function assignmentCell(string $role,int $scope,array $selected): string
    {
        $checked=in_array($scope,$selected[$role]??[],true)?' checked':'';return '<td><input class="inline-check" type="checkbox" name="assignments['.$this->e($role).'][]" value="'.$scope.'"'.$checked.' aria-label="'.$this->e($role.' '.$scope).'"></td>';
    }
    private function readAssignments(): array
    {
        $input=$_POST['assignments']??[];if(!is_array($input))return [];$out=[];foreach($input as $role=>$scopes){if(!is_string($role)||!preg_match('/\\A[a-z][a-z0-9_-]{1,63}\\z/',$role)||!is_array($scopes))continue;foreach($scopes as $scope){$id=filter_var($scope,FILTER_VALIDATE_INT);if($id!==false&&$id!==null&&$id>=0)$out[]=['role_key'=>$role,'scope_id'=>(int)$id];}}return $out;
    }
    private function offerings(int $schoolId): array
    {
        $stmt=$this->pdo->prepare("SELECT id,offering_name FROM ".$this->schoolsPrefix()."school_offerings WHERE school_id=:school_id AND status='active' ORDER BY offering_name");$stmt->execute(['school_id'=>$schoolId]);return $stmt->fetchAll();
    }
    private function schoolsPrefix(): string
    {
        return $this->prefix;
    }
    private function authorizePermission(int $schoolId,string $permission): void
    {
        if($schoolId<1||!$this->schools->hasPermission($this->userId,$this->globalRole,$schoolId,$permission))$this->fail(403,'Zugriff verweigert','Für diese Aktion fehlt die Berechtigung.');
    }

    private function authorizeSchool(int $schoolId): void
    {
        if ($schoolId < 1 || !$this->schools->canManageSchool($this->userId, $this->globalRole, $schoolId)) {
            $this->fail(403, 'Zugriff verweigert', 'Für diese Schule fehlt die Berechtigung.');
        }
    }

    private function input(string $name): string
    {
        return is_string($_POST[$name] ?? null) ? $_POST[$name] : '';
    }

    private function int(string $name): int
    {
        $value = filter_var($_POST[$name] ?? null, FILTER_VALIDATE_INT);
        if ($value === false || $value === null || $value < 1) {
            throw new AdminInputException('Eine benötigte Kennung ist ungültig.');
        }
        return $value;
    }

    private function e(string $value): string
    {
        return Html::escape($value);
    }

    private function redirect(string $url): never
    {
        header('Location: ' . $url, true, 303);
        exit;
    }

    private function fail(int $status, string $title, string $message, ?string $reference = null): never
    {
        http_response_code($status);
        $extra = $reference === null ? '' : '<p class="muted">Referenz: ' . $this->e($reference) . '</p>';
        Html::page($title, '<section class="card"><h1>' . $this->e($title) . '</h1><p>' . $this->e($message) . '</p>' . $extra . '</section>');
        exit;
    }
}

final class AdminInputException extends RuntimeException
{
}
