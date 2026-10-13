<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Nur für die Kommandozeile.\n");
    exit(1);
}

require dirname(__DIR__) . '/bootstrap.php';

use OrdiSchool\Database;

if ($config->environment === 'test' && !str_ends_with($config->dbName, '_test')) {
    fwrite(STDERR, "Abbruch: Testmigrationen sind nur auf einer Datenbank mit _test-Suffix erlaubt.\n");
    exit(2);
}
if ($config->environment === 'production' && getenv('ORDI_ALLOW_PRODUCTION_MIGRATIONS') !== 'YES') {
    fwrite(STDERR, "Produktionsmigration gesperrt. Erst Backup und Freigabe prüfen, dann ORDI_ALLOW_PRODUCTION_MIGRATIONS=YES setzen.\n");
    exit(2);
}

try {
    $pdo = Database::connect($config);
    $pdo->exec("CREATE TABLE IF NOT EXISTS {$config->tablePrefix}schema_migrations (
        migration VARCHAR(190) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
        applied_at DATETIME NOT NULL,
        PRIMARY KEY (migration)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $files = glob(dirname(__DIR__) . '/migrations/[0-9][0-9][0-9]_*.sql') ?: [];
    sort($files, SORT_STRING);
    $known = $pdo->query('SELECT migration FROM ' . $config->tablePrefix . 'schema_migrations')->fetchAll(PDO::FETCH_COLUMN);
    foreach ($files as $file) {
        $name = basename($file);
        if (in_array($name, $known, true)) {
            continue;
        }
        $sql = file_get_contents($file);
        if ($sql !== false) {
            $sql = str_replace('{{prefix}}', $config->tablePrefix, $sql);
        }
        if ($sql === false || trim($sql) === '') {
            throw new RuntimeException('Migration ist leer oder nicht lesbar: ' . $name);
        }
        // MySQL-DDL ist nicht transaktional. Bei Fehlern das Schema manuell prüfen.
        $pdo->exec($sql);
        $statement = $pdo->prepare('INSERT INTO ' . $config->tablePrefix . 'schema_migrations (migration, applied_at) VALUES (:migration, UTC_TIMESTAMP())');
        $statement->execute(['migration' => $name]);
        fwrite(STDOUT, "Angewendet: {$name}\n");
    }
    fwrite(STDOUT, "Migrationen abgeschlossen.\n");
} catch (Throwable $exception) {
    fwrite(STDERR, 'Migration fehlgeschlagen (' . get_class($exception) . '). Schema vor erneutem Lauf prüfen.' . "\n");
    exit(1);
}
