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
| 0.0.7 | Zielarchitektur, Technologie-/Hostingbewertung und Anbieter-Prüfliste |
| 0.0.8 | Bedrohungsmodell, Sicherheits-/Testfallmatrix und Freigabegates |
| 0.0.9 | Priorisiertes Backlog, Verantwortlichkeiten, Aufwand und Releasefreigabe v0.1.0 |
| 0.1.0 | Technisches Grundgerüst und Anmeldung |
| 0.2.0 | Schulstammdaten und Rollen |
| 0.3.0–0.6.0 | Stunden-, Raum-, Regel- und Vertretungsplanung mit Assistenz; ab v0.3.0 mit mehreren Schulformen/Bildungsgängen je Schule und schulformübergreifender Lehrkraft-Konfliktprüfung |
| 0.7.0–0.11.0 | Sitzpläne, Klassenbuch, Aufgaben, Termine, Gruppen, Wiki und Nachrichten |
| 0.12.0–0.13.0 | Notenverwaltung und notenfreie Radarspinne |
| 0.14.0–0.15.0 | Zeugnisse, Dashboard und Integration/Administration |
| 0.16.0–0.18.0 | Pilot, kontrollierte Beta und Release Candidate |
| 1.0.0 | Stabile, konkret freigegebene Produktversion |

## Abnahme v0.2.0 – Schulstammdaten und Rollen

- Versionierte Migration ergänzt Benutzername, Schulen, Schuljahre, Mitgliedschaften, schulweite Rollen und Audit-Ereignisse; Neuinstallation und Update von v0.1.2 sind abgedeckt.
- Systemadministration kann Schulen anlegen, erhält dadurch aber keine pädagogischen Datenrechte.
- Schuladministration ist auf aktive Mitgliedschaften der eigenen Schule begrenzt; schulweite Rollen können bei Kontoanlage zugewiesen werden.
- Schuljahre können mit Status und Zeitraum verwaltet werden; nur ein aktuelles Schuljahr pro Schule bleibt nach dem Statuswechsel aktuell.
- Kontozugriff und Mitgliedschaftsstatus werden serverseitig geprüft; zustandsändernde Formulare verlangen CSRF-Schutz.
- Kontoanlage und Mitgliedschaftsänderungen werden mit Akteur und Zeitpunkt protokolliert; Passwörter und IP-Adressen kommen nicht in diese fachlichen Audit-Einträge.
- Rollen gelten in dieser Version nur auf Schulebene; fehlende Klassen-/Kurs-/Fachscopes, Passwortwechsel und Löschworkflow sind dokumentiert.
- Update-SQL mit dem Standardpräfix ordi_ und Hinweise für abweichende Präfixe sind enthalten.
- Datenschutzinventar und Betriebsfreigabe sind offen; ausschließlich synthetische Testdaten. Keine pauschale DSGVO-Konformitätszusage.
- PHP-/MySQL-Laufzeitprüfungen sind vor Release über CI auszuführen; sie wurden in dieser Arbeitsumgebung nicht ausgeführt.

## Abnahme v0.1.0 – technisches Grundgerüst und Anmeldung

- Projektstruktur und lokale Startanleitung sind enthalten; Zugangsdaten/Schlüssel werden nicht als echte Werte eingecheckt.
- Getrennte Umgebungsprofile `local`, `test` und `production`; Produktionsmigration und Produktions-Adminbootstrap sind zusätzlich gesperrt.
- MySQL-Verbindung und versionierte technische Migrationen für Konten und Login-Throttle sind vorhanden.
- Initiale technische Rolle entsteht nur über einen einmaligen CLI-Prozess und erhält keine pädagogischen Datenrechte.
- Anmeldung verlangt sicheres Passwort, verwendet Passwort-Hashing, Session-ID-Wechsel, CSRF-Schutz, sichere Produktionscookies und Login-Begrenzung.
- Nicht angemeldete Personen werden vom geschützten Platzhalter zur Anmeldung geleitet; inaktive/entzogene Konten verlieren den Zugriff.
- CI enthält Syntax-, Migrationswiederholungs-, Authentifizierungs-, CSRF-, HTTP-Smoke- und synthetische Restore-Prüfungen.
- Entwicklungs-/CI-Daten sind synthetisch. Lokale PHP-/Datenbankläufe gelten erst als bestanden, wenn CI ausgeführt wurde.
- Update-, Backup- und Restore-Schritte sind dokumentiert; produktives Backup-/Restoreverfahren bleibt vor echtem Betrieb zu prüfen.
- v0.1.0 behauptet keine DSGVO-Konformität. Verantwortung, Rechtsgrundlagen, Aufbewahrung, Hosting und DSFA-Entscheidung bleiben konkrete Freigabepunkte.

## Versionsregeln

- v0.0.x dokumentiert und prüft Anforderungen, Architektur und Freigaben; sie ist noch keine produktive Software.
- v0.x.0 liefert aufeinander aufbauende, noch veränderbare Funktionen.
- v1.0.0 ist erst freigabefähig, wenn fachliche Abnahme, Betrieb, Sicherheit und Datenschutz für den konkreten Einsatz dokumentiert sind.
- Jede Version erhält einen Commit mit `docs(...)`, `feat(...)`, `fix(...)` oder einem passenden Typ und Versionsbezug.

### Abnahme v0.3.0 – manuelle Stundenplanung

- Eine Schule kann mehrere Schulformen/Bildungsgänge anbieten; Neuinstallation und Migration übernehmen bestehende Einzelschulformen als erstes Angebot.
- Klassen/Kurse werden je Schuljahr einem gültigen Schulformangebot zugeordnet.
- Zeitraster, Fächer, Klassen/Kurse, Lehrkraftzuweisungen und Doppelstunden können gepflegt werden.
- Entwürfe können angelegt, aus freigegebenen Plänen kopiert und nach erfolgreicher Konfliktprüfung freigegeben werden.
- Doppelbelegungen von Klasse und Lehrkraft werden innerhalb des gemeinsamen Schulplans über alle Schulformen hinweg erkannt.
- Neue Änderungen an freigegebenen Planständen erfolgen als neue Fassung; die alte Veröffentlichung wird nachvollziehbar ersetzt.
- Rollen, Schulbereich, CSRF-Schutz, Eingabevalidierung und Audit-Ereignisse gelten auch für Planungsvorgänge.
- Raumkapazitäten, Fachraumprioritäten, automatische Vorschläge, Thüringer Regelengine und echte Schülerdaten sind nicht Teil dieses Releases.
- Datenbankupdate ist mit Backuphinweis und Standardpräfix `ordi_` enthalten; für andere Präfixe wird das Skript angepasst.
- Nur synthetische Testdaten; keine DSGVO- oder Produktivfreigabe wird behauptet.

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


### Abnahme v0.0.7

- Zielarchitektur und Technologieoptionen sind mit Betriebsfolgen dokumentiert.
- PHP/SQL-Zielversionen und Supportstände sind anhand offizieller Quellen geprüft; Kompatibilitätsbedingungen bleiben sichtbar.
- Hostingmodelle einschließlich gemeinsamem Webspace, verwalteter VM/VPS, Managed Platform und Schul-/Trägerbetrieb sind bewertet.
- Konkreter Anbieter bleibt bis zur Prüfung der konkreten Vertrags-/Produktunterlagen und Verantwortlichkeit offen.
- Einzelschulbetrieb ist Startumfang; Mehrschulbetrieb setzt eigene Trennungs- und Datenschutzprüfung voraus.
- Daten-/Dateifluss, Worker, Updates, Backups und Restore sind skizziert.
- Produktivfreigabe setzt Verantwortlichen, AVV soweit einschlägig, Datenstandort-/Zugriffsnachweise, Restore-Test und DSFA-Prüfung voraus.


### Abnahme v0.0.8

- Bedrohungsmodell benennt Schutzobjekte, Akteure, Vertrauensgrenzen und priorisierte Szenarien.
- Tests für Rechte, Planregeln, Bewertungen, Export, Löschung, Anhänge und Restore sind Anforderungen zugeordnet.
- Merge-, Pilot- und Produktionsgates blockieren Freigabe bei kritischen/hohen Sicherheitslücken.
- Testdatenstrategie schließt echte Schülerdaten in Entwicklung/CI aus.
- OWASP ASVS 5.0.0 und NIST SSDF 1.1 sind mit Version/Quelle referenziert.
- Tests sind als geplant und nicht durchgeführt markiert; keine Zertifizierung wird behauptet.


### Abnahme v0.0.9

- Backlog enthält Priorität, Zielversion, Akzeptanz, Abhängigkeiten, Verantwortung und Aufwandsspanne.
- Aufwände sind grobe Planungswerte.
- v0.1.0 beschränkt sich auf technische Basis, Datenbank/Migration, Anmeldung, initiale Adminrolle, CI und Restoretest.
- Keine produktiven Schülerdaten oder schulischen Fachdaten sind v0.1.0-Scope.
- Änderung, Entscheidung, Git-Commit und Releasefreigabe haben dokumentierte Abläufe.
- Offene Datenschutz-/Betriebsfragen bleiben Gates vor Pilot/Produktion.


## v0.4.0 – Raumplanung (umgesetzt)

Räume mit frei definierbaren Typen und optionalen Kapazitäten, optionale Gruppengrößen, Raumzuordnung an Unterrichtsstunden, harte Prüfungen auf Raumbelegung/Kapazität sowie nicht blockierende Hinweise für Fachraumpräferenzen.
