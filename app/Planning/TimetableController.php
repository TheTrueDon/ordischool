<?php
declare(strict_types=1);

namespace OrdiSchool\Planning;

use DateTimeImmutable;
use OrdiSchool\Http\Html;
use OrdiSchool\Security\Csrf;
use PDO;
use Throwable;

final class TimetableController
{
    private TimetableRepository $repo;
    private string $token;
    private int $userId;

    public function __construct(private readonly PDO $pdo, string $prefix, array $user)
    {
        $this->repo=new TimetableRepository($pdo,$prefix);
        $this->userId=(int)$user['id'];
        $this->token=Html::escape(Csrf::token());
    }

    public function handle(string $path,string $method): never
    {
        if($method==='POST')$this->handlePost($path);
        if($method!=='GET')$this->fail(405,'Methode nicht erlaubt','Diese Methode wird nicht unterstützt.');
        $this->page();
    }

    private function handlePost(string $path): never
    {
        if(!Csrf::verify($_POST['csrf_token']??null))$this->fail(400,'Anfrage abgelaufen','Bitte lade die Seite neu und versuche es erneut.');
        $schoolId=0;
        try{
            $schoolId=$this->int('school_id');
            if(!$this->repo->canPlan($this->userId,$schoolId))$this->fail(403,'Zugriff verweigert','Für diese Schule fehlt die Planungsberechtigung.');
            if($path==='/admin/planning/offering'){
                $key=strtolower(trim($this->input('offering_key')));
                $name=trim($this->input('offering_name'));
                $from=$this->input('valid_from');$until=$this->input('valid_until');
                if(!preg_match('/\A[a-z0-9_-]{2,64}\z/',$key)||$name===''||strlen($name)>560||!$this->validDate($from)||($until!==''&&(!$this->validDate($until)||$until<$from)))throw new InputException('Bitte prüfe Kennung, Bezeichnung und Gültigkeitszeitraum.');
                $this->repo->addOffering($schoolId,$key,$name,$from,$until===''?null:$until,$this->userId);
            }elseif($path==='/admin/planning/class'){
                $year=$this->int('year_id');$offering=$this->int('offering_id');$code=trim($this->input('class_code'));$grade=trim($this->input('grade_level'));$type=$this->input('group_type');
                if(!preg_match('/\A[A-Za-z0-9._ -]{1,64}\z/',$code)||$grade===''||strlen($grade)>128||!in_array($type,['class','course','group'],true))throw new InputException('Bitte prüfe Klassen-/Kurskennung, Jahrgang und Typ.');
                $expectedRaw=$this->input('expected_size');$expected=$expectedRaw===''?null:filter_var($expectedRaw,FILTER_VALIDATE_INT);if($expectedRaw!==''&&($expected===false||$expected<1||$expected>65535))throw new InputException('Bitte prüfe die Gruppengröße.');$this->repo->addClass($schoolId,$year,$offering,$code,$grade,$type,$this->userId,$expected===false?null:$expected);
            }elseif($path==='/admin/planning/class-size'){
                $class=$this->int('class_id');$sizeRaw=$this->input('expected_size');$size=$sizeRaw===''?null:filter_var($sizeRaw,FILTER_VALIDATE_INT);if($sizeRaw!==''&&($size===false||$size<1||$size>65535))throw new InputException('Bitte prüfe die Gruppengröße.');$this->repo->setClassExpectedSize($schoolId,$class,$size===false?null:$size,$this->userId);
            }elseif($path==='/admin/planning/subject'){
                $code=strtoupper(trim($this->input('subject_code')));$name=trim($this->input('subject_name'));
                if(!preg_match('/\A[A-Z0-9_-]{1,32}\z/',$code)||$name===''||strlen($name)>480)throw new InputException('Bitte prüfe Fachkürzel und Fachname.');
                $roomKey=strtolower(trim($this->input('preferred_room_type_key')));$roomName=trim($this->input('preferred_room_type_name'));if($roomKey!==''&&!preg_match('/\A[a-z0-9_-]{1,40}\z/',$roomKey))throw new InputException('Bitte prüfe die Raumtyp-Kennung.');if(strlen($roomName)>400)throw new InputException('Die Raumtyp-Bezeichnung ist zu lang.');$this->repo->addSubject($schoolId,$code,$name,$this->userId,$roomKey===''?null:$roomKey,$roomKey===''?null:($roomName===''?null:$roomName));
            }elseif($path==='/admin/planning/room'){
                $code=trim($this->input('room_code'));$name=trim($this->input('room_name'));$key=strtolower(trim($this->input('room_type_key')));$typeName=trim($this->input('room_type_name'));$capRaw=$this->input('capacity');$capacity=$capRaw===''?null:filter_var($capRaw,FILTER_VALIDATE_INT);
                if(!preg_match('/\A[A-Za-z0-9._-]{1,64}\z/',$code)||$name===''||strlen($name)>560||!preg_match('/\A[a-z0-9_-]{1,40}\z/',$key)||$typeName===''||strlen($typeName)>400||($capRaw!==''&&($capacity===false||$capacity<1||$capacity>65535)))throw new InputException('Bitte prüfe Raumkennung, Name, Raumtyp und Kapazität.');
                $this->repo->addRoom($schoolId,$code,$name,$key,$typeName,$capacity===false?null:$capacity,$this->userId);
            }elseif($path==='/admin/planning/subject-room-preference'){
                $subject=$this->int('subject_id');$key=strtolower(trim($this->input('preferred_room_type_key')));$typeName=trim($this->input('preferred_room_type_name'));
                if($key!==''&&!preg_match('/\A[a-z0-9_-]{1,40}\z/',$key))throw new InputException('Bitte prüfe die Raumtyp-Kennung.');if(strlen($typeName)>400)throw new InputException('Die Raumtyp-Bezeichnung ist zu lang.');
                $this->repo->setSubjectRoomPreference($schoolId,$subject,$key===''?null:$key,$key===''?null:($typeName===''?null:$typeName),$this->userId);
            }elseif($path==='/admin/planning/period'){
                $number=$this->int('period_number');$label=trim($this->input('period_label'));$start=$this->input('starts_at');$end=$this->input('ends_at');
                if($number>20||$label===''||strlen($label)>160||!$this->validTime($start)||!$this->validTime($end)||$end<=$start)throw new InputException('Bitte prüfe Stundennummer, Bezeichnung sowie Beginn und Ende.');
                $this->repo->addPeriod($schoolId,$number,$label,$start,$end,$this->userId);
            }elseif($path==='/admin/planning/draft'){
                $year=$this->int('year_id');$title=trim($this->input('title'));
                if($title===''||strlen($title)>560)throw new InputException('Bitte gib einen Titel mit höchstens 140 Zeichen ein.');
                $planId=$this->repo->createDraft($schoolId,$year,$title,$this->userId);
                $this->redirect('/admin/planning?school_id='.$schoolId.'&year_id='.$year.'&plan_id='.$planId.'&notice=draft');
            }elseif($path==='/admin/planning/revise'){
                $year=$this->int('year_id');$source=$this->int('plan_id');$title=trim($this->input('title'));
                if($title===''||strlen($title)>560)throw new InputException('Bitte gib einen Titel mit höchstens 140 Zeichen ein.');
                $planId=$this->repo->createDraft($schoolId,$year,$title,$this->userId,$source);
                $this->redirect('/admin/planning?school_id='.$schoolId.'&year_id='.$year.'&plan_id='.$planId.'&notice=draft');
            }elseif($path==='/admin/planning/lesson'){
                $year=$this->int('year_id');$planId=$this->int('plan_id');$class=$this->int('class_id');$subject=$this->int('subject_id');$teacher=$this->int('teacher_id');$weekday=$this->int('weekday');$period=$this->int('period_number');$duration=$this->int('duration_periods');$roomRaw=$this->input('room_id');$roomId=$roomRaw===''?null:filter_var($roomRaw,FILTER_VALIDATE_INT);if($roomRaw!==''&&($roomId===false||$roomId<1))throw new InputException('Bitte wähle einen gültigen Raum.');
                if($weekday<1||$weekday>5||$duration<1||$duration>2)throw new InputException('Bitte prüfe Wochentag und Dauer.');
                $this->repo->addLesson($schoolId,$planId,$class,$subject,$teacher,$weekday,$period,$duration,$this->userId,$roomId===false?null:$roomId);
                $this->redirect('/admin/planning?school_id='.$schoolId.'&year_id='.$year.'&plan_id='.$planId.'&notice=lesson');
            }elseif($path==='/admin/planning/remove-lesson'){
                $year=$this->int('year_id');$planId=$this->int('plan_id');$lessonId=$this->int('lesson_id');
                $this->repo->removeLesson($schoolId,$planId,$lessonId,$this->userId);
                $this->redirect('/admin/planning?school_id='.$schoolId.'&year_id='.$year.'&plan_id='.$planId.'&notice=removed');
            }elseif($path==='/admin/planning/publish'){
                $year=$this->int('year_id');$planId=$this->int('plan_id');
                if(!$this->repo->canPublish($this->userId,$schoolId))$this->fail(403,'Freigabe nicht möglich','Die Veröffentlichung erfordert eine aktive Schuladministrationsrolle an dieser Schule.');
                $this->repo->publish($schoolId,$planId,$this->userId);
                $this->redirect('/admin/planning?school_id='.$schoolId.'&year_id='.$year.'&plan_id='.$planId.'&notice=published');
            }else{$this->fail(404,'Seite nicht gefunden','Diese Planungsaktion ist nicht vorhanden.');}
            $year=(int)($_POST['year_id']??0);
            $query=$year>0?'&year_id='.$year:'';
            $this->redirect('/admin/planning?school_id='.$schoolId.$query.'&notice=saved');
        }catch(InputException $e){$this->fail(400,'Eingabe konnte nicht gespeichert werden',$e->getMessage());}
        catch(Throwable $e){
            $reference=bin2hex(random_bytes(6));
            error_log('OrdiSchool timetable operation failure '.$reference.' ('.get_class($e).')');
            $message=$e instanceof PlanningException?$e->getMessage():'Die Änderung konnte nicht gespeichert werden. Bitte prüfe die Eingaben oder Serverprotokolle.';
            $this->fail(400,'Änderung nicht möglich',$message,$reference);
        }
    }

    private function page(): never
    {
        $schools=$this->repo->schoolsForPlanner($this->userId);
        if($schools===[])$this->fail(403,'Keine Planungsberechtigung','Für dein Konto ist keine Schule mit Planungsrecht verfügbar.');
        $requested=filter_var($_GET['school_id']??null,FILTER_VALIDATE_INT);
        $schoolId=(int)($requested?:$schools[0]['id']);
        $school=null;
        foreach($schools as $candidate)if((int)$candidate['id']===$schoolId)$school=$candidate;
        if($school===null)$this->fail(403,'Zugriff verweigert','Für diese Schule fehlt die Planungsberechtigung.');
        $years=$this->repo->listYears($schoolId);
        $yearRequested=filter_var($_GET['year_id']??null,FILTER_VALIDATE_INT);
        $yearId=(int)($yearRequested?:($years[0]['id']??0));
        $year=null;foreach($years as $candidate)if((int)$candidate['id']===$yearId)$year=$candidate;
        if($yearId>0&&$year===null)$this->fail(404,'Schuljahr nicht gefunden','Das Schuljahr gehört nicht zu dieser Schule.');
        $offerings=$this->repo->listOfferings($schoolId);
        $periods=$this->repo->listPeriods($schoolId);
        $subjects=$this->repo->listSubjects($schoolId);
        $rooms=$this->repo->listRooms($schoolId);
        $classes=$yearId>0?$this->repo->listClasses($schoolId,$yearId):[];
        $teachers=$this->repo->listTeachers($schoolId);
        $plans=$yearId>0?$this->repo->listPlans($schoolId,$yearId):[];
        $planId=(int)(filter_var($_GET['plan_id']??null,FILTER_VALIDATE_INT)?:($plans[0]['id']??0));
        $plan=$planId>0?$this->repo->findPlan($schoolId,$planId):null;
        if($plan!==null&&(int)$plan['school_year_id']!==$yearId)$plan=null;
        if($plan===null&&$planId>0)$this->fail(404,'Plan nicht gefunden','Der ausgewählte Plan gehört nicht zu dieser Schule und diesem Schuljahr.');
        $lessons=$plan!==null?$this->repo->listLessons($schoolId,$planId):[];
        $canPublish=$this->repo->canPublish($this->userId,$schoolId);
        $notice=$this->notice((string)($_GET['notice']??''));
        $error=is_string($_GET['error']??null)?'<p class="error" role="alert">'.$this->e($_GET['error']).'</p>':'';
        $body='<section class="card"><p class="eyebrow">Planung v0.4.0</p><h1>Stundenplan</h1><p><a href="/">← Dashboard</a></p>'.$notice.$error;
        if(count($schools)>1){$body.='<form method="get"><label for="school_id">Schule</label><select id="school_id" name="school_id" onchange="this.form.submit()">';foreach($schools as $s)$body.='<option value="'.(int)$s['id'].'"'.((int)$s['id']===$schoolId?' selected':'').'>'.$this->e((string)$s['name']).'</option>';$body.='</select><noscript><button>Auswählen</button></noscript></form>';}
        $body.='<h2>'.$this->e((string)$school['name']).'</h2>';
        if($years!==[]){$body.='<form method="get"><input type="hidden" name="school_id" value="'.$schoolId.'"><label for="year_id">Schuljahr</label><select id="year_id" name="year_id" onchange="this.form.submit()">';foreach($years as $y)$body.='<option value="'.(int)$y['id'].'"'.((int)$y['id']===$yearId?' selected':'').'>'.$this->e((string)$y['label']).' · '.$this->e((string)$y['status']).'</option>';$body.='</select><noscript><button>Anzeigen</button></noscript></form>';}
        $body.=$this->setupForms($schoolId,$yearId,$offerings,$subjects,$periods,$rooms,$classes);
        if($yearId>0){
            $body.='<h2>Planfassungen '.$this->e((string)$year['label']).'</h2><form method="post" action="/admin/planning/draft" class="compact"><input type="hidden" name="csrf_token" value="'.$this->token.'"><input type="hidden" name="school_id" value="'.$schoolId.'"><input type="hidden" name="year_id" value="'.$yearId.'"><label>Titel für neuen Entwurf</label><input name="title" maxlength="140" placeholder="Stundenplan Entwurf 1" required><button>Entwurf anlegen</button></form>';
            if($plans!==[]){$body.='<div class="table-wrap"><table><thead><tr><th>Fassung</th><th>Status</th><th>Erstellt</th><th></th></tr></thead><tbody>';foreach($plans as $p){$body.='<tr><td>v'.(int)$p['version_number'].' · '.$this->e((string)$p['title']).'</td><td>'.$this->status((string)$p['status']).'</td><td>'.$this->e((string)$p['created_at']).'</td><td><a href="/admin/planning?school_id='.$schoolId.'&year_id='.$yearId.'&plan_id='.(int)$p['id'].'">Öffnen</a></td></tr>';if($p['status']==='published')$body.='<tr><td colspan="4"><form method="post" action="/admin/planning/revise" class="inline-form"><input type="hidden" name="csrf_token" value="'.$this->token.'"><input type="hidden" name="school_id" value="'.$schoolId.'"><input type="hidden" name="year_id" value="'.$yearId.'"><input type="hidden" name="plan_id" value="'.(int)$p['id'].'"><label>Neue Fassung aus v'.(int)$p['version_number'].' erstellen</label><input name="title" maxlength="140" placeholder="Änderungsentwurf" required><button class="secondary">Kopieren und bearbeiten</button></form></td></tr>';}$body.='</tbody></table></div>';}
            if($plan!==null)$body.=$this->planEditor($schoolId,$yearId,$plan,$lessons,$classes,$subjects,$teachers,$periods,$rooms,$canPublish);
        } else $body.='<p class="muted">Lege zuerst in der Schulverwaltung ein Schuljahr an.</p>';
        $body.='<p class="muted">v0.4.0 verwaltet Räume, Kapazitäten und unverbindliche Fachraumpräferenzen im gemeinsamen Stundenplan aller Schulformen. Raumkollisionen und bekannte Kapazitätsüberschreitungen blockieren die Eintragung/Freigabe; Präferenzabweichungen werden nur als Hinweis angezeigt. Bis zur fachlichen und betrieblichen Freigabe nur synthetische Testdaten verwenden.</p></section>';
        Html::page('Stundenplan', $body);exit;
    }

    private function setupForms(int $schoolId,int $yearId,array $offerings,array $subjects,array $periods,array $rooms,array $classes): string
    {
        $html='<details><summary>Planungsgrundlagen verwalten</summary><h3>Räume</h3><p class="muted">Raumtypen sind frei definierbar. Beispiele: chemie, physik, bio, kunst, musik, werken. Kapazität leer lassen, wenn sie unbekannt ist.</p><form method="post" action="/admin/planning/room">'.$this->hidden($schoolId,$yearId).'<label>Raumkennung</label><input name="room_code" maxlength="64" required><label>Raumname</label><input name="room_name" maxlength="140" required><label>Raumtyp-Kennung</label><input name="room_type_key" pattern="[a-z0-9_-]{1,40}" placeholder="chemie" required><label>Raumtyp-Bezeichnung</label><input name="room_type_name" maxlength="100" placeholder="Chemieraum" required><label>Kapazität (Personen, optional)</label><input type="number" name="capacity" min="1" max="65535"><button>Raum hinzufügen</button></form><ul>';foreach($rooms as $r)$html.='<li>'.$this->e((string)$r['room_code']).' · '.$this->e((string)$r['room_name']).' · '.$this->e((string)$r['room_type_name']).' · Kapazität: '.($r['capacity']===null?'unbekannt':(int)$r['capacity']).'</li>';$html.='</ul>'; 
        $html.='<h3>Schulformen und Bildungsgänge</h3><p class="muted">Eine Schule kann mehrere Angebote führen. Klassen und Kurse werden jeweils genau einem Angebot zugeordnet.</p><div class="grid">';
        $html.='<form method="post" action="/admin/planning/offering">'.$this->hidden($schoolId,$yearId).'<label>Kennung</label><input name="offering_key" pattern="[a-z0-9_-]{2,64}" placeholder="gymnasium" required><label>Bezeichnung</label><input name="offering_name" maxlength="140" placeholder="Gymnasium" required><label>Gültig ab</label><input type="date" name="valid_from" required><label>Gültig bis (optional)</label><input type="date" name="valid_until"><button>Angebot hinzufügen</button></form>';
        $html.='<div><strong>Vorhandene Angebote</strong><ul>';foreach($offerings as $o)$html.='<li>'.$this->e((string)$o['offering_name']).' · '.$this->e((string)$o['status']).'</li>';$html.='</ul></div></div>';
        if($yearId>0){$html.='<h3>Klassen und Lerngruppen</h3><form method="post" action="/admin/planning/class">'.$this->hidden($schoolId,$yearId).'<label>Angebot</label><select name="offering_id" required>'.$this->options($offerings,'id','offering_name').'</select><label>Kennung</label><input name="class_code" maxlength="64" placeholder="9a oder 10_Physik_KursA" required><label>Jahrgang/Klassenstufe</label><input name="grade_level" maxlength="32" placeholder="9" required><label>Typ</label><select name="group_type"><option value="class">Klasse</option><option value="course">Kurs</option><option value="group">Lerngruppe</option></select><label>Erwartete Gruppengröße (optional)</label><input type="number" name="expected_size" min="1" max="65535"><button>Klasse/Kurs speichern</button></form>';if($classes!==[]){$html.='<h4>Gruppengrößen vorhandener Klassen/Kurse</h4><div class="grid">';foreach($classes as $class){$html.='<form method="post" action="/admin/planning/class-size">'.$this->hidden($schoolId,$yearId).'<input type="hidden" name="class_id" value="'.(int)$class['id'].'"><strong>'.$this->e((string)$class['offering_name']).' · '.$this->e((string)$class['class_code']).'</strong><label>Erwartete Gruppengröße (leer = unbekannt)</label><input type="number" name="expected_size" min="1" max="65535" value="'.($class['expected_size']===null?'':(int)$class['expected_size']).'"><button class="secondary">Größe speichern</button></form>';}$html.='</div>';}}
        $html.='<h3>Fächer</h3><form method="post" action="/admin/planning/subject">'.$this->hidden($schoolId,$yearId).'<label>Kürzel</label><input name="subject_code" maxlength="32" placeholder="PHY" required><label>Fachname</label><input name="subject_name" maxlength="120" placeholder="Physik" required><label>Bevorzugter Raumtyp (optional)</label><input name="preferred_room_type_key" maxlength="40" placeholder="physik"><label>Bezeichnung des Raumtyps</label><input name="preferred_room_type_name" maxlength="100" placeholder="Physikraum"><button>Fach speichern</button></form>';
        if($subjects!==[]){$html.='<h4>Fachraumpräferenzen</h4><div class="grid">';foreach($subjects as $subject){$html.='<form method="post" action="/admin/planning/subject-room-preference">'.$this->hidden($schoolId,$yearId).'<input type="hidden" name="subject_id" value="'.(int)$subject['id'].'"><strong>'.$this->e((string)$subject['subject_name']).'</strong><label>Raumtyp-Kennung (leer = keine Präferenz)</label><input name="preferred_room_type_key" maxlength="40" value="'.$this->e((string)($subject['preferred_room_type_key']??'')).'" placeholder="chemie"><label>Bezeichnung</label><input name="preferred_room_type_name" maxlength="100" value="'.$this->e((string)($subject['preferred_room_type_name']??'')).'" placeholder="Chemieraum"><button class="secondary">Präferenz speichern</button></form>';}$html.='</div>';}
        $html.='<h3>Zeit-/Stundenraster</h3><form method="post" action="/admin/planning/period">'.$this->hidden($schoolId,$yearId).'<label>Stundennummer</label><input type="number" name="period_number" min="1" max="20" required><label>Bezeichnung</label><input name="period_label" maxlength="40" placeholder="1. Stunde" required><label>Beginn</label><input type="time" name="starts_at" required><label>Ende</label><input type="time" name="ends_at" required><button>Stunde hinzufügen</button></form><ul>';foreach($periods as $p)$html.='<li>'.(int)$p['period_number'].'. '.$this->e((string)$p['label']).' ('.substr((string)$p['starts_at'],0,5).'–'.substr((string)$p['ends_at'],0,5).')</li>';$html.='</ul></details>';
        return $html;
    }

    private function planEditor(int $schoolId,int $yearId,array $plan,array $lessons,array $classes,array $subjects,array $teachers,array $periods,array $rooms,bool $canPublish): string
    {
        $id=(int)$plan['id'];$html='<hr><h2>v'.(int)$plan['version_number'].' · '.$this->e((string)$plan['title']).' · '.$this->status((string)$plan['status']).'</h2>';
        if($plan['status']==='draft'&&$classes!==[]&&$subjects!==[]&&$teachers!==[]&&$periods!==[]){
            $html.='<h3>Unterrichtsstunde zum Entwurf hinzufügen</h3><form method="post" action="/admin/planning/lesson">'.$this->hidden($schoolId,$yearId).'<input type="hidden" name="plan_id" value="'.$id.'"><label>Klasse/Kurs</label><select name="class_id" required>'.$this->options($classes,'id','class_code',static fn($r)=>$r['offering_name'].' · '.$r['grade_level'].' · '.$r['class_code']).'</select><label>Fach</label><select name="subject_id" required>'.$this->options($subjects,'id','subject_code',static fn($r)=>$r['subject_code'].' · '.$r['subject_name']).'</select><label>Lehrkraft</label><select name="teacher_id" required>'.$this->options($teachers,'id','username').'</select><label>Wochentag</label><select name="weekday"><option value="1">Montag</option><option value="2">Dienstag</option><option value="3">Mittwoch</option><option value="4">Donnerstag</option><option value="5">Freitag</option></select><label>Beginn</label><select name="period_number" required>'.$this->options($periods,'period_number','label',static fn($r)=>(int)$r['period_number'].'. '.$r['label'].' ('.substr($r['starts_at'],0,5).')').'</select><label>Raum (optional)</label><select name="room_id"><option value="">Kein Raum zugewiesen</option>'.$this->options($rooms,'id','room_name',static fn($r)=>$r['room_code'].' · '.$r['room_name'].' · '.$r['room_type_name'].($r['capacity']===null?'':' · '.$r['capacity'].' Plätze')).'</select><label>Dauer</label><select name="duration_periods"><option value="1">Einzelstunde</option><option value="2">Doppelstunde</option></select><button>Stunde eintragen</button></form>';
        }elseif($plan['status']==='draft')$html.='<p class="muted">Zum Eintragen fehlen Planungsgrundlagen. Lege Klassen/Kurse, Fächer, Lehrkräfte mit Rolle „Lehrkraft“ und Zeitraster an.</p>';
        $days=[1=>'Montag',2=>'Dienstag',3=>'Mittwoch',4=>'Donnerstag',5=>'Freitag'];
        $html.='<h3>Einträge</h3><div class="table-wrap"><table><thead><tr><th>Tag / Stunde</th><th>Schulform</th><th>Klasse/Kurs</th><th>Fach</th><th>Lehrkraft</th><th>Raum</th><th>Dauer</th><th>Hinweise</th><th></th></tr></thead><tbody>';
        foreach($lessons as $l){$html.='<tr><td>'.$days[(int)$l['weekday']].' · '.(int)$l['start_period'].'. Stunde</td><td>'.$this->e((string)$l['offering_name']).'</td><td>'.$this->e((string)$l['class_code']).' ('.$this->e((string)$l['grade_level']).')</td><td>'.$this->e((string)$l['subject_name']).'</td><td>'.$this->e((string)$l['username']).'</td><td>'.($l['room_id']===null?'—':$this->e((string)$l['room_name'])).'</td><td>'.((int)$l['duration_periods']===2?'Doppelstunde':'Einzelstunde').'</td><td>'.$this->renderRoomWarnings($l,$rooms).'</td><td>';
            if($plan['status']==='draft')$html.='<form method="post" action="/admin/planning/remove-lesson"><input type="hidden" name="csrf_token" value="'.$this->token.'"><input type="hidden" name="school_id" value="'.$schoolId.'"><input type="hidden" name="year_id" value="'.$yearId.'"><input type="hidden" name="plan_id" value="'.$id.'"><input type="hidden" name="lesson_id" value="'.(int)$l['id'].'"><button class="secondary">Entfernen</button></form>';
            $html.='</td></tr>';}
        if($lessons===[])$html.='<tr><td colspan="9">Noch keine Unterrichtsstunden eingetragen.</td></tr>';
        $html.='</tbody></table></div>';
        if($plan['status']==='draft'){
            if($canPublish)$html.='<form method="post" action="/admin/planning/publish" onsubmit="return confirm(\'Diesen Entwurf prüfen und als gültigen Stundenplan freigeben?\')"><input type="hidden" name="csrf_token" value="'.$this->token.'"><input type="hidden" name="school_id" value="'.$schoolId.'"><input type="hidden" name="year_id" value="'.$yearId.'"><input type="hidden" name="plan_id" value="'.$id.'"><button>Konfliktfrei prüfen und freigeben</button></form>';
            else $html.='<p class="muted">Die Freigabe muss durch eine berechtigte Schuladministration erfolgen.</p>';
            $html.='<p class="muted">Eine freigegebene Fassung bleibt unverändert. Änderungen erfolgen in einer neuen, aus ihr kopierten Entwurfsfassung.</p>';
        }
        return $html;
    }

    private function renderRoomWarnings(array $lesson,array $rooms): string
    {
        $warnings=[];$preferred=(string)($lesson['preferred_room_type_key']??'');$preferredName=(string)($lesson['preferred_room_type_name']??$preferred);
        if($lesson['room_id']===null){$warnings[]=$preferred!==''?'Kein Raum zugewiesen; bevorzugt: '.$preferredName:'Kein Raum zugewiesen';}
        elseif((int)($lesson['room_active']??0)!==1){$warnings[]='Zugewiesener Raum ist deaktiviert';}elseif($preferred!==''&&(string)($lesson['actual_room_type_key']??'')!==$preferred){$warnings[]='Fachraumpräferenz „'.$preferredName.'“ nicht erfüllt';}
        if($preferred!==''){$available=false;foreach($rooms as $room)if((string)$room['room_type_key']===$preferred){$available=true;break;}if(!$available)$warnings[]='Kein aktiver Raum des bevorzugten Typs vorhanden';}
        if($lesson['room_id']!==null&&$lesson['expected_size']!==null&&$lesson['room_capacity']===null)$warnings[]='Raumkapazität unbekannt';
        if($lesson['expected_size']===null)$warnings[]='Gruppengröße unbekannt; Kapazität nicht geprüft';
        if($warnings===[])return '<span class="muted">—</span>';
        return '<ul class="muted"><li>'.implode('</li><li>',array_map(fn($warning)=>$this->e($warning),$warnings)).'</li></ul>';
    }

    private function hidden(int $schoolId,int $yearId): string{return '<input type="hidden" name="csrf_token" value="'.$this->token.'"><input type="hidden" name="school_id" value="'.$schoolId.'"><input type="hidden" name="year_id" value="'.$yearId.'">';}
    private function options(array $rows,string $key,string $label,?callable $format=null): string{$out='';foreach($rows as $row){$value=(string)$row[$key];$text=$format!==null?(string)$format($row):(string)$row[$label];$out.='<option value="'.$this->e($value).'">'.$this->e($text).'</option>'; }return $out;}
    private function notice(string $key): string{$texts=['draft'=>'Entwurf wurde angelegt.','lesson'=>'Unterrichtsstunde wurde eingetragen.','removed'=>'Eintrag wurde entfernt.','published'=>'Planfassung wurde freigegeben.','saved'=>'Änderung wurde gespeichert.','room'=>'Raum wurde hinzugefügt.','subject-room'=>'Fachraumpräferenz wurde gespeichert.'];return isset($texts[$key])?'<p class="success" role="status">'.$texts[$key].'</p>':'';}
    private function status(string $value): string{return match($value){'draft'=>'Entwurf','published'=>'Freigegeben','superseded'=>'Ersetzt',default=>$this->e($value)};}
    private function validDate(string $value): bool{$d=DateTimeImmutable::createFromFormat('!Y-m-d',$value);return $d instanceof DateTimeImmutable&&$d->format('Y-m-d')===$value;}
    private function validTime(string $value): bool{return (bool)preg_match('/\A(?:[01]\d|2[0-3]):[0-5]\d\z/',$value);}
    private function input(string $name): string{return is_string($_POST[$name]??null)?trim($_POST[$name]):'';}
    private function int(string $name): int{$value=filter_var($_POST[$name]??null,FILTER_VALIDATE_INT);if($value===false||$value===null||$value<1)throw new InputException('Eine benötigte Kennung oder Zahl ist ungültig.');return (int)$value;}
    private function e(string $value): string{return Html::escape($value);}
    private function redirect(string $url): never{header('Location: '.$url,true,303);exit;}
    private function fail(int $status,string $title,string $message,?string $reference=null): never{http_response_code($status);$extra=$reference===null?'':'<p class="muted">Referenz: '.$this->e($reference).'</p>';Html::page($title,'<section class="card"><h1>'.$this->e($title).'</h1><p>'.$this->e($message).'</p>'.$extra.'</section>');exit;}
}

final class InputException extends \RuntimeException{}
