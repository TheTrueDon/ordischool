# Umsetzungsstand und Datenschutzinventar v0.1.2

**Version:** 0.1.2 · **Stand:** 13. Oktober 2026 · **Status:** Entwicklungsfassung; CI-Ausführung noch erforderlich

## Umgesetzter Umfang

Die Anwendung bietet einen technischen Login, Logout und eine geschützte Platzhalterseite. Es gibt noch keine Schulstammdaten, Klassen, Schüler-/Beschäftigtenprofile, Stundenpläne, Klassenbuch-, Noten-, Aufgaben-, Nachrichten- oder Zeugnisfunktionen. Die Systemadministration ist in dieser Version kein pädagogisches Benutzerkonto und erhält keine Fachdatenrechte.

## Dateninventar dieser Version

| Daten | Zweck | Speicher / Empfänger | Lösch-/Aufbewahrungshinweis |
|---|---|---|---|
| Administrations-E-Mail | Login und Anzeige des angemeldeten technischen Kontos | Tabelle `users` in der konfigurierten MySQL-Datenbank | Löschung/Deaktivierung technisch möglich, konkrete Frist für Betrieb festzulegen |
| Passwort-Hash | Passwortprüfung, ohne Klartextpasswort zu speichern | Tabelle `users`; Argon2id, wenn verfügbar, andernfalls PHP-Standardalgorithmus | mit dem Konto löschen/ersetzen; keine Passwörter in Logs oder Commit |
| Rolle `system_admin`, Aktivstatus, Erstell- und letzter Loginzeitpunkt | technischer Zugriff und Kontoverwaltung | Tabelle `users` | technische Audit-/Aufbewahrungsentscheidung vor Betrieb festzulegen |
| Session-ID und CSRF-Token | Anmeldung und Schutz zustandsändernder Formulare | Session-Cookie im Browser; serverseitige PHP-Session-Ablage des Hosts | Session-Cookie endet beim Schließen des Browsers; serverseitige Session-Cleanup-Konfiguration ist noch Betreiberaufgabe |
| HMAC-pseudonymisierte IP-/IP-E-Mail-Buckets, Zähler, Zeitfenster | Begrenzung von Loginversuchen | Tabelle `auth_throttle` in derselben DB; kein externer Empfänger | Abgelaufene Buckets werden bei Login-Aufrufen gelöscht. Ohne weitere Login-Aufrufe bleiben sie bis zur nächsten Bereinigung gespeichert; für Betrieb ist ein geplanter Cleanup-Lauf einzurichten. 24h ist der technische Löschschwellenwert, keine rechtlich freigegebene Frist. |
| Technische Fehlerreferenz und Ausnahmeklasse | Fehlersuche ohne Klartextdetails | PHP-Fehlerlog des Hosts | Logzugriff und Fristen sind durch den Betreiber noch festzulegen |

Die IP-Adresse wird nur im Loginprozess zur HMAC-Berechnung verwendet. `X-Forwarded-For` wird nicht ausgewertet. Die HMAC-Werte sind pseudonyme und damit weiterhin als schutzbedürftig zu behandeln; sie sind nicht anonym.

## Technische Schutzmaßnahmen

- Passwörter ab 14 Unicode-Zeichen; Argon2id akzeptiert bis 1024 Bytes. Falls die PHP-Laufzeit kein Argon2id bereitstellt, greift `PASSWORD_DEFAULT` und die Eingabe wird auf maximal 72 Bytes begrenzt, damit keine bcrypt-Abschneidung zu uneindeutigen Passwörtern führt.
- PDO mit nativen Prepared Statements, utf8mb4 und UTC-Datenbankzeit.
- Generische Loginfehler, Dummy-Hash-Prüfung für unbekannte Konten, IP- und IP/E-Mail-Loginbegrenzung.
- Session-Cookie `HttpOnly`, `SameSite=Lax`, im Produktionsprofil `Secure`; Session-ID-Wechsel nach Anmeldung, Logout zerstört die Session.
- CSRF-Token für Login und Logout, Content-Security-Policy, `X-Frame-Options`, `nosniff`, private/no-store und HSTS im Produktionsprofil.
- Produktivprofile erzwingen sichere Sessioncookies; produktive Migrationen und Admininitialisierung sind standardmäßig gesperrt.
- Kein Drittanbieter-JavaScript, Tracking, Telemetrie, externe Schriftart, CDN oder Dateiupload.

Dies ist eine technische Beschreibung, kein vollständiger Nachweis der Sicherheit oder Rechtskonformität. Eine unabhängige Prüfung nach dem im Projektplan vorgesehenen Sicherheitsniveau steht aus.

## DSGVO-/Schuldatenschutz-Prüfpunkte vor echtem Betrieb

Vor einer Nutzung durch Schule oder Träger sind für den konkreten Einsatz unter anderem Verantwortlichkeit, Zwecke und Rechtsgrundlagen, Informationspflichten, Rollen/Weisungen, Hosting und Zugriffsorte, Verträge mit Auftragsverarbeitern, sichere Schlüssel-/Sessionablage, tatsächliche Löschfristen, Sicherungs-/Restore-Verfahren, Betroffenenprozesse und die DSFA-Erforderlichkeit durch die zuständigen Stellen zu prüfen. Die Software behauptet keine DSGVO-Konformitätsbescheinigung. Bis zu diesen Freigaben bleiben ausschließlich lokale synthetische Tests zulässig.


## Bereitstellung v0.1.2

Die Webinstallation erfolgt über `public/install.php` unter HTTPS. Die Konfiguration wird als `config.local.php` außerhalb des öffentlichen `public`-Dokumentenstamms abgelegt. Der Installer erstellt Tabellen mit dem konfigurierten Präfix; Standard ist `ordi_`.

Das Admin-Login akzeptiert den Installationsbenutzernamen. Für die bestehende v0.1.0-Datenbankstruktur wird dieser intern als Adresse unter der reservierten Domain `ordischool.invalid` abgelegt; es wird keine echte E-Mail-Kommunikation ausgelöst.

## Temporärer HTTP-Testmodus

Ohne Zertifikat kann die Erstinstallation nach ausdrücklicher Bestätigung über HTTP erfolgen. Sie speichert `environment = test` und `secure_cookies = false`, damit die Anmeldung während des synthetischen Tests funktioniert. Zugangsdaten können dabei im Netzwerk mitgelesen werden. Vor produktivem Betrieb HTTPS aktivieren und die in `HTTPS_UMSTELLUNG_v0.1.2.md` beschriebenen Einstellungen setzen.
