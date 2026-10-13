<?php
declare(strict_types=1);

require dirname(__DIR__) . '/autoload.php';

use OrdiSchool\Auth\Authenticator;
use OrdiSchool\Auth\RateLimiter;
use OrdiSchool\Auth\UserRepository;
use OrdiSchool\Config;
use OrdiSchool\Database;
use OrdiSchool\Http\Html;
use OrdiSchool\Security\Csrf;
use OrdiSchool\Security\Headers;
use OrdiSchool\Security\Session;

function respondError(int $status, string $title, string $message, ?string $reference = null): never
{
    http_response_code($status);
    $extra = $reference === null ? '' : '<p class="muted">Referenz: ' . Html::escape($reference) . '</p>';
    Html::page($title, '<section class="card"><h1>' . Html::escape($title) . '</h1><p>' . Html::escape($message) . '</p>' . $extra . '</section>');
    exit;
}

function loginPage(string $error = ''): never
{
    $notice = $error === '' ? '' : '<p class="error" role="alert">' . Html::escape($error) . '</p>';
    $token = Html::escape(Csrf::token());
    $content = '<section class="card narrow"><p class="eyebrow">Technischer Zugang</p><h1>Anmelden</h1>' . $notice
        . '<form method="post" action="/login" autocomplete="on">'
        . '<input type="hidden" name="csrf_token" value="' . $token . '">'
        . '<label for="email">Benutzername oder E-Mail-Adresse</label><input id="email" name="email" type="text" maxlength="254" autocomplete="username" required autofocus>'
        . '<label for="password">Passwort</label><input id="password" name="password" type="password" maxlength="1024" autocomplete="current-password" required>'
        . '<button type="submit">Anmelden</button></form><p class="muted">Bitte verwende nur synthetische Testdaten.</p></section>';
    Html::page('Anmelden', $content);
    exit;
}

try {
    $config = Config::load(dirname(__DIR__));
    Headers::send($config);
    Session::start($config);
} catch (Throwable $exception) {
    $reference = bin2hex(random_bytes(6));
    error_log('OrdiSchool startup failure ' . $reference . ' (' . get_class($exception) . ')');
    respondError(503, 'Dienst vorübergehend nicht verfügbar', 'Die Anwendung konnte nicht gestartet werden. Bitte versuchen Sie es später erneut.', $reference);
}

$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$path = is_string($path) ? $path : '/';

if ($path === '/healthz' && $method === 'GET') {
    header('Content-Type: text/plain; charset=utf-8');
    echo "ok\n";
    exit;
}

if ($path === '/login' && $method === 'GET') {
    loginPage();
}

if ($path === '/login' && $method === 'POST') {
    if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
        respondError(400, 'Anfrage abgelaufen', 'Bitte laden Sie die Seite neu und versuchen Sie es erneut.');
    }
    $email = is_string($_POST['email'] ?? null) ? substr($_POST['email'], 0, 512) : '';
    $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
    try {
        $pdo = Database::connect($config);
        $users = new UserRepository($pdo, $config->tablePrefix);
        $auth = new Authenticator($users, new RateLimiter($pdo, $config));
        $ip = is_string($_SERVER['REMOTE_ADDR'] ?? null) ? $_SERVER['REMOTE_ADDR'] : 'unknown';
        $user = $auth->authenticate($email, $password, $ip);
    } catch (Throwable $exception) {
        $reference = bin2hex(random_bytes(6));
        error_log('OrdiSchool authentication failure ' . $reference . ' (' . get_class($exception) . ')');
        respondError(503, 'Anmeldung vorübergehend nicht verfügbar', 'Die Anmeldung konnte nicht verarbeitet werden. Bitte versuchen Sie es später erneut.', $reference);
    }
    if ($user === null) {
        loginPage('Anmeldung nicht möglich. Prüfen Sie die Eingaben oder versuchen Sie es später erneut.');
    }
    Session::regenerate();
    $_SESSION = ['user_id' => $user['id']];
    header('Location: /', true, 303);
    exit;
}

if ($path === '/logout' && $method === 'POST') {
    if (!Csrf::verify($_POST['csrf_token'] ?? null)) {
        respondError(400, 'Anfrage abgelaufen', 'Bitte laden Sie die Seite neu und versuchen Sie es erneut.');
    }
    Session::destroy();
    header('Location: /login', true, 303);
    exit;
}

if ($path === '/' || str_starts_with($path, '/admin/')) {
    $userId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);
    if ($userId === false || $userId === null) {
        header('Location: /login', true, 303);
        exit;
    }
    try {
        $pdo = Database::connect($config);
        $user = (new UserRepository($pdo, $config->tablePrefix))->findActiveById((int) $userId);
    } catch (Throwable $exception) {
        $reference = bin2hex(random_bytes(6));
        error_log('OrdiSchool session check failure ' . $reference . ' (' . get_class($exception) . ')');
        respondError(503, 'Dienst vorübergehend nicht verfügbar', 'Der Zugriff konnte nicht geprüft werden. Bitte versuchen Sie es später erneut.', $reference);
    }
    if ($user === null) {
        Session::destroy();
        header('Location: /login', true, 303);
        exit;
    }
    $schoolRepository = new \OrdiSchool\School\SchoolRepository($pdo, $config->tablePrefix);
    $userRoles = ($user['role'] ?? '') === 'system_admin' ? [] : $schoolRepository->rolesForUser((int) $userId);
    if (($user['role'] ?? '') !== 'system_admin' && $userRoles === []) {
        Session::destroy();
        header('Location: /login', true, 303);
        exit;
    }
    if (str_starts_with($path, '/admin/')) {
        if (str_starts_with($path, '/admin/planning')) {
            (new \OrdiSchool\Planning\TimetableController($pdo, $config->tablePrefix, $user))->handle($path, $method);
        }
        (new \OrdiSchool\School\AdminController($pdo, $config->tablePrefix, $user))->handle($path, $method);
    }
    $token = Html::escape(Csrf::token());
    $displayRole = ($user['role'] ?? '') === 'system_admin' ? 'Technische Administration' : implode(', ', array_unique(array_column($userRoles, 'role_key')));
    $adminLink = ($user['role'] ?? '') === 'system_admin' || in_array('school_admin', array_column($userRoles, 'role_key'), true)
        ? '<p><a class="button-link" href="/admin/schools">Schulverwaltung öffnen</a></p>' : '';
    $planningLink = array_intersect(['school_admin', 'planner'], array_column($userRoles, 'role_key')) !== []
        ? '<p><a class="button-link" href="/admin/planning">Stundenplanung öffnen</a></p>' : '';
    $content = '<section class="card"><p class="eyebrow">OrdiSchool v0.4.1</p><h1>Willkommen, ' . Html::escape((string) ($user['username'] ?? '')) . '</h1>'
        . '<p>Angemeldet mit der Rolle: ' . Html::escape($displayRole) . '.</p>'
        . '<p class="muted">Diese Version ergänzt Schulstammdaten und Rollen um die erste Stundenplanfassung. Verwende nur synthetische Testdaten.</p>' . $adminLink . $planningLink
        . '<form method="post" action="/logout"><input type="hidden" name="csrf_token" value="' . $token . '"><button class="secondary" type="submit">Abmelden</button></form></section>';
    Html::page('Dashboard', $content);
    exit;
}

respondError(404, 'Seite nicht gefunden', 'Diese Seite ist in v0.4.1 nicht vorhanden.');
