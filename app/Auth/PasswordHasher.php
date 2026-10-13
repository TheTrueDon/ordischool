<?php
declare(strict_types=1);

namespace OrdiSchool\Auth;

use InvalidArgumentException;

final class PasswordHasher
{
    public static function meetsMinimum(string $password): bool
    {
        return strlen($password) <= self::maximumBytes() && preg_match_all('/./us', $password) >= 14;
    }

    public static function maximumBytes(): int
    {
        // bcrypt (the usual PASSWORD_DEFAULT fallback) ignores bytes after 72.
        return defined('PASSWORD_ARGON2ID') ? 1024 : 72;
    }

    public static function hash(string $password): string
    {
        if (!self::meetsMinimum($password)) {
            throw new InvalidArgumentException('Das Passwort muss mindestens 14 Zeichen haben.');
        }

        $algorithm = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_DEFAULT;
        $hash = password_hash($password, $algorithm);
        if ($hash === false) {
            throw new InvalidArgumentException('Passwort konnte nicht sicher verarbeitet werden.');
        }
        return $hash;
    }

    public static function verify(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    public static function needsRehash(string $hash): bool
    {
        $algorithm = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_DEFAULT;
        return password_needs_rehash($hash, $algorithm);
    }
}
