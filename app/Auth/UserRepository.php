<?php
declare(strict_types=1);

namespace OrdiSchool\Auth;

use PDO;
use RuntimeException;

final class UserRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public static function normalizeEmail(string $email): string
    {
        return strtolower(trim($email));
    }

    public static function validEmail(string $email): bool
    {
        return strlen($email) <= 254
            && filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public function findByEmail(string $email): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT id, email, password_hash, role, is_active FROM users WHERE email = :email LIMIT 1'
        );
        $statement->execute(['email' => self::normalizeEmail($email)]);
        $user = $statement->fetch();
        return is_array($user) ? $user : null;
    }

    public function findActiveById(int $id): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT id, email, role FROM users WHERE id = :id AND is_active = 1 LIMIT 1'
        );
        $statement->execute(['id' => $id]);
        $user = $statement->fetch();
        return is_array($user) ? $user : null;
    }

    public function systemAdminExists(): bool
    {
        $statement = $this->pdo->query("SELECT 1 FROM users WHERE role = 'system_admin' LIMIT 1");
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
            "INSERT INTO users (email, password_hash, role, is_active, created_at)
             VALUES (:email, :password_hash, 'system_admin', 1, UTC_TIMESTAMP())"
        );
        $statement->execute([
            'email' => $email,
            'password_hash' => $passwordHash,
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function updatePasswordHash(int $id, string $passwordHash): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE users SET password_hash = :password_hash WHERE id = :id'
        );
        $statement->execute(['password_hash' => $passwordHash, 'id' => $id]);
    }

    public function recordSuccessfulLogin(int $id): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE users SET last_login_at = UTC_TIMESTAMP() WHERE id = :id AND is_active = 1'
        );
        $statement->execute(['id' => $id]);
    }
}
