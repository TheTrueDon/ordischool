<?php
declare(strict_types=1);

namespace OrdiSchool\Auth;

use OrdiSchool\Config;

final class Authenticator
{
    private const DUMMY_HASH = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4KoEa3Ro9llC/.og/at2.uheWG/igi';

    public function __construct(
        private readonly UserRepository $users,
        private readonly RateLimiter $rateLimiter,
    ) {
    }

    public function authenticate(string $email, string $password, string $ip): ?array
    {
        $email = UserRepository::normalizeEmail($email);
        $ip = filter_var($ip, FILTER_VALIDATE_IP) !== false ? $ip : 'unknown';
        $safeEmail = strlen($email) <= 254 ? $email : substr($email, 0, 254);

        if ($this->rateLimiter->isBlocked($ip, $safeEmail)) {
            return null;
        }

        $user = UserRepository::validEmail($email) ? $this->users->findByEmail($email) : null;
        $hash = is_array($user) ? (string) $user['password_hash'] : self::DUMMY_HASH;
        $passwordWithinLimit = strlen($password) <= PasswordHasher::maximumBytes();
        $verified = $passwordWithinLimit && PasswordHasher::verify($password, $hash);

        if (!$verified || !is_array($user) || (int) $user['is_active'] !== 1) {
            $this->rateLimiter->recordFailure($ip, $safeEmail);
            return null;
        }

        if (PasswordHasher::needsRehash($hash)) {
            $this->users->updatePasswordHash((int) $user['id'], PasswordHasher::hash($password));
        }
        $this->users->recordSuccessfulLogin((int) $user['id']);
        $this->rateLimiter->recordSuccess($ip, $safeEmail);

        return [
            'id' => (int) $user['id'],
            'email' => (string) $user['email'],
            'role' => (string) $user['role'],
        ];
    }
}
