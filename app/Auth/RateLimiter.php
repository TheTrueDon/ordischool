<?php
declare(strict_types=1);

namespace OrdiSchool\Auth;

use OrdiSchool\Config;
use PDO;

final class RateLimiter
{
    private const WINDOW_SECONDS = 900;
    private const MAX_PER_IP = 30;
    private const MAX_PER_IP_EMAIL = 5;

    public function __construct(
        private readonly PDO $pdo,
        private readonly Config $config,
    ) {
    }

    public function isBlocked(string $ip, string $email): bool
    {
        $this->removeExpiredRows();
        return $this->attempts($this->bucket('ip', $ip)) >= self::MAX_PER_IP
            || $this->attempts($this->bucket('pair', $ip . '|' . UserRepository::normalizeEmail($email))) >= self::MAX_PER_IP_EMAIL;
    }

    public function recordFailure(string $ip, string $email): void
    {
        $this->increment($this->bucket('ip', $ip));
        $this->increment($this->bucket('pair', $ip . '|' . UserRepository::normalizeEmail($email)));
    }

    public function recordSuccess(string $ip, string $email): void
    {
        $statement = $this->pdo->prepare('DELETE FROM auth_throttle WHERE bucket_key = :bucket');
        $statement->execute([
            'bucket' => $this->bucket('pair', $ip . '|' . UserRepository::normalizeEmail($email)),
        ]);
    }

    private function bucket(string $type, string $value): string
    {
        return hash_hmac('sha256', $type . ':' . $value, $this->config->appKey);
    }

    private function attempts(string $key): int
    {
        $statement = $this->pdo->prepare(
            'SELECT attempts FROM auth_throttle
             WHERE bucket_key = :bucket
             AND window_started_at >= DATE_SUB(UTC_TIMESTAMP(), INTERVAL 15 MINUTE)'
        );
        $statement->execute(['bucket' => $key]);
        return (int) ($statement->fetchColumn() ?: 0);
    }

    private function increment(string $key): void
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO auth_throttle (bucket_key, attempts, window_started_at)
             VALUES (:bucket, 1, UTC_TIMESTAMP())
             ON DUPLICATE KEY UPDATE
               attempts = IF(window_started_at < DATE_SUB(UTC_TIMESTAMP(), INTERVAL 15 MINUTE), 1, LEAST(attempts + 1, 65535)),
               window_started_at = IF(window_started_at < DATE_SUB(UTC_TIMESTAMP(), INTERVAL 15 MINUTE), UTC_TIMESTAMP(), window_started_at)'
        );
        $statement->execute(['bucket' => $key]);
    }

    private function removeExpiredRows(): void
    {
        $this->pdo->exec(
            'DELETE FROM auth_throttle WHERE window_started_at < DATE_SUB(UTC_TIMESTAMP(), INTERVAL 1 DAY)'
        );
    }
}
