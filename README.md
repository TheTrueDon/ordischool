# OrdiSchool

**Ordinatio Scholae – technische Basis v0.1.0**

OrdiSchool ist als integrierte Schulsoftware für Planung, Vertretung, Klassenbuch, Aufgaben, Kommunikation und Bewertung geplant. Diese Version liefert ausschließlich eine lokale technische Basis mit Datenbankmigrationen und Anmeldung. Sie enthält keine schulischen Fachmodule und ist nicht für echte Schul- oder Schülerdaten freigegeben.

## Voraussetzungen

- PHP 8.5 CLI mit `pdo_mysql` (Zielversion gemäß Architekturentscheidung)
- MySQL 8.4 LTS
- optional Docker Compose v2 zum lokalen Start der Datenbank
- Bash und MySQL-Client nur für den Restore-Smoke-Test

Es gibt keine PHP-Paketabhängigkeiten von Drittanbietern. Die Anwendung nutzt PHP-Core, PDO und die MySQL-Erweiterung.

## Lokaler Start

1. Projekt lokal auschecken, `.env.example` als `.env` kopieren und die Datei schützen:

   ```sh
   cp .env.example .env
   chmod 600 .env
   ```
2. Einen Schlüssel erzeugen und in `.env` als `ORDI_APP_KEY` eintragen:

   ```sh
   php bin/generate-key.php
   ```

3. Datenbankname, Nutzer, Passwort und ein separates Root-Passwort in `.env` setzen. Für lokale Entwicklung mindestens 32 zufällige Zeichen je Passwort verwenden.
4. Datenbank starten (oder eine eigene lokale MySQL-8.4-Instanz konfigurieren):

   ```sh
   docker compose up -d db
   ```

   Der Container bindet den Port nur an `127.0.0.1`. Compose ist ausschließlich für lokale Entwicklung gedacht.
5. Migrationen anwenden:

   ```sh
   php bin/migrate.php
   ```

6. Einmaliges technisches Administratorkonto aus einem vertrauenswürdigen Terminal anlegen. Das Passwort steht nicht in der Shell-History und darf nicht in `.env` geschrieben werden:

   ```sh
   read -r -p 'Admin-E-Mail: ' ORDI_BOOTSTRAP_ADMIN_EMAIL
   read -r -s -p 'Admin-Passwort (mindestens 14 Zeichen): ' ORDI_BOOTSTRAP_ADMIN_PASSWORD
   printf '\n'
   export ORDI_BOOTSTRAP_ADMIN_EMAIL ORDI_BOOTSTRAP_ADMIN_PASSWORD
   php bin/create-admin.php
   unset ORDI_BOOTSTRAP_ADMIN_EMAIL ORDI_BOOTSTRAP_ADMIN_PASSWORD
   ```

7. Anwendung lokal starten:

   ```sh
   php -S 127.0.0.1:8080 -t public public/router.php
   ```

8. `http://127.0.0.1:8080/login` im Browser öffnen. Der lokale HTTP-Modus ist ausschließlich für Loopback-Entwicklung; produktive Sessions erfordern HTTPS und sichere Cookies.

Die Migrationen sind versioniert. Da MySQL-DDL nicht transaktional ist, prüfe nach einem fehlgeschlagenen Migrationslauf das Schema, bevor du erneut startest. Produktionsmigrationen sind standardmäßig gesperrt und benötigen ein geprüftes Backup sowie eine bewusste zusätzliche Freigabevariable.

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

## Datenschutz- und Sicherheitsgrenzen v0.1.0

- Gespeichert werden nur technische Administrations-E-Mail, Passwort-Hash, Rolle, Aktivstatus und technische Zeitstempel.
- Login-Begrenzungen speichern HMAC-pseudonymisierte IP-/E-Mail-Buckets. Roh-IP und Klartext-E-Mail werden hierfür nicht in der Throttle-Tabelle gespeichert. Abgelaufene Einträge werden bei Login-Aufrufen gelöscht; ohne weiteren Login-Aufruf bleiben sie bis zur nächsten Bereinigung erhalten. 24 Stunden ist nur der technische Löschschwellenwert, keine rechtlich geprüfte Aufbewahrungsfrist.
- Session-Cookies sind `HttpOnly`, `SameSite=Lax` und im Produktionsprofil `Secure`; nach erfolgreicher Anmeldung wird die Session-ID erneuert. Formulare verwenden CSRF-Tokens.
- Kein Telemetrie-, Analyse-, CDN-, E-Mail- oder sonstiger externer Dienst ist eingebunden.
- Es gibt keine Datenschutz-Zertifizierung und keine pauschale DSGVO-Konformitätszusage. Verantwortlichkeit, Rechtsgrundlage, Information, Aufbewahrung, Hosting, Verträge und eine mögliche DSFA sind vor einem konkreten Schulbetrieb durch die zuständigen Stellen zu prüfen.
- Ausschließlich synthetische Testdaten verwenden. Keine echten Schülerinnen-/Schüler-, Beschäftigten- oder Schuldaten in Entwicklung/CI einbringen.

## Dokumentation

- [Umsetzungsstand und Datenschutzinventar v0.1.0](docs/IMPLEMENTIERUNGSSTAND_v0.1.0.md)
- [Testprotokoll v0.1.0](docs/TESTPROTOKOLL_v0.1.0.md)
- [Update-, Backup- und Restore-Ablauf](docs/UPDATE_UND_RESTORE_v0.1.0.md)
- [Projektanforderungen](docs/ANFORDERUNGEN.md)
- [Versionsplan](docs/VERSIONSPLAN.md)
- [Änderungsprotokoll](CHANGELOG.md)
- [Commit-Vorschlag](docs/COMMIT_MESSAGE_v0.1.0.txt)

Die früheren Planungs-, Rollen-, Thüringen-, Datenmodell-, Datenschutz- und Sicherheitsdokumente bleiben Teil des Projektstands; sie sind Entwurfs-/Prüfgrundlagen und keine fachliche oder rechtliche Freigabe.
