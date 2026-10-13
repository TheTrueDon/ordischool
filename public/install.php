<?php
declare(strict_types=1);

require dirname(__DIR__) . '/autoload.php';

use OrdiSchool\Auth\PasswordHasher;
use OrdiSchool\Http\Html;

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: no-referrer');
header("Content-Security-Policy: default-src 'self'; object-src 'none'; base-uri 'self'; frame-ancestors 'none'; form-action 'self'");
header('Cache-Control: no-store, max-age=0');
$root = dirname(__DIR__);
$configPath = $root . '/config.local.php';
$isHttps = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== '' && strtolower((string) $_SERVER['HTTPS']) !== 'off';
if (is_file($configPath)) {
    http_response_code(409);
    Html::page('Installation abgeschlossen', '<section class="card narrow"><h1>OrdiSchool ist bereits eingerichtet</h1><p>Der Installer ist gesperrt. Melde dich über die Anmeldeseite an.</p><p><a href="/login">Zur Anmeldung</a></p></section>');
    exit;
}
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
session_name('ORDIINSTALL');
session_set_cookie_params(['lifetime' => 0, 'path' => '/', 'secure' => $isHttps, 'httponly' => true, 'samesite' => 'Strict']);
session_start();
if (!isset($_SESSION['install_token'])) {
    $_SESSION['install_token'] = bin2hex(random_bytes(32));
}
$token = (string) $_SESSION['install_token'];
$error = '';
$done = false;
$values = [
    'db_host' => 'localhost', 'db_port' => '3306', 'db_name' => '', 'db_user' => '',
    'db_prefix' => 'ordi_', 'admin_username' => '',
];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    foreach ($values as $key => $_) {
        $values[$key] = is_string($_POST[$key] ?? null) ? trim($_POST[$key]) : '';
    }
    $dbPassword = is_string($_POST['db_password'] ?? null) ? $_POST['db_password'] : '';
    $adminPassword = is_string($_POST['admin_password'] ?? null) ? $_POST['admin_password'] : '';
    $confirmPassword = is_string($_POST['admin_password_confirm'] ?? null) ? $_POST['admin_password_confirm'] : '';
    $configCreated = false;
    try {
        if (!hash_equals($token, is_string($_POST['csrf_token'] ?? null) ? $_POST['csrf_token'] : '')) {
            throw new RuntimeException('Die Sitzung ist abgelaufen. Lade die Seite neu.');
        }
        if (!$isHttps && ($_POST['ack_http_test'] ?? '') !== '1') {
            throw new RuntimeException('Für eine HTTP-Installation musst du den Hinweis zum unsicheren Testbetrieb bestätigen.');
        }
        if (!is_writable($root)) {
            throw new RuntimeException('Der Projektordner ist nicht beschreibbar. Für die Einrichtung muss PHP dort config.local.php anlegen dürfen.');
        }
        if (!extension_loaded('pdo_mysql')) {
            throw new RuntimeException('Die PHP-Erweiterung pdo_mysql ist auf dem Webserver nicht aktiviert.');
        }
        if (!preg_match('/\A[a-zA-Z0-9._:-]{1,255}\z/', $values['db_host'])) {
            throw new RuntimeException('Der Datenbankhost enthält ungültige Zeichen.');
        }
        if (!ctype_digit($values['db_port']) || (int) $values['db_port'] < 1 || (int) $values['db_port'] > 65535) {
            throw new RuntimeException('Der Datenbankport muss zwischen 1 und 65535 liegen.');
        }
        if (!preg_match('/\A[a-zA-Z0-9_]{1,64}\z/', $values['db_name']) || !preg_match('/\A[a-zA-Z0-9_]{1,64}\z/', $values['db_user'])) {
            throw new RuntimeException('Datenbankname und Benutzer dürfen nur Buchstaben, Zahlen und Unterstriche enthalten.');
        }
        if (!preg_match('/\A[a-zA-Z0-9_]{1,40}\z/', $values['db_prefix'])) {
            throw new RuntimeException('Das Tabellenpräfix darf 1 bis 40 Buchstaben, Zahlen oder Unterstriche enthalten.');
        }
        if (!preg_match('/\A[a-zA-Z0-9._-]{3,64}\z/', $values['admin_username'])) {
            throw new RuntimeException('Der Admin-Benutzername muss 3 bis 64 Zeichen lang sein und darf Buchstaben, Zahlen, Punkt, Bindestrich und Unterstrich enthalten.');
        }
        if ($adminPassword !== $confirmPassword || !PasswordHasher::meetsMinimum($adminPassword)) {
            throw new RuntimeException('Die Admin-Passwörter stimmen nicht überein oder erfüllen die Mindestanforderung von 14 Zeichen nicht.');
        }
        $internalEmail = strtolower($values['admin_username']) . '@ordischool.invalid';
        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $values['db_host'], (int) $values['db_port'], $values['db_name']);
        $pdo = new PDO($dsn, $values['db_user'], $dbPassword, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_TIMEOUT => 5,
        ]);
        $pdo->exec("SET time_zone = '+00:00'");
        $prefix = $values['db_prefix'];
        $pdo->exec("CREATE TABLE IF NOT EXISTS {$prefix}schema_migrations (
            migration VARCHAR(190) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
            applied_at DATETIME NOT NULL,
            PRIMARY KEY (migration)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $known = $pdo->query("SELECT migration FROM {$prefix}schema_migrations")->fetchAll(PDO::FETCH_COLUMN);
        $files = glob($root . '/migrations/[0-9][0-9][0-9]_*.sql') ?: [];
        sort($files, SORT_STRING);
        foreach ($files as $file) {
            $name = basename($file);
            if (in_array($name, $known, true)) {
                continue;
            }
            $sql = file_get_contents($file);
            if ($sql === false || trim($sql) === '') {
                throw new RuntimeException('Eine Installationsmigration konnte nicht gelesen werden.');
            }
            if ($name === '003_school_scope_roles.sql') {
                $columnCheck = $pdo->prepare(
                    'SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
                     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table_name
                       AND COLUMN_NAME = :column_name LIMIT 1'
                );
                $columnCheck->execute(['table_name' => $prefix . 'users', 'column_name' => 'username']);
                if ($columnCheck->fetchColumn() !== false) {
                    $sql = preg_replace(
                        '/^[ \t]*-- ORDISCHOOL_OPTIONAL_USERNAME_ADD_BEGIN\\R.*?^[ \t]*-- ORDISCHOOL_OPTIONAL_USERNAME_ADD_END\\R?/ms',
                        '',
                        $sql,
                        1,
                        $removedBlockCount
                    );
                    if ($sql === null || $removedBlockCount !== 1) {
                        throw new RuntimeException('Die vorhandene Benutzername-Spalte konnte nicht in die Migration übernommen werden.');
                    }
                }
            }
            $sql = str_replace('{{prefix}}', $prefix, $sql);
            $statements = preg_split('/;\s*(?:\r?\n|$)/', trim($sql)) ?: [];
            foreach ($statements as $statementSql) {
                if (trim($statementSql) !== '') {
                    $pdo->exec($statementSql);
                }
            }
            $stmt = $pdo->prepare("INSERT INTO {$prefix}schema_migrations (migration, applied_at) VALUES (:migration, UTC_TIMESTAMP())");
            $stmt->execute(['migration' => $name]);
        }
        $exists = (bool) $pdo->query("SELECT EXISTS(SELECT 1 FROM {$prefix}users WHERE role='system_admin')")->fetchColumn();
        if ($exists) {
            throw new RuntimeException('In dieser Installation gibt es bereits ein Systemkonto. Der Installer verändert vorhandene Konten nicht.');
        }
        $settings = [
            'environment' => $isHttps ? 'production' : 'test', 'app_key' => bin2hex(random_bytes(32)), 'secure_cookies' => $isHttps,
            'db_host' => $values['db_host'], 'db_port' => (int) $values['db_port'],
            'db_name' => $values['db_name'], 'db_user' => $values['db_user'],
            'db_password' => $dbPassword, 'db_prefix' => $prefix,
        ];
        $php = "<?php\ndeclare(strict_types=1);\n\nreturn " . var_export($settings, true) . ";\n";
        $temporaryConfig = $configPath . '.tmp-' . bin2hex(random_bytes(6));
        if (file_put_contents($temporaryConfig, $php, LOCK_EX) === false || !chmod($temporaryConfig, 0600) || !rename($temporaryConfig, $configPath)) {
            @unlink($temporaryConfig);
            throw new RuntimeException('Die Konfigurationsdatei konnte nicht sicher gespeichert werden. Prüfe Schreibrechte des Projektordners.');
        }
        $configCreated = true;
        $stmt = $pdo->prepare("INSERT INTO {$prefix}users (username, email, password_hash, role, is_active, created_at) VALUES (:username, :email, :hash, 'system_admin', 1, UTC_TIMESTAMP())");
        $stmt->execute(['username' => strtolower($values['admin_username']), 'email' => $internalEmail, 'hash' => PasswordHasher::hash($adminPassword)]);
        unset($dbPassword, $adminPassword, $confirmPassword);
        session_regenerate_id(true);
        $done = true;
    } catch (Throwable $exception) {
        if ($configCreated) {
            @unlink($configPath);
        }
        error_log('OrdiSchool installer error (' . get_class($exception) . ')');
        $error = $exception instanceof RuntimeException ? $exception->getMessage() : 'Einrichtung fehlgeschlagen. Prüfe DB-Zugangsdaten, Berechtigungen und Serverprotokoll.';
    }
}

if ($done) {
    $httpCaution = $isHttps ? '' : '<p class="error" role="alert">Diese Instanz läuft ohne HTTPS im unsicheren Testmodus. Verwende keine echten Schul- oder Personendaten. DB-Zugangsdaten und Anmeldung können im Netzwerk mitgelesen werden. Stelle HTTPS fertig und schalte Secure-Cookies ein, bevor du die Instanz produktiv nutzt.</p>';
    Html::page('Installation abgeschlossen', '<section class="card narrow"><h1>Installation abgeschlossen</h1><p>Die Datenbanktabellen wurden mit dem Präfix <code>' . Html::escape($values['db_prefix']) . '</code> angelegt und das Admin-Konto erstellt.</p>' . $httpCaution . '<p>Der Installer ist jetzt gesperrt. Melde dich mit deinem Benutzernamen und dem selbst gewählten Passwort an.</p><p><a class="button-link" href="/login">Zur Anmeldung</a></p><p class="muted">Notiere das Datenbankpräfix für spätere SQL-Updates. Für jede neue Version zuerst ein Backup erstellen, dann das mitgelieferte SQL-Update in phpMyAdmin anwenden und anschließend die Programmdateien ersetzen.</p></section>');
    exit;
}

$e = static fn (string $value): string => Html::escape($value);
$notice = $error === '' ? '' : '<p class="error" role="alert">' . $e($error) . '</p>';
$tokenField = '<input type="hidden" name="csrf_token" value="' . $e($token) . '">';
$transportNotice = $isHttps
    ? '<p>Die Zugangsdaten werden verschlüsselt übertragen. Die Installationskonfiguration wird außerhalb des öffentlichen Ordners gespeichert.</p>'
    : '<div class="error" role="alert"><strong>Unsicherer HTTP-Testbetrieb:</strong> Datenbankpasswort und Admin-Passwort werden unverschlüsselt übertragen. Nutze ausschließlich eine separate Testdatenbank mit einem eigens dafür angelegten DB-Benutzer. Keine echten Schul- oder Personendaten eingeben. Sobald HTTPS eingerichtet ist, musst du die Instanz auf Produktion und sichere Cookies umstellen.</div>';
$body = '<section class="card narrow"><p class="eyebrow">Ersteinrichtung</p><h1>OrdiSchool installieren</h1>' . $notice
    . $transportNotice
    . '<form method="post" action="/install.php" autocomplete="off">' . $tokenField
    . '<h2>Datenbank</h2>'
    . '<label for="db_host">Datenbankhost</label><input id="db_host" name="db_host" value="' . $e($values['db_host']) . '" required>'
    . '<label for="db_port">Port</label><input id="db_port" name="db_port" inputmode="numeric" value="' . $e($values['db_port']) . '" required>'
    . '<label for="db_name">Datenbankname</label><input id="db_name" name="db_name" value="' . $e($values['db_name']) . '" required>'
    . '<label for="db_user">Datenbankbenutzer</label><input id="db_user" name="db_user" value="' . $e($values['db_user']) . '" required>'
    . '<label for="db_password">Datenbankpasswort</label><input id="db_password" name="db_password" type="password" autocomplete="new-password">'
    . '<label for="db_prefix">Tabellenpräfix</label><input id="db_prefix" name="db_prefix" maxlength="40" value="' . $e($values['db_prefix']) . '" required>'
    . '<h2>Administratorkonto</h2>'
    . '<label for="admin_username">Admin-Benutzername</label><input id="admin_username" name="admin_username" maxlength="64" value="' . $e($values['admin_username']) . '" autocomplete="username" required>'
    . '<label for="admin_password">Passwort (mindestens 14 Zeichen)</label><input id="admin_password" name="admin_password" type="password" minlength="14" autocomplete="new-password" required>'
    . '<label for="admin_password_confirm">Passwort wiederholen</label><input id="admin_password_confirm" name="admin_password_confirm" type="password" minlength="14" autocomplete="new-password" required>'
    . ($isHttps ? '' : '<label><input type="checkbox" class="inline-check" name="ack_http_test" value="1" required> Ich bestätige, dass diese Einrichtung nur für einen temporären Test mit synthetischen Daten erfolgt und Zugangsdaten über HTTP unverschlüsselt übertragen werden.</label>')
    . '<button type="submit">Installation starten</button></form><p class="muted">Verwende ein leeres Datenbankschema oder ein eigenes Tabellenpräfix. Bestehende Daten werden nicht gelöscht.</p></section>';
Html::page('OrdiSchool installieren', $body);
