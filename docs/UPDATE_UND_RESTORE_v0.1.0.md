# Update, Backup und Restore v0.1.0

## Updateablauf für lokale Entwicklungs-/Testumgebung

1. Änderung, CI-Ergebnis und Versionshinweise prüfen.
2. Für jede Datenbank einen zeitgestempelten Datenbankdump erzeugen und die Datei ausschließlich in einem zugriffsbeschränkten lokalen Verzeichnis außerhalb des Repositories ablegen.
3. Vor jedem Update die Anwendung in den Wartungszustand bringen bzw. keine parallelen Schreibzugriffe zulassen.
4. Anwendungscode aktualisieren und `php bin/migrate.php` ausführen. Migrationen erhalten fortlaufende Nummern und werden in `schema_migrations` dokumentiert.
5. Health- und Login-Smoke-Test ausführen; CI-Restore-Smoke erneut durchführen.
6. Bei Fehlern Zugriff sperren, Fehlerklasse/Referenz sichern und Datenbankschema samt Migrationstabelle untersuchen. Nicht blind dieselbe DDL-Migration erneut ausführen.

MySQL-DDL führt implizite Commits aus. Die Migrationen dieses Releases sind deshalb nicht als transaktional rückrollbar zu behandeln. Ein Code-Rollback setzt nicht automatisch das Schema zurück.

## Synthetischer Backup-/Restore-Test

Der automatisierte Test `bash bin/restore-smoke.sh` verlangt `ORDI_ENV=test` und einen Datenbanknamen, der auf `_test` endet. Er erstellt ausschließlich die Tabelle `restore_probe` mit einem synthetischen Marker, dumpt diese Tabelle in ein temporäres Verzeichnis, löscht und restauriert sie, prüft den Marker und löscht die Prüftabelle. Der Test ist destruktiv für eine eventuell bereits vorhandene Tabelle gleichen Namens; deshalb niemals gegen eine andere oder produktive Datenbank ausführen.

## Datenbanksicherung

Beispiel für einen **lokalen synthetischen Teststand** (Zugangsdaten im Prozessumfeld; Dumpzugriff lokal schützen):

```sh
umask 077
export MYSQL_PWD="$ORDI_DB_PASSWORD"
mysqldump --protocol=tcp --host="$ORDI_DB_HOST" --port="$ORDI_DB_PORT" \\
  --user="$ORDI_DB_USER" --single-transaction --no-tablespaces \\
  "$ORDI_DB_NAME" > "/tmp/${ORDI_DB_NAME}-$(date -u +%Y%m%dT%H%M%SZ).sql"
unset MYSQL_PWD
```

Wiederherstellung nur in eine eigens bereitgestellte, isolierte Testdatenbank mit geprüftem Dump und passend berechtigtem DB-Konto durchführen. Dumps enthalten Kontoinformationen und sind daher vertraulich zu behandeln. Keine Dumps in Git, Tickets, Chat oder unverschlüsselten gemeinsamen Ordner ablegen.

## Produktionsgrenzen

Dieser Ablauf ist keine Betriebsfreigabe oder vollständige Backup-Strategie. Vor echtem Betrieb sind Zielsystem, Verschlüsselung, Schlüsselverwaltung, Zugriffskontrolle, Aufbewahrung, externe Sicherungsorte, Wiederherstellungsziele, Verantwortlichkeiten, Restore-Frequenz und dokumentierter Wiederherstellungsnachweis konkret festzulegen. Migrationen im Produktionsprofil erfordern die zusätzliche Variable `ORDI_ALLOW_PRODUCTION_MIGRATIONS=YES`; das ist eine technische Sperre und ersetzt keine Freigabe.
