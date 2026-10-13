# OrdiSchool

**Ordinatio Scholae – technische Basis v0.1.2**

v0.1.2 enthält einen klassischen PHP-/MySQL-Webinstaller für einen eigenen Webserver. Er richtet die Datenbanktabellen, das Präfix und das erste Administratorkonto ein. Fachmodule und schulische Daten sind noch nicht enthalten; die Fassung ist ausschließlich mit synthetischen Testdaten zu verwenden.

## Live-Test auf eigenem Webserver

Voraussetzungen: PHP 8.2 oder neuer mit `pdo_mysql`, MySQL 8.x und ein Webserver mit `public` als Dokumentenstamm (Document Root). Der PHP-Prozess braucht während der Ersteinrichtung Schreibrechte im Projektordner, um `config.local.php` zu erstellen.

1. Projektordner auf den Webserver kopieren. Die Domain oder Subdomain so konfigurieren, dass `OrdiSchool/public` der öffentliche Dokumentenstamm ist. `app`, `migrations`, `bin`, `docs` und `config.local.php` müssen außerhalb des öffentlichen Webpfads liegen.
2. Eine leere Datenbank und einen Datenbankbenutzer mit Rechten zum Anlegen und Ändern von Tabellen bereitstellen.
3. `https://DEINE-DOMAIN/install.php` bei vorhandenem HTTPS oder `http://DEINE-DOMAIN/install.php` für den ausdrücklich bestätigten Testbetrieb aufrufen.
4. DB-Host, Port, DB-Name, DB-Benutzer, DB-Passwort und Tabellenpräfix eingeben. Das Präfix ist standardmäßig `ordi_`. Danach Admin-Benutzername und ein Passwort mit mindestens 14 Zeichen festlegen.
5. Nach erfolgreicher Installation mit dem selbst festgelegten Benutzernamen und Passwort anmelden. `config.local.php` liegt außerhalb von `public`, enthält jedoch notwendigerweise DB-Zugangsdaten. Sie darf nicht öffentlich erreichbar, in Git committet oder weitergegeben werden.

Der Installer ist nach der Einrichtung durch die vorhandene Konfigurationsdatei gesperrt. HTTP setzt die Installation in einen unsicheren Testmodus; Zugangsdaten können mitgelesen werden. Verwende dort nur eine separate Testdatenbank und synthetische Daten. Der Login funktioniert über HTTP, aber erst nach HTTPS darf die Instanz produktiv genutzt werden. Die Installation speichert keine eingegebenen Passwörter in Logs. Für den ersten Live-Test nur synthetische Daten nutzen; v0.1.2 enthält noch keine Funktionen für echte Schul- oder Schülerdaten.

## Updates

Bei jeder neuen Version werden die Programmdateien im Projektordner ersetzt. Vorher Datenbank und Dateien sichern. Falls sich das Datenbankschema ändert, enthält das Versionspaket ein passendes SQL-Update mit dem Tabellenpräfix. Dieses vor oder nach dem Dateiaustausch genau nach der Versionsanleitung über phpMyAdmin ausführen. Ein Update setzt weder die Datenbank zurück noch ersetzt es `config.local.php`.

**v0.1.2 benötigt für bestehende v0.1.0-Datenbanken kein SQL-Update:** Es kommen keine neuen Datenbankspalten oder Tabellen hinzu. Das Tabellenpräfix wird bei einer Neuinstallation für die bestehenden Tabellen angewendet. Für vorhandene lokale Konfigurationen ohne `ORDI_DB_PREFIX` gilt weiterhin das leere Präfix.

Vor dem produktiven Einsatz über HTTPS die Konfiguration auf `environment = production` und `secure_cookies = true` umstellen. Die Schritte stehen in [HTTPS später aktivieren](docs/HTTPS_UMSTELLUNG_v0.1.2.md).

## Lokaler Schnelltest

Für Entwicklung bleibt `bash test.sh` mit Docker Compose verfügbar. Das Skript startet eine temporäre Datenbank und Webanwendung auf `http://127.0.0.1:8080`; Zugangsdaten werden im Terminal angezeigt. Mit Strg+C werden Container und Datenbankvolume entfernt.

## Tests

Die CI prüft PHP-Syntax, wendet Migrationen auf eine leere MySQL-Testdatenbank zweimal an, testet Passwort-, Login-, Session- und CSRF-Grundverhalten, prüft die Weiterleitung unangemeldeter Zugriffe und führt einen synthetischen Datenbank-Backup/Restore-Smoke-Test aus.

Für lokale Tests muss eine **separate, wegwerfbare Testdatenbank** verwendet werden. `ORDI_ENV=test` verhindert das Laden der lokalen `.env`; Werte müssen explizit gesetzt werden:

```sh
export ORDI_ENV=test
export ORDI_APP_KEY="$(php bin/generate-key.php)"
export ORDI_SESSION_SECURE_COOKIE=false
export ORDI_DB_HOST=127.0.0.1 ORDI_DB_PORT=3306
export ORDI_DB_NAME=ordischool_test ORDI_DB_USER=ordischool_test
export ORDI_DB_PASSWORD='nur-fuer-diese-testdatenbank'
php bin/migrate.php
php bin/migrate.php
php tests/run.php
bash bin/restore-smoke.sh
```

Der Restore-Smoke-Test verlangt `ORDI_ENV=test` und einen Datenbanknamen mit `_test` am Ende. Er legt eine synthetische Prüftabelle an, sichert sie, löscht sie, stellt sie wieder her und entfernt sie danach. Er ist kein Produktions-Backupverfahren.

## Datenschutz- und Sicherheitsgrenzen v0.1.2

- Gespeichert werden nur technische Administrations-E-Mail, Passwort-Hash, Rolle, Aktivstatus und technische Zeitstempel.
- Login-Begrenzungen speichern HMAC-pseudonymisierte IP-/E-Mail-Buckets. Roh-IP und Klartext-E-Mail werden hierfür nicht in der Throttle-Tabelle gespeichert. Abgelaufene Einträge werden bei Login-Aufrufen gelöscht; ohne weiteren Login-Aufruf bleiben sie bis zur nächsten Bereinigung erhalten. 24 Stunden ist nur der technische Löschschwellenwert, keine rechtlich geprüfte Aufbewahrungsfrist.
- Session-Cookies sind `HttpOnly`, `SameSite=Lax` und im Produktionsprofil `Secure`; nach erfolgreicher Anmeldung wird die Session-ID erneuert. Formulare verwenden CSRF-Tokens.
- Kein Telemetrie-, Analyse-, CDN-, E-Mail- oder sonstiger externer Dienst ist eingebunden.
- Es gibt keine Datenschutz-Zertifizierung und keine pauschale DSGVO-Konformitätszusage. Verantwortlichkeit, Rechtsgrundlage, Information, Aufbewahrung, Hosting, Verträge und eine mögliche DSFA sind vor einem konkreten Schulbetrieb durch die zuständigen Stellen zu prüfen.
- Ausschließlich synthetische Testdaten verwenden. Keine echten Schülerinnen-/Schüler-, Beschäftigten- oder Schuldaten in Entwicklung/CI einbringen.

## Dokumentation

- [Umsetzungsstand und Datenschutzinventar v0.1.2](docs/IMPLEMENTIERUNGSSTAND_v0.1.2.md)
- [Testprotokoll v0.1.0](docs/TESTPROTOKOLL_v0.1.0.md)
- [Update-, Backup- und Restore-Ablauf](docs/UPDATE_UND_RESTORE_v0.1.2.md)
- [Projektanforderungen](docs/ANFORDERUNGEN.md)
- [Versionsplan](docs/VERSIONSPLAN.md)
- [Änderungsprotokoll](CHANGELOG.md)
- [Commit-Vorschlag](docs/COMMIT_MESSAGE_v0.1.2.txt)

Die früheren Planungs-, Rollen-, Thüringen-, Datenmodell-, Datenschutz- und Sicherheitsdokumente bleiben Teil des Projektstands; sie sind Entwurfs-/Prüfgrundlagen und keine fachliche oder rechtliche Freigabe.
