<?php
declare(strict_types=1);

namespace OrdiSchool\Auth;

use PDO;
use RuntimeException;

final class UserRepository
{
    public function __construct(private readonly PDO $pdo, private readonly string $prefix = '')
    {
    }

    public static function normalizeEmail(string $email): string
    {
        $identifier = strtolower(trim($email));
        if (!str_contains($identifier, '@') && preg_match('/\A[a-z0-9._-]{3,64}\z/', $identifier)) {
            return $identifier . '@ordischool.invalid';
        }
        return $identifier;
    }

    public static function validEmail(string $email): bool
    {
        return strlen($email) <= 254
            && filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public function findByEmail(string $email): ?array
    {
        $identifier = strtolower(trim($email));
        if (str_contains($identifier, '@')) {
            $statement = $this->pdo->prepare(
                "SELECT id, username, email, password_hash, role, is_active FROM {$this->prefix}users WHERE email = :identifier LIMIT 1"
            );
        } else {
            $statement = $this->pdo->prepare(
                "SELECT id, username, email, password_hash, role, is_active FROM {$this->prefix}users WHERE username = :identifier LIMIT 1"
            );
        }
        $statement->execute(['identifier' => $identifier]);
        $user = $statement->fetch();
        return is_array($user) ? $user : null;
    }

    public function findActiveById(int $id): ?array
    {
        $statement = $this->pdo->prepare(
            "SELECT id, username, email, role FROM {$this->prefix}users WHERE id = :id AND is_active = 1 LIMIT 1"
        );
        $statement->execute(['id' => $id]);
        $user = $statement->fetch();
        return is_array($user) ? $user : null;
    }

    public function systemAdminExists(): bool
    {
        $statement = $this->pdo->query("SELECT 1 FROM {$this->prefix}users WHERE role = 'system_admin' LIMIT 1");
        return $statement->fetchColumn() !== false;
    }

    public function createInitialAdmin(string $email, string $passwordHash): int
    {
        $email = self::normalizeEmail($email);
        if (!self::validEmail($email)) {
            throw new RuntimeException('Die E-Mail-Adresse ist ungültig.');
        }
        if ($this->systemAdminExists()) {
            throw new RuntimeException('Eine initiale Systemadministration existiert bereits.');
        }

        $statement = $this->pdo->prepare(
            "INSERT INTO {$this->prefix}users (username, email, password_hash, role, is_active, created_at)
             VALUES (:username, :email, :password_hash, 'system_admin', 1, UTC_TIMESTAMP())"
        );
        $statement->execute([
            'username' => strtolower(substr($email, 0, (int) strpos($email, '@'))),
            'email' => $email,
            'password_hash' => $passwordHash,
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function updatePasswordHash(int $id, string $passwordHash): void
    {
        $statement = $this->pdo->prepare(
            "UPDATE {$this->prefix}users SET password_hash = :password_hash WHERE id = :id"
        );
        $statement->execute(['password_hash' => $passwordHash, 'id' => $id]);
    }

    public function recordSuccessfulLogin(int $id): void
    {
        $statement = $this->pdo->prepare(
            "UPDATE {$this->prefix}users SET last_login_at = UTC_TIMESTAMP() WHERE id = :id AND is_active = 1"
        );
        $statement->execute(['id' => $id]);
    }
}
