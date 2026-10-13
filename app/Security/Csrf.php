<?php
declare(strict_types=1);

namespace OrdiSchool\Security;

use RuntimeException;

final class Csrf
{
    public static function token(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            throw new RuntimeException('Session muss vor Erzeugung des CSRF-Tokens aktiv sein.');
        }

        if (!isset($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function verify(mixed $submitted): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE || !is_string($submitted)) {
            return false;
        }
        if (!isset($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $submitted);
    }
}
