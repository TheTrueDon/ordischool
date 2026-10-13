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

    public function __construct(private readonly PDO $pdo, string $prefix, array $user)
    {
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
        if ($path === '/admin/members') {
            $this->membersPage();
        }
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
                $this->schools->createSchool($code, $name, $type, $state, $this->userId, $selectedTypes);
                $this->redirect('/admin/schools?created=1');
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
                $this->redirect('/admin/schools');
            }
            if ($path === '/admin/members/create') {
                $schoolId = $this->int('school_id');
                $this->authorizeSchool($schoolId);
                $newUsername = strtolower(trim($this->input('username')));
                $password = $this->input('password');
                $roleKey = $this->input('role_key');
                $allowed = ['planner', 'teacher', 'class_leader', 'subject_lead', 'office_staff', 'student'];
                if ($this->globalRole === 'system_admin') {
                    $allowed[] = 'school_admin';
                }
                if (!preg_match('/\A[a-z0-9._-]{3,64}\z/', $newUsername) || !PasswordHasher::meetsMinimum($password) || !in_array($roleKey, $allowed, true)) {
                    throw new AdminInputException('Bitte prüfe Benutzername, Passwort (mindestens 14 Zeichen) und Rolle.');
                }
                $this->schools->createMember($newUsername, PasswordHasher::hash($password), $schoolId, $roleKey, $this->userId);
                $this->redirect('/admin/members?school_id=' . $schoolId . '&created=1');
            }
            if ($path === '/admin/members/status') {
                $schoolId = $this->int('school_id');
                $membershipId = $this->int('membership_id');
                $active = $this->input('active') === '1';
                $this->authorizeSchool($schoolId);
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
            $schoolRows = $this->schools->schoolsForUser($this->userId);
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
            $body .= '<hr><h2>' . $this->e((string) $school['name']) . '</h2><p class="muted">' . $this->e((string) $school['code']) . ' · ' . $this->e(implode(', ', $offerings) ?: 'Noch keine Schulformangebote') . ' · ' . $this->e((string) $school['federal_state']) . '</p><p><a href="/admin/planning?school_id=' . $schoolId . '">Stundenplanung</a> · <a href="/admin/members?school_id=' . $schoolId . '">Konten und Rollen verwalten</a></p><h3>Schuljahre</h3><ul>';
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
        $school = $this->schools->findSchool((int) $schoolId);
        if ($school === null) {
            $this->fail(404, 'Schule nicht gefunden', 'Die angeforderte Schule ist nicht vorhanden.');
        }
        $roleLabels = ['school_admin'=>'Schuladministration','planner'=>'Planung','teacher'=>'Lehrkraft','class_leader'=>'Klassenleitung','subject_lead'=>'Fachleitung','office_staff'=>'Sekretariat','student'=>'Schüler/in'];
        $roleOptions = $this->globalRole === 'system_admin' ? $roleLabels : array_diff_key($roleLabels, ['school_admin'=>true]);
        $created = ($_GET['created'] ?? '') === '1' ? '<p class="success" role="status">Konto wurde angelegt und der Schule zugeordnet. Es steht unten in der Mitgliederliste.</p>' : '';
        $body = '<section class="card"><p class="eyebrow">Konten und Rollen</p><h1>' . $this->e((string) $school['name']) . '</h1><p><a href="/admin/schools">← Schulverwaltung</a></p>' . $created . '<h2>Konto hinzufügen</h2><p class="muted">Benutzername: 3–64 Zeichen (Buchstaben, Zahlen, Punkt, Unterstrich oder Bindestrich). Das Startpasswort muss mindestens 14 Zeichen haben und sicher übergeben werden. Nach dem Speichern erscheint das Konto unten in der Mitgliederliste. Ein Selbstbedienungs-Passwortwechsel ist noch nicht enthalten.</p><form method="post" action="/admin/members/create"><input type="hidden" name="csrf_token" value="' . $this->token . '"><input type="hidden" name="school_id" value="' . (int) $schoolId . '"><label>Benutzername</label><input name="username" minlength="3" maxlength="64" pattern="[a-zA-Z0-9._-]{3,64}" autocomplete="off" required><label>Startpasswort (mindestens 14 Zeichen)</label><input type="password" name="password" minlength="14" autocomplete="new-password" required><label>Rolle an dieser Schule</label><select name="role_key">';
        foreach ($roleOptions as $key => $label) {
            $body .= '<option value="' . $this->e($key) . '">' . $this->e($label) . '</option>';
        }
        $body .= '</select><button type="submit">Konto anlegen</button></form><h2>Mitglieder</h2><div class="table-wrap"><table><thead><tr><th>Benutzername</th><th>Rollen</th><th>Status</th><th>Aktion</th></tr></thead><tbody>';
        foreach ($this->schools->listMembers((int) $schoolId) as $member) {
            $active = $member['membership_status'] === 'active';
            $memberRoles = array_filter(explode(',', (string) ($member['roles'] ?? '')));
            $labels = array_map(static fn (string $role): string => $roleLabels[$role] ?? $role, $memberRoles);
            $body .= '<tr><td>' . $this->e((string) $member['username']) . '</td><td>' . $this->e(implode(', ', $labels) ?: '—') . '</td><td>' . ($active ? 'Aktiv' : 'Inaktiv') . '</td><td><form method="post" action="/admin/members/status"><input type="hidden" name="csrf_token" value="' . $this->token . '"><input type="hidden" name="school_id" value="' . (int) $schoolId . '"><input type="hidden" name="membership_id" value="' . (int) $member['membership_id'] . '"><input type="hidden" name="active" value="' . ($active ? '0' : '1') . '"><button class="secondary" type="submit">' . ($active ? 'Sperren' : 'Reaktivieren') . '</button></form></td></tr>';
        }
        $body .= '</tbody></table></div><p class="muted">Rollen gelten in dieser Version für die gesamte Schule. Klassen-, Kurs- und Fachbereiche folgen mit den Fachmodulen. Konto- und Mitgliedschaftsänderungen erhalten einen Audit-Eintrag.</p></section>';
        Html::page('Konten und Rollen', $body);
        exit;
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
