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
use OrdiSchool\Security\Csrf;
use OrdiSchool\Security\Session;

$checks = 0;
$assert = static function (bool $condition, string $message) use (&$checks): void {
    if (!$condition) {
        throw new RuntimeException('Test fehlgeschlagen: ' . $message);
    }
    $checks++;
};

$pdo = Database::connect($config);
$email = 'ordischool-v010-test@example.invalid';
$ip = '192.0.2.10'; // Dokumentationsadresse, keine echte Client-IP.
$userId = null;
try {
    $pdo->prepare("DELETE FROM {$config->tablePrefix}users WHERE email = :email")->execute(['email' => $email]);
    $assert(PasswordHasher::meetsMinimum('Ein-Sicheres-Testpasswort-01'), 'starkes synthetisches Passwort wird akzeptiert');
    $assert(!PasswordHasher::meetsMinimum('zu-kurz'), 'kurzes Passwort wird abgelehnt');
    $assert(!PasswordHasher::meetsMinimum("ungültig\xFF"), 'ungültiges UTF-8 wird abgelehnt');
    $hash = PasswordHasher::hash('Ein-Sicheres-Testpasswort-01');
    $assert(PasswordHasher::verify('Ein-Sicheres-Testpasswort-01', $hash), 'Passworthash kann verifiziert werden');
    $assert(!PasswordHasher::verify('falsches-Testpasswort-02', $hash), 'falsches Passwort wird abgelehnt');
    $assert(UserRepository::normalizeEmail('  PERSON@example.invalid ') === 'person@example.invalid', 'E-Mail-Normalisierung ist stabil');
    $assert(UserRepository::normalizeEmail('Flo.Admin') === 'flo.admin@ordischool.invalid', 'Benutzername wird sicher auf den internen Login-Schlüssel abgebildet');

    $users = new UserRepository($pdo, $config->tablePrefix);
    $statement = $pdo->prepare("INSERT INTO {$config->tablePrefix}users (email, password_hash, role, is_active, created_at) VALUES (:email, :hash, 'system_admin', 1, UTC_TIMESTAMP())");
    $statement->execute(['email' => $email, 'hash' => $hash]);
    $userId = (int) $pdo->lastInsertId();
    $auth = new Authenticator($users, new RateLimiter($pdo, $config));
    $assert($auth->authenticate($email, 'falsches-Testpasswort-02', $ip) === null, 'falsches Login liefert keinen Benutzer');
    $authenticated = $auth->authenticate($email, 'Ein-Sicheres-Testpasswort-01', $ip);
    $assert(is_array($authenticated) && $authenticated['id'] === $userId, 'korrekte Anmeldung liefert aktiven technischen Benutzer');
    $assert(($auth->authenticate('ordischool-v010-test', 'Ein-Sicheres-Testpasswort-01', $ip)['id'] ?? null) === $userId, 'Anmeldung mit technischem Benutzernamen funktioniert');
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
        $pdo->prepare("DELETE FROM {$config->tablePrefix}users WHERE id = :id")->execute(['id' => $userId]);
    }
    $buckets = [
        hash_hmac('sha256', 'ip:' . $ip, $config->appKey),
        hash_hmac('sha256', 'pair:' . $ip . '|' . $email, $config->appKey),
    ];
    $deleteThrottle = $pdo->prepare("DELETE FROM {$config->tablePrefix}auth_throttle WHERE bucket_key = :bucket");
    foreach ($buckets as $bucket) {
        $deleteThrottle->execute(['bucket' => $bucket]);
    }
    // Der Testaccount nutzt ausschließlich die reservierte Domain example.invalid.
}
