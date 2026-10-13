<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Nur für die Kommandozeile.\n");
    exit(1);
}

require dirname(__DIR__) . '/bootstrap.php';

use OrdiSchool\Auth\PasswordHasher;
use OrdiSchool\Database;

if ($config->environment === 'production' && getenv('ORDI_ALLOW_ADMIN_BOOTSTRAP') !== 'YES') {
    fwrite(STDERR, "Produktions-Bootstrap gesperrt. Freigabe und sicheres Terminal prüfen, dann ORDI_ALLOW_ADMIN_BOOTSTRAP=YES setzen.\n");
    exit(2);
}

$email = strtolower(trim(OrdiSchool\Config::env('ORDI_BOOTSTRAP_ADMIN_EMAIL')));
$username = strtolower(trim(OrdiSchool\Config::env('ORDI_BOOTSTRAP_ADMIN_USERNAME', explode('@', $email)[0] ?? '')));
$password = OrdiSchool\Config::env('ORDI_BOOTSTRAP_ADMIN_PASSWORD');
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254 || !preg_match('/\A[a-z0-9._-]{3,64}\z/', $username) || $password === '') {
    fwrite(STDERR, "ORDI_BOOTSTRAP_ADMIN_EMAIL, ein gültiger ORDI_BOOTSTRAP_ADMIN_USERNAME und ORDI_BOOTSTRAP_ADMIN_PASSWORD müssen temporär gesetzt sein.\n");
    exit(2);
}

$exitCode = 0;
try {
    $pdo = Database::connect($config);
    $lock = (int) $pdo->query("SELECT GET_LOCK('ordischool_initial_admin_bootstrap', 10)")->fetchColumn();
    if ($lock !== 1) {
        throw new RuntimeException('Bootstrap-Sperre konnte nicht erworben werden.');
    }
    $exists = (bool) $pdo->query("SELECT EXISTS(SELECT 1 FROM {$config->tablePrefix}users WHERE role = 'system_admin')")->fetchColumn();
    if ($exists) {
        fwrite(STDERR, "Ein technisches Administratorkonto existiert bereits. Bootstrap ist einmalig.\n");
        $exitCode = 3;
    } else {
        $hash = PasswordHasher::hash($password);
        $statement = $pdo->prepare("INSERT INTO {$config->tablePrefix}users (username, email, password_hash, role, is_active, created_at) VALUES (:username, :email, :password_hash, 'system_admin', 1, UTC_TIMESTAMP())");
        $statement->execute(['username' => $username, 'email' => $email, 'password_hash' => $hash]);
        fwrite(STDOUT, "Technisches Administratorkonto angelegt. Zugangsdaten aus dem Prozess entfernen.\n");
    }
} catch (Throwable $exception) {
    fwrite(STDERR, 'Bootstrap fehlgeschlagen (' . get_class($exception) . '). Es wurden keine Zugangsdaten ausgegeben.' . "\n");
    $exitCode = 1;
} finally {
    if (isset($pdo) && $pdo instanceof PDO) {
        try {
            $pdo->query("SELECT RELEASE_LOCK('ordischool_initial_admin_bootstrap')");
        } catch (Throwable) {
            // Die Verbindung schließt unmittelbar nach dem Einmal-Bootstrap.
        }
    }
    if (isset($password) && function_exists('sodium_memzero')) {
        sodium_memzero($password);
    }
}
exit($exitCode);
