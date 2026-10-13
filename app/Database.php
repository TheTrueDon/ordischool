<?php
declare(strict_types=1);

namespace OrdiSchool;

use PDO;
use PDOException;
use RuntimeException;

final class Database
{
    public static function connect(Config $config): PDO
    {
        if (!extension_loaded('pdo_mysql')) {
            throw new RuntimeException('Die PHP-Erweiterung pdo_mysql fehlt.');
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            $config->dbHost,
            $config->dbPort,
            $config->dbName,
        );

        try {
            $pdo = new PDO($dsn, $config->dbUser, $config->dbPassword, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_STRINGIFY_FETCHES => false,
                PDO::ATTR_TIMEOUT => 5,
            ]);
            $pdo->exec("SET time_zone = '+00:00'");
            return $pdo;
        } catch (PDOException $exception) {
            throw new RuntimeException('Datenbankverbindung fehlgeschlagen.', 0, $exception);
        }
    }
}
