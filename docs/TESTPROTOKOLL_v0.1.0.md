# Testprotokoll v0.1.0

**Status:** Testdefinition und CI sind erstellt; erfolgreicher Lauf wurde in dieser Arbeitsumgebung noch nicht nachgewiesen.

## Geplante CI-Prüfungen

| Prüfschritt | Abdeckung | erwartetes Ergebnis |
|---|---|---|
| PHP-Lint | alle PHP-Dateien unter `app`, `public`, `bin`, `tests` | jeder Aufruf `php -l` erfolgreich |
| Migration leerer Test-DB | Schema- und Throttle-Tabellen | jede neue Migration wird angewendet |
| Migration erneut anwenden | Wiederholbarkeit | keine doppelte Migration, kein Fehler |
| Integrationstest | Passwortregeln/Hash, E-Mail-Normalisierung, richtiger/falscher Login, deaktiviertes Konto, CSRF | alle Prüfungen bestehen, Testkonto wird entfernt |
| HTTP-Smoke-Test | unangemeldeter Zugriff auf `/`, Loginansicht | Weiterleitung 303 zu `/login`, Loginseite liefert 200 |
| Restore-Smoke-Test | synthetische Tabelle in Datenbank mit `_test`-Suffix | Backup, Löschen, Wiederherstellen und Integritätsabfrage erfolgreich |

Alle Testdaten sind synthetisch; E-Mail-Adressen verwenden die reservierte Domain `example.invalid`, die IP-Testadresse liegt im Dokumentationsnetz `192.0.2.0/24`.

## Tatsächlicher Laufstatus

Im hier verfügbaren Arbeitscontainer sind PHP, Composer und Docker nicht installiert. Deshalb konnten PHP-Syntax, MySQL-Integration, HTTP-Smoke, Restore-Smoke und der neue Docker-Schnellstart hier nicht ausgeführt werden. Die CI-Workflow-Datei ist angelegt, aber ihr Ergebnis muss nach dem Einspielen in das Git-Repository geprüft werden. Bis dahin sind diese Prüfschritte **nicht als bestanden** zu werten.

## Bekannte Testgrenzen

- Kein Browser-/Accessibility-Test und keine Last-/Race-/Penetrationstests.
- Keine unabhängige OWASP-ASVS-Verifikation.
- Produktionsmigration, TLS/HTTPS, Proxy-/Hostkonfiguration, Session-Speicher und Produktiv-Backup wurden nicht getestet.
- Kein Test mit echten Personen- oder Schuldaten; ein solcher Test gehört nicht in CI.
