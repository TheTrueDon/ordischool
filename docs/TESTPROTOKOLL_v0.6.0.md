# Testprotokoll v0.6.0

## Statische Prüfungen

- Shellskripte: `bash -n test.sh docker-entrypoint-test.sh`.
- PHP-Dateien: `php -l` für `app/`, `public/`, `tests/` und `bin/`.
- SQL-Update gegen `migrations/007_school_setup_and_permissions.sql` auf Vollständigkeit und Migrationsmarker abgleichen.
- ZIP-Inhalt auf vollständige App-, SQL-, Doku- und Installationsdateien prüfen.

## Erforderliche CI-/Laufzeitprüfungen

- Neuinstallation über `public/install.php`; Anmeldung des installierenden Kontos; Anlage einer Schule.
- Prüfen, dass Installationskonto Schuladministration und alle Rechte erhält, Hauptgebäude und Rollen vorhanden sind.
- Räume, Fachräume und Fächer anlegen; Schuljahr anlegen; Personal mit Mehrfachrollen und mehreren Schulformen erfassen.
- Rollenmatrix ändern und serverseitig kontrollieren, dass nicht berechtigte Rollen Änderungen nicht ausführen können.
- Klasse mit unterschiedlicher Klassenleitung und Stellvertretung anlegen; bestehende Klassenleitungen aktualisieren; ungültige Lehrkräfte und doppelte Person ablehnen.
- Schüler ohne Konto und Schüler mit Klassenzuordnung anlegen.
- Vorhandensein der Planfreigabe erst nach Erfüllung der Planungsgrundlagen prüfen.
- Update v0.5.0 auf v0.6.0 mit Standard- und verändertem Tabellenpräfix sowie Wiederholung nach erfolgreichem Update prüfen.
- Sicherung und Wiederherstellung der Testdatenbank durchführen.

Die Laufzeitprüfungen wurden in dieser Arbeitsumgebung nicht ausgeführt.
