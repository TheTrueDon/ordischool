# OrdiSchool

**Ordinatio Scholae – Schulinitialisierung und Administration v0.6.0**

v0.6.0 richtet den Einstieg an der realen Schulreihenfolge aus: Nach der Installation wird zuerst eine Schule mit Schulformen/Bildungsgängen angelegt. Das Installationskonto erhält dabei die schulweite Schuladministration. Eine Checkliste führt durch Gebäude/Räume, Fächer, Personal/Rollen, Schuljahr, Klassen und Schüler. Erst nach den nötigen Planungsgrundlagen wird die Stundenplananlage freigeschaltet.

## Update von v0.5.0

1. Erstelle vor dem Update ein vollständiges Backup der Datenbank und Anwendungsdateien.
2. In phpMyAdmin die Datenbank auswählen und `sql/update_v0.5.0_to_v0.6.0_ordi.sql` importieren. Bei anderem Tabellenpräfix vorher alle `ordi_`-Vorkommen ersetzen.
3. Prüfe `ordi_schema_migrations` auf `007_school_setup_and_permissions.sql`.
4. Überschreibe die Anwendungsdateien mit v0.6.0. `config.local.php` und installationsspezifische Dateien beibehalten.
5. Anmelden, **Schule einrichten und verwalten** öffnen und die Schritte der Checkliste abarbeiten.

Das Update-SQL ist für Wiederholung ausgelegt. Nach einem Fehler zuerst Backup, Schema und Migrationsmarker prüfen. Neuinstallationen führen Migration 007 über `public/install.php` automatisch aus.

## Schulverwaltung

- Beim Erstellen einer Schule werden ein Hauptgebäude und Standardrollen erzeugt.
- Räume und Fächer werden im Bereich Planungsgrundlagen gepflegt, nicht in der täglichen Planansicht.
- Personen können mehrere Rollen erhalten; Rollen können schulweit oder für Schulformen/Bildungsgänge zugewiesen werden.
- Die Rollen- und Berechtigungsmatrix erlaubt schulindividuelle Rollen und Rechte.
- Klassen/Kurse benötigen Klassenleitung und Stellvertretung. Schüler können ohne Benutzerkonto angelegt und einer Klasse zugeordnet werden.
- Für die spätere Planung müssen Räume, Fächer, Schuljahr, Klasse und Lehrkräfte vorhanden sein.

## Datenschutz und Einsatzgrenzen

Die Version enthält datensparsame Grundfelder, serverseitige Berechtigungsprüfungen und Audit-Ereignisse, ist aber keine pauschale DSGVO-Konformitäts- oder Produktivfreigabe. Vor echtem Einsatz müssen insbesondere Zweck/Rechtsgrundlage, Verantwortlichkeit, Aufbewahrung/Löschung, Hosting, Auftragsverarbeitung, Sicherungen und ein mögliches DSFA-Erfordernis geprüft und freigegeben sein. Bis dahin nur synthetische Testdaten verwenden.

## Tests

PHP, MySQL und Docker waren in der Erstellungsumgebung nicht verfügbar. Laufzeittests und die Ausführung der Migration gegen MySQL/MariaDB wurden daher nicht bestätigt. CI muss PHP-Syntax, Migration, Berechtigungen, Webabläufe und Restore testen.

## Release-Dateien

- [Versionsplan](docs/VERSIONSPLAN.md)
- [Implementierungsstand v0.6.0](docs/IMPLEMENTIERUNGSSTAND_v0.6.0.md)
- [Testprotokoll v0.6.0](docs/TESTPROTOKOLL_v0.6.0.md)
- [SQL-Update von v0.5.0](sql/update_v0.5.0_to_v0.6.0_ordi.sql)
- [Commit-Vorschlag](docs/COMMIT_MESSAGE_v0.6.0.txt)
- [Änderungsprotokoll](CHANGELOG.md)
