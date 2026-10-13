# Update und Restore – v0.1.2

## Dateiupdate

1. Vor jedem Update Webdateien und Datenbank sichern. `config.local.php` außerhalb des Dokumentenstamms separat sichern.
2. Die neue Version entpacken und Programmdateien ersetzen. `config.local.php` erhalten.
3. Wenn das Versionspaket ein SQL-Update enthält: Präfix im Skript auf das bei der Installation verwendete Präfix prüfen und das Update über phpMyAdmin ausführen.
4. Anmeldung, Startseite und Datenbankverbindung prüfen.

Für v0.1.2 ist kein SQL-Update erforderlich, weil keine Schemaänderung enthalten ist. Der Webinstaller legt bei Neuinstallation `ordi_schema_migrations`, `ordi_users` und `ordi_auth_throttle` an.

## Sicherungen

Vor Änderungen Datenbankexport in phpMyAdmin erstellen und eine Dateisicherung außerhalb des öffentlichen Webverzeichnisses anlegen. Sicherungen enthalten Zugangsdaten oder später möglicherweise personenbezogene Daten und müssen entsprechend geschützt werden. Wiederherstellung regelmäßig in einer getrennten Testumgebung prüfen.

`config.local.php` enthält DB-Zugangsdaten und einen kryptografischen App-Schlüssel. Sie darf nicht öffentlich erreichbar sein und nicht in Git oder ungeschützte Backups gelangen.

Bei einer über HTTP angelegten Testinstanz vor produktiver Nutzung HTTPS einrichten und `config.local.php` auf `environment = production` sowie `secure_cookies = true` umstellen. Zusätzlich muss der Webserver HTTP-Anfragen auf HTTPS umleiten. Details: `HTTPS_UMSTELLUNG_v0.1.2.md`.
