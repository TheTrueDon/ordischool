# Versionsplan

Der vollständige Umfang und die Abnahmekriterien stehen im beiliegenden Projektplan. Diese Kurzfassung ordnet die Hauptlieferungen zu.

| Version | Liefergegenstand |
|---|---|
| 0.0.1 | Projektdefinition, Anforderungen, Datenschutzleitplanken und offene Entscheidungen |
| 0.0.2 | Rollen- und Rechteentwurf, Kernabläufe, Sichtbarkeit, Prüfung/Freigabe und offene Schulentscheidungen |
| 0.0.3 | Thüringer Regeln und Planungsbedingungen mit Quellenregister |
| 0.0.4 | Datenschutz- und Datenflussentwurf |
| 0.0.5 | Interaktionsprototyp |
| 0.0.6 | Fachliches Datenmodell, Datenwörterbuch, Versionierung sowie erste Migrations-/Löschstrategie |
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

### Abnahme v0.0.3

- Thüringer Quellenregister enthält Herausgeber, Fundstelle, Zeitbezug und Verwendungsgrenze.
- Regelprofil trennt Landesrecht, befristete Jahresvorgabe, Schulbeschluss, lokale Gebäude-/Schulbedingung und Präferenz.
- Übergangsregeln, Schulart, Jahrgang, Kohorte und Schuljahr sind als Auswahlmerkmale vorgesehen.
- Raumkapazität/Fachraumpriorität werden nur mit lokalem Nachweis aktiviert.
- Ungeprüfte oder nicht abgerufene Quellentexte sind sichtbar und nicht als aktive Softwareregel ausgegeben.
- Kein ausführbarer Code und keine echten personenbezogenen Daten sind Teil dieser Version.

### Abnahme v0.0.4

- Dateninventar deckt alle geplanten Kernmodule, Betroffene, Zwecke, Datenarten, Sichtbarkeiten und logischen Speicherklassen ab.
- Datenflüsse für Eingabe, Nutzung, Anbieter, Anhänge, Logs, Export, Backup/Restore und Löschung sind beschrieben.
- Rechtsgrundlage, Verantwortlichkeit und Aufbewahrung bleiben sichtbar offene Einzelfallentscheidungen.
- Thüringer Schuldatenschutzquellen einschließlich Spezialregeln und ihres Anwendungsbereichs sind eingetragen.
- DSFA-Screening benennt Risiken, Verantwortliche und den Freigabepunkt vor Pilot/Produktivbetrieb.
- Kein ausführbarer Code und keine echten personenbezogenen Daten sind Teil dieser Version.

### Abnahme v0.0.5

- Klickbarer Entwurf macht Planbearbeitung und Konfliktprüfung nachvollziehbar.
- Ansichten für persönlichen Stundenplan, Vertretung, Klassenbuchhinweise, Aufgaben und Dashboard sind vorhanden.
- Vorschläge und Entwürfe werden sichtbar von menschlicher Freigabe und Veröffentlichung getrennt.
- Feedbackprotokoll und offene Usability-Fragen sind enthalten; nicht erfolgte Nutzertests werden nicht als durchgeführt dargestellt.
- Prototyp verwendet nur synthetische Daten und keine externen Dienste oder dauerhafte Datenspeicherung.


### Abnahme v0.0.6

- Objekte und Beziehungen sind in FACHLICHES_DATENMODELL.md beschrieben.
- Stammdatenquellen sind eindeutig; Planung, Unterrichtsereignis, Vertretung und Klassenbuchnachweis sind getrennt.
- Versionierungs- und Freigabeprinzipien sind dokumentiert.
- Datenwörterbuch benennt Pflichtfelder, bedingte Felder und Datenschutz-/Lifecycle-Hinweise.
- Migration/Löschung berücksichtigt Sicherungen, Tests, Rückfallgrenzen, Anhänge und Exporte.
- Keine Datenbank, Hostingumgebung, pauschale Rechtsgrundlage oder Aufbewahrungsfrist festgelegt.
- Nur synthetische Daten; kein Produktivcode und keine echte Schülerdatenverarbeitung.
