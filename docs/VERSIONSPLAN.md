# Versionsplan

Der vollständige Umfang und die Abnahmekriterien stehen im beiliegenden Projektplan. Diese Kurzfassung ordnet die Hauptlieferungen zu.

| Version | Liefergegenstand |
|---|---|
| 0.0.1 | Projektdefinition, Anforderungen, Datenschutzleitplanken und offene Entscheidungen |
| 0.0.2 | Rollen- und Rechteentwurf, Kernabläufe, Sichtbarkeit, Prüfung/Freigabe und offene Schulentscheidungen |
| 0.0.3 | Thüringer Regeln und Planungsbedingungen mit Quellenregister |
| 0.0.4 | Datenschutz- und Datenflussentwurf |
| 0.0.5 | Interaktionsprototyp |
| 0.0.6 | Fachliches Datenmodell |
| 0.0.7 | Architektur- und Hostingentscheidung |
| 0.0.8 | Sicherheits- und Teststrategie |
| 0.0.9 | Priorisiertes Backlog und Freigabeumfang für v0.1.0 |
| 0.1.0 | Technisches Grundgerüst und Anmeldung |
| 0.2.0 | Schulstammdaten und Rollen |
| 0.3.0–0.6.0 | Stunden-, Raum-, Regel- und Vertretungsplanung mit Assistenz |
| 0.7.0–0.11.0 | Sitzpläne, Klassenbuch, Aufgaben, Termine, Gruppen, Wiki und Nachrichten |
| 0.12.0–0.13.0 | Notenverwaltung und notenfreie Radarspinne |
| 0.14.0–0.15.0 | Zeugnisse, Dashboard und Integration/Administration |
| 0.16.0–0.18.0 | Pilot, kontrollierte Beta und Release Candidate |
| 1.0.0 | Stabile, konkret freigegebene Produktversion |

## Versionsregeln

- v0.0.x dokumentiert und prüft Anforderungen, Architektur und Freigaben; sie ist noch keine produktive Software.
- v0.x.0 liefert aufeinander aufbauende, noch veränderbare Funktionen.
- v1.0.0 ist erst freigabefähig, wenn fachliche Abnahme, Betrieb, Sicherheit und Datenschutz für den konkreten Einsatz dokumentiert sind.
- Jede Version erhält einen Commit mit `docs(...)`, `feat(...)`, `fix(...)` oder einem passenden Typ und Versionsbezug.

### Abnahme v0.0.2

- Rollen- und Rechte-Matrix deckt Planung, Vertretung, Klassenbuch, Aufgaben, Bewertungen, Zeugnisse, Kommunikation, Kalender und Konten ab.
- Prozessdokumentation benennt pro Kernvorgang Ausführende, Sichtbarkeit, Freigabe und wesentliche Ausnahmen.
- Offene Rollen- und Schulentscheidungen sind als vorläufig gekennzeichnet.
- Datenschutzanforderungen an Scope, Anhänge, Benachrichtigungen, Exporte und Protokollierung sind dokumentiert.
- Kein ausführbarer Code und keine echten personenbezogenen Daten sind Teil dieser Version.
