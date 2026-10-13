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
        . '<label for="email">Benutzername oder Benutzername oder E-Mail-Adresse</label><input id="email" name="email" type="text" maxlength="254" autocomplete="username" required autofocus>'
        . '<label for="password">Passwort</label><input id="password" name="password" type="password" maxlength="1024" autocomplete="current-password" required>'
        . '<button type="submit">Anmelden</button></form><p class="muted">v0.1.2 enthält nur technische Administration. Es gibt noch keine Schüler- oder Schuldaten.</p></section>';
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

if ($path === '/' && $method === 'GET') {
    $userId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);
    if ($userId === false || $userId === null) {
        header('Location: /login', true, 303);
        exit;
    }
    try {
        $user = (new UserRepository(Database::connect($config), $config->tablePrefix))->findActiveById((int) $userId);
    } catch (Throwable $exception) {
        $reference = bin2hex(random_bytes(6));
        error_log('OrdiSchool session check failure ' . $reference . ' (' . get_class($exception) . ')');
        respondError(503, 'Dienst vorübergehend nicht verfügbar', 'Der Zugriff konnte nicht geprüft werden. Bitte versuchen Sie es später erneut.', $reference);
    }
    if ($user === null || ($user['role'] ?? '') !== 'system_admin') {
        Session::destroy();
        header('Location: /login', true, 303);
        exit;
    }
    $token = Html::escape(Csrf::token());
    $content = '<section class="card"><p class="eyebrow">Geschützter Bereich · technische Administration</p><h1>Willkommen bei OrdiSchool</h1>'
        . '<p>Das technische Grundgerüst v0.1.2 ist gestartet. Fachmodule und schulische Daten sind noch nicht enthalten.</p>'
        . '<p class="muted">Angemeldet als ' . Html::escape(str_ends_with((string) $user['email'], '@ordischool.invalid') ? substr((string) $user['email'], 0, -strlen('@ordischool.invalid')) : (string) $user['email']) . '</p>'
        . '<form method="post" action="/logout"><input type="hidden" name="csrf_token" value="' . $token . '"><button class="secondary" type="submit">Abmelden</button></form></section>';
    Html::page('Dashboard', $content);
    exit;
}

respondError(404, 'Seite nicht gefunden', 'Diese Seite ist in v0.1.2 nicht vorhanden.');
