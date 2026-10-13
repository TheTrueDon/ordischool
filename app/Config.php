<?php
declare(strict_types=1);

namespace OrdiSchool;

use RuntimeException;

final class Config
{
    private function __construct(
        public readonly string $environment,
        public readonly string $appKey,
        public readonly bool $secureCookies,
        public readonly string $dbHost,
        public readonly int $dbPort,
        public readonly string $dbName,
        public readonly string $dbUser,
        public readonly string $dbPassword,
        public readonly string $tablePrefix,
    ) {
    }

    public static function load(string $projectRoot): self
    {
        self::loadGeneratedConfig($projectRoot . '/config.local.php');

        $explicitEnvironment = getenv('ORDI_ENV');
        if ($explicitEnvironment === false || $explicitEnvironment === 'local') {
            self::loadLocalFile($projectRoot . '/.env');
        }

        $environment = self::env('ORDI_ENV', 'local');
        if (!in_array($environment, ['local', 'test', 'production'], true)) {
            throw new RuntimeException('ORDI_ENV muss local, test oder production sein.');
        }

        $appKey = self::env('ORDI_APP_KEY', '');
        if (!preg_match('/\A[a-f0-9]{64}\z/i', $appKey)) {
            throw new RuntimeException('ORDI_APP_KEY muss ein mit bin/generate-key.php erzeugter 64-stelliger Hex-Schlüssel sein.');
        }

        $secureCookies = self::boolEnv('ORDI_SESSION_SECURE_COOKIE', $environment === 'production');
        if ($environment === 'production' && !$secureCookies) {
            throw new RuntimeException('Sichere Session-Cookies müssen in production aktiviert sein.');
        }

        $dbHost = self::env('ORDI_DB_HOST', '127.0.0.1');
        $dbPort = filter_var(self::env('ORDI_DB_PORT', '3306'), FILTER_VALIDATE_INT);
        $dbName = self::env('ORDI_DB_NAME', '');
        $dbUser = self::env('ORDI_DB_USER', '');
        $dbPassword = self::env('ORDI_DB_PASSWORD', '');
        $tablePrefix = self::env('ORDI_DB_PREFIX', '');
        if (!preg_match('/\A[a-zA-Z0-9_]{0,40}\z/', $tablePrefix)) {
            throw new RuntimeException('ORDI_DB_PREFIX darf nur aus bis zu 40 Buchstaben, Zahlen und Unterstrichen bestehen.');
        }

        if ($dbPort === false || $dbPort < 1 || $dbPort > 65535) {
            throw new RuntimeException('ORDI_DB_PORT ist ungültig.');
        }
        if (!preg_match('/\A[a-zA-Z0-9_]+\z/', $dbName)) {
            throw new RuntimeException('ORDI_DB_NAME darf nur Buchstaben, Zahlen und Unterstriche enthalten.');
        }
        if ($dbHost === '' || $dbUser === '') {
            throw new RuntimeException('ORDI_DB_HOST und ORDI_DB_USER müssen gesetzt sein.');
        }

        return new self(
            $environment,
            $appKey,
            $secureCookies,
            $dbHost,
            (int) $dbPort,
            $dbName,
            $dbUser,
            $dbPassword,
            $tablePrefix,
        );
    }

    public static function env(string $name, string $default = ''): string
    {
        $value = getenv($name);
        return $value === false ? $default : $value;
    }

    private static function boolEnv(string $name, bool $default): bool
    {
        $value = getenv($name);
        if ($value === false) {
            return $default;
        }

        $parsed = filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
        if ($parsed === null) {
            throw new RuntimeException($name . ' muss true oder false sein.');
        }
        return $parsed;
    }


    private static function loadGeneratedConfig(string $path): void
    {
        if (!is_file($path) || !is_readable($path)) {
            return;
        }
        $settings = require $path;
        if (!is_array($settings)) {
            throw new RuntimeException('Die lokale Installationskonfiguration ist ungültig.');
        }
        $map = [
            'environment' => 'ORDI_ENV', 'app_key' => 'ORDI_APP_KEY',
            'secure_cookies' => 'ORDI_SESSION_SECURE_COOKIE', 'db_host' => 'ORDI_DB_HOST',
            'db_port' => 'ORDI_DB_PORT', 'db_name' => 'ORDI_DB_NAME',
            'db_user' => 'ORDI_DB_USER', 'db_password' => 'ORDI_DB_PASSWORD',
            'db_prefix' => 'ORDI_DB_PREFIX',
        ];
        foreach ($map as $key => $name) {
            if (array_key_exists($key, $settings) && getenv($name) === false) {
                $value = is_bool($settings[$key]) ? ($settings[$key] ? 'true' : 'false') : (string) $settings[$key];
                putenv($name . '=' . $value);
                $_ENV[$name] = $value;
            }
        }
    }

    private static function loadLocalFile(string $path): void
    {
        if (!is_file($path) || !is_readable($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES);
        if ($lines === false) {
            throw new RuntimeException('Lokale Konfiguration konnte nicht gelesen werden.');
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            if (!preg_match('/\A(ORDI_[A-Z0-9_]+)=(.*)\z/', $line, $matches)) {
                throw new RuntimeException('Ungültige Zeile in .env; erlaubt ist ausschließlich ORDI_NAME=Wert.');
            }

            $name = $matches[1];
            if (str_starts_with($name, 'ORDI_BOOTSTRAP_ADMIN_')) {
                throw new RuntimeException('Bootstrap-Zugangsdaten dürfen nicht in .env abgelegt werden.');
            }
            $value = trim($matches[2]);
            if (strlen($value) >= 2) {
                $first = $value[0];
                $last = $value[strlen($value) - 1];
                if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                    $value = substr($value, 1, -1);
                }
            }

            if (str_contains($value, "\0") || str_contains($value, "\n") || str_contains($value, "\r")) {
                throw new RuntimeException('Ungültiger Wert in lokaler Konfiguration.');
            }
            if (getenv($name) === false) {
                putenv($name . '=' . $value);
                $_ENV[$name] = $value;
            }
        }
    }
}
