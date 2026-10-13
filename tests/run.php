<?php
declare(strict_types=1);

if (getenv('ORDI_ENV') !== 'test') {
    fwrite(STDERR, "Tests dürfen ausschließlich mit ORDI_ENV=test laufen.\n");
    exit(2);
}

require dirname(__DIR__) . '/bootstrap.php';

if (!str_ends_with($config->dbName, '_test')) {
    fwrite(STDERR, "Tests sind nur auf einer Datenbank mit _test-Suffix erlaubt.\n");
    exit(2);
}

use OrdiSchool\Auth\Authenticator;
use OrdiSchool\Auth\PasswordHasher;
use OrdiSchool\Auth\RateLimiter;
use OrdiSchool\Auth\UserRepository;
use OrdiSchool\Database;
use OrdiSchool\Planning\TimetableConflictDetector;
use OrdiSchool\Planning\TimetableRepository;
use OrdiSchool\Security\Csrf;
use OrdiSchool\Security\Session;
use OrdiSchool\School\SchoolRepository;

$checks = 0;
$assert = static function (bool $condition, string $message) use (&$checks): void {
    if (!$condition) {
        throw new RuntimeException('Test fehlgeschlagen: ' . $message);
    }
    $checks++;
};

$pdo = Database::connect($config);
$email = 'ordischool-v030-test@example.invalid';
$username = 'ordischool-v030-test';
$ip = '192.0.2.10'; // Dokumentationsadresse, keine echte Client-IP.
$userId = null;
$schoolId = null;
$memberIds = [];
$membershipIds = [];
$memberUserIds = [];
$planningIds = [];
try {
    $pdo->prepare("DELETE FROM {$config->tablePrefix}users WHERE email = :email OR username = :username")->execute(['email' => $email, 'username' => $username]);
    $assert(PasswordHasher::meetsMinimum('Ein-Sicheres-Testpasswort-01'), 'starkes synthetisches Passwort wird akzeptiert');
    $assert(!PasswordHasher::meetsMinimum('zu-kurz'), 'kurzes Passwort wird abgelehnt');
    $assert(!PasswordHasher::meetsMinimum("ungültig\xFF"), 'ungültiges UTF-8 wird abgelehnt');
    $hash = PasswordHasher::hash('Ein-Sicheres-Testpasswort-01');
    $assert(PasswordHasher::verify('Ein-Sicheres-Testpasswort-01', $hash), 'Passworthash kann verifiziert werden');
    $assert(!PasswordHasher::verify('falsches-Testpasswort-02', $hash), 'falsches Passwort wird abgelehnt');
    $assert(UserRepository::normalizeEmail('  PERSON@example.invalid ') === 'person@example.invalid', 'E-Mail-Normalisierung ist stabil');
    $assert(UserRepository::normalizeEmail('Flo.Admin') === 'flo.admin@ordischool.invalid', 'Benutzername wird sicher auf den internen Login-Schlüssel abgebildet');

    $crossFormConflict = TimetableConflictDetector::detect([
        ['class_id'=>10,'teacher_user_id'=>22,'weekday'=>1,'start_period'=>2,'duration_periods'=>1],
        ['class_id'=>11,'teacher_user_id'=>22,'weekday'=>1,'start_period'=>2,'duration_periods'=>1],
    ]);
    $assert(count($crossFormConflict)===1 && $crossFormConflict[0]['teacher_conflict']===true && $crossFormConflict[0]['class_conflict']===false, 'Lehrkraftkonflikt wird über verschiedene Schulformen/Klassen hinweg erkannt');
    $assert(TimetableConflictDetector::detect([
        ['class_id'=>10,'teacher_user_id'=>22,'weekday'=>1,'start_period'=>2,'duration_periods'=>2],
        ['class_id'=>11,'teacher_user_id'=>23,'room_id'=>8,'weekday'=>1,'start_period'=>3,'duration_periods'=>1],
    ])===[], 'gleichzeitige Belegung verschiedener Lehrkräfte löst keinen Konflikt aus');
    $roomConflict=TimetableConflictDetector::detect([['class_id'=>10,'teacher_user_id'=>22,'room_id'=>8,'weekday'=>1,'start_period'=>2,'duration_periods'=>2],['class_id'=>11,'teacher_user_id'=>23,'room_id'=>8,'weekday'=>1,'start_period'=>3,'duration_periods'=>1]]);
    $assert(count($roomConflict)===1&&$roomConflict[0]['room_conflict']===true&&!$roomConflict[0]['class_conflict']&&!$roomConflict[0]['teacher_conflict'],'gleichzeitige Belegung desselben Raums ist ein harter Konflikt');

    $users = new UserRepository($pdo, $config->tablePrefix);
    $statement = $pdo->prepare("INSERT INTO {$config->tablePrefix}users (username, email, password_hash, role, is_active, created_at) VALUES (:username, :email, :hash, 'system_admin', 1, UTC_TIMESTAMP())");
    $statement->execute(['username' => $username, 'email' => $email, 'hash' => $hash]);
    $userId = (int) $pdo->lastInsertId();
    $auth = new Authenticator($users, new RateLimiter($pdo, $config));
    $assert($auth->authenticate($email, 'falsches-Testpasswort-02', $ip) === null, 'falsches Login liefert keinen Benutzer');
    $authenticated = $auth->authenticate($email, 'Ein-Sicheres-Testpasswort-01', $ip);
    $assert(is_array($authenticated) && $authenticated['id'] === $userId, 'korrekte Anmeldung liefert aktiven technischen Benutzer');
    $assert(($auth->authenticate('ordischool-v030-test', 'Ein-Sicheres-Testpasswort-01', $ip)['id'] ?? null) === $userId, 'Anmeldung mit technischem Benutzernamen funktioniert');
    $schoolRepository = new SchoolRepository($pdo, $config->tablePrefix);
    $schoolId = $schoolRepository->createSchool('test-school', 'Synthetische Tests-Schule', 'gymnasium', 'Thüringen', $userId, ['gymnasium','regelschule']);
    $assert(count($schoolRepository->offeringNames($schoolId))===2, 'eine Schule kann mehrere Schulformangebote führen');
    $schoolRepository->createSchoolYear($schoolId, 'Testjahr', '2026-08-01', '2027-07-31', 'current', $userId);
    $assert(count($schoolRepository->listYears($schoolId)) === 1, 'Schuljahr wird der richtigen Schule zugeordnet');
    $schoolRepository->createMember('teacher-test', $hash, $schoolId, 'teacher', $userId);
    $schoolRepository->createMember('school-admin-test', $hash, $schoolId, 'school_admin', $userId);
    $testMembers = $schoolRepository->listMembers($schoolId);
    foreach ($testMembers as $testMember) {
        if (in_array($testMember['username'], ['teacher-test', 'school-admin-test'], true)) {
            $memberIds[] = (int) $testMember['user_id'];
            $membershipIds[(string) $testMember['username']] = (int) $testMember['membership_id'];
            $memberUserIds[(string) $testMember['username']] = (int) $testMember['user_id'];
        }
    }
    $assert(count($memberIds) === 2, 'Schulkonten werden nur in dieser Schule gelistet');
    $adminMemberId = $membershipIds['school-admin-test'];
    $teacherMemberId = $membershipIds['teacher-test'];
    $schoolAdminUserId = $memberUserIds['school-admin-test'];
    $assert($schoolRepository->canManageSchool($schoolAdminUserId, 'member', $schoolId), 'Schuladministration darf die eigene Schule verwalten');
    $teacherUserId = $memberUserIds['teacher-test'];
    $assert(!$schoolRepository->canManageSchool($teacherUserId, 'member', $schoolId), 'Lehrkraft erhält keine Schuladministration');
    $assert((new TimetableRepository($pdo,$config->tablePrefix))->canPublish($schoolAdminUserId,$schoolId), 'aktive Schuladministration darf Planfassungen freigeben');
    $assert(!(new TimetableRepository($pdo,$config->tablePrefix))->canPublish($teacherUserId,$schoolId), 'Lehrkraft erhält kein automatisches Planfreigaberecht');
    $assert(!(new TimetableRepository($pdo,$config->tablePrefix))->canPlan($userId,$schoolId), 'technische Systemadministration erhält ohne Schulrolle keinen Planungszugriff');

    $timetable = new TimetableRepository($pdo, $config->tablePrefix);
    $offerStmt=$pdo->prepare("SELECT id FROM {$config->tablePrefix}school_offerings WHERE school_id=:school_id ORDER BY offering_key");
    $offerStmt->execute(['school_id'=>$schoolId]);$offeringIds=array_map('intval',$offerStmt->fetchAll(PDO::FETCH_COLUMN));
    $yearStmt=$pdo->prepare("SELECT id FROM {$config->tablePrefix}school_years WHERE school_id=:school_id LIMIT 1");$yearStmt->execute(['school_id'=>$schoolId]);$yearId=(int)$yearStmt->fetchColumn();
    $timetable->addClass($schoolId,$yearId,$offeringIds[0],'9a','9','class',$userId,24);
    $timetable->addClass($schoolId,$yearId,$offeringIds[1],'9a','9','class',$userId,24);
    $classStmt=$pdo->prepare("SELECT id FROM {$config->tablePrefix}school_classes WHERE school_id=:school_id ORDER BY id");$classStmt->execute(['school_id'=>$schoolId]);$classIds=array_map('intval',$classStmt->fetchAll(PDO::FETCH_COLUMN));
    $timetable->addSubject($schoolId,'PHY','Physik',$userId,'kunst','Kunstraum');
    $subjectStmt=$pdo->prepare("SELECT id FROM {$config->tablePrefix}school_subjects WHERE school_id=:school_id AND subject_code='PHY'");$subjectStmt->execute(['school_id'=>$schoolId]);$subjectId=(int)$subjectStmt->fetchColumn();
    $roomIdStmt=$pdo->prepare("INSERT INTO {$config->tablePrefix}school_rooms (school_id,room_code,room_name,room_type_key,room_type_name,capacity,is_active,created_at) VALUES (:school_id,'PH-1','Physikraum 1','physik','Physikraum',30,1,UTC_TIMESTAMP())");$roomIdStmt->execute(['school_id'=>$schoolId]);$roomId=(int)$pdo->lastInsertId();
    $timetable->addPeriod($schoolId,1,'1. Stunde','08:00','08:45',$userId);
    $timetable->addPeriod($schoolId,2,'2. Stunde','08:50','09:35',$userId);
    $overlappingPeriodBlocked=false;
    try{$timetable->addPeriod($schoolId,3,'3. Stunde','09:00','09:45',$userId);}catch(RuntimeException){$overlappingPeriodBlocked=true;}
    $assert($overlappingPeriodBlocked,'überlappende Unterrichtszeiten werden abgewiesen');
    $planId=$timetable->createDraft($schoolId,$yearId,'Testentwurf',$userId);
    $smallRoomIdStmt=$pdo->prepare("INSERT INTO {$config->tablePrefix}school_rooms (school_id,room_code,room_name,room_type_key,room_type_name,capacity,is_active,created_at) VALUES (:school_id,'SM-1','Kleiner Raum','physik','Physikraum',20,1,UTC_TIMESTAMP())");$smallRoomIdStmt->execute(['school_id'=>$schoolId]);$smallRoomId=(int)$pdo->lastInsertId();
    $capacityBlocked=false;try{$timetable->addLesson($schoolId,$planId,$classIds[0],$subjectId,$teacherUserId,1,1,1,$userId,$smallRoomId);}catch(RuntimeException){$capacityBlocked=true;}$assert($capacityBlocked,'bekannte Raumkapazitätsüberschreitung wird blockiert');
    $timetable->addLesson($schoolId,$planId,$classIds[0],$subjectId,$teacherUserId,1,1,1,$userId,$roomId);
    $assert((int)$timetable->listLessons($schoolId,$planId)[0]['room_id']===$roomId,'Raum wird einer Unterrichtsstunde zugeordnet');
    $crossOfferingBlocked=false;
    try{$timetable->addLesson($schoolId,$planId,$classIds[1],$subjectId,$teacherUserId,1,1,1,$userId);}catch(RuntimeException){$crossOfferingBlocked=true;}
    $assert($crossOfferingBlocked,'derselbe Lehrer kann nicht zeitgleich in zwei Schulformen verplant werden');
    $timetable->publish($schoolId,$planId,$schoolAdminUserId);
    $assert($timetable->findPlan($schoolId,$planId)['status']==='published','Fachraumabweichung bleibt ein Hinweis und blockiert die Freigabe nicht');
    $copyId=$timetable->createDraft($schoolId,$yearId,'Folgeentwurf',$userId,$planId);
    $assert(count($timetable->listLessons($schoolId,$copyId))===1,'neue Entwurfsfassung übernimmt freigegebene Stunden ohne sie zu verändern');
    $assert((int)$timetable->listLessons($schoolId,$copyId)[0]['room_id']===$roomId,'Kopie übernimmt Raumzuweisung');
    $schoolRepository->setMembershipActive($teacherMemberId,false,$userId,$schoolId);
    $inactiveTeacherBlocked=false;
    try{$timetable->publish($schoolId,$copyId,$schoolAdminUserId);}catch(RuntimeException){$inactiveTeacherBlocked=true;}
    $assert($inactiveTeacherBlocked,'ein Entwurf mit inzwischen inaktiver Lehrkraft kann nicht freigegeben werden');
    $schoolRepository->setMembershipActive($adminMemberId, false, $userId, $schoolId);
    $assert(!$schoolRepository->canManageSchool($schoolAdminUserId, 'member', $schoolId), 'deaktivierte Mitgliedschaft verliert sofort die Verwaltungsberechtigung');
    $assert(!(new TimetableRepository($pdo,$config->tablePrefix))->canPublish($schoolAdminUserId,$schoolId), 'deaktivierte Schuladministration verliert sofort das Freigaberecht');
            $pdo->prepare("UPDATE {$config->tablePrefix}users SET is_active = 0 WHERE id = :id")->execute(['id' => $userId]);
    $assert($auth->authenticate($email, 'Ein-Sicheres-Testpasswort-01', $ip) === null, 'deaktiviertes Konto kann sich nicht anmelden');

    $sessionPath = sys_get_temp_dir();
    session_save_path($sessionPath);
    Session::start($config);
    $oldSessionId = session_id();
    Session::regenerate();
    $assert(session_id() !== $oldSessionId, 'Session-ID wechselt nach Authentifizierung');
    $token = Csrf::token();
    $assert(Csrf::verify($token), 'CSRF-Token lässt sich verifizieren');
    $assert(!Csrf::verify('falscher-token'), 'falsches CSRF-Token wird abgelehnt');
    Session::destroy();
    $assert(session_status() !== PHP_SESSION_ACTIVE, 'Logout-Helfer zerstört die aktive Session');

    fwrite(STDOUT, "{$checks} Prüfungen erfolgreich.\n");
} finally {
    if (session_status() === PHP_SESSION_ACTIVE) {
        Session::destroy();
    }
    if ($userId !== null) {
        if ($schoolId !== null) {
            $pdo->prepare("DELETE FROM {$config->tablePrefix}timetable_lessons WHERE school_id=:school_id")->execute(['school_id'=>$schoolId]);
            $pdo->prepare("UPDATE {$config->tablePrefix}timetable_plans SET based_on_plan_id=NULL WHERE school_id=:school_id")->execute(['school_id'=>$schoolId]);
            $pdo->prepare("DELETE FROM {$config->tablePrefix}timetable_plans WHERE school_id=:school_id")->execute(['school_id'=>$schoolId]);
            $pdo->prepare("DELETE FROM {$config->tablePrefix}school_rooms WHERE school_id=:school_id")->execute(['school_id'=>$schoolId]);
            $pdo->prepare("DELETE FROM {$config->tablePrefix}school_classes WHERE school_id=:school_id")->execute(['school_id'=>$schoolId]);
            $pdo->prepare("DELETE FROM {$config->tablePrefix}school_subjects WHERE school_id=:school_id")->execute(['school_id'=>$schoolId]);
            $pdo->prepare("DELETE FROM {$config->tablePrefix}timetable_periods WHERE school_id=:school_id")->execute(['school_id'=>$schoolId]);
            $pdo->prepare("DELETE FROM {$config->tablePrefix}school_offerings WHERE school_id=:school_id")->execute(['school_id'=>$schoolId]);
            $pdo->prepare("DELETE FROM {$config->tablePrefix}audit_log WHERE school_id = :school_id")->execute(['school_id' => $schoolId]);
            $pdo->prepare("DELETE rb FROM {$config->tablePrefix}role_bindings rb JOIN {$config->tablePrefix}school_memberships m ON m.id = rb.membership_id WHERE m.school_id = :school_id")->execute(['school_id' => $schoolId]);
            $pdo->prepare("DELETE FROM {$config->tablePrefix}school_memberships WHERE school_id = :school_id")->execute(['school_id' => $schoolId]);
            $pdo->prepare("DELETE FROM {$config->tablePrefix}school_years WHERE school_id = :school_id")->execute(['school_id' => $schoolId]);
            $pdo->prepare("DELETE FROM {$config->tablePrefix}schools WHERE id = :school_id")->execute(['school_id' => $schoolId]);
        }
        foreach ($memberIds as $memberId) {
            $pdo->prepare("DELETE FROM {$config->tablePrefix}users WHERE id = :id")->execute(['id' => $memberId]);
        }
        $pdo->prepare("DELETE FROM {$config->tablePrefix}users WHERE id = :id")->execute(['id' => $userId]);
    }
    $buckets = [
        hash_hmac('sha256', 'ip:' . $ip, $config->appKey),
        hash_hmac('sha256', 'pair:' . $ip . '|' . UserRepository::normalizeEmail($email), $config->appKey),
    ];
    $deleteThrottle = $pdo->prepare("DELETE FROM {$config->tablePrefix}auth_throttle WHERE bucket_key = :bucket");
    foreach ($buckets as $bucket) {
        $deleteThrottle->execute(['bucket' => $bucket]);
    }
    // Der Testaccount nutzt ausschließlich die reservierte Domain example.invalid.
}
