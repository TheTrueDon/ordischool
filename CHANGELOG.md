# Änderungsprotokoll

## v0.6.0

- Schulinitialisierung mit automatisch erzeugtem Hauptgebäude, Rollen und vollständigen Rechten des Installationskontos ergänzt.
- Geführte Checkliste für Gebäude/Räume, Fächer, Personal, Schuljahr, Klassen und Schüler mit Menülinks ergänzt.
- Personalstammdaten um Vor- und Nachnamen erweitert; Mehrfachrollen und schulformbezogene Zuordnung per Checkbox-Tabelle ergänzt.
- Rollenverwaltung mit tabellarischer Berechtigungsmatrix und anlegbaren Schulrollen ergänzt.
- Klassen/Kurse erfordern Klassenleitung und Stellvertretung; Schüler können minimal erfasst und Klassen zugeordnet werden.
- Stundenplananlage bleibt bis zur Anlage der erforderlichen Planungsgrundlagen gesperrt.
- Wiederholbare Migration `007_school_setup_and_permissions.sql` und phpMyAdmin-Update von v0.5.0 ergänzt.
- Geplante Vertretungsassistenz rückt auf v0.7.0.


## v0.5.0

- Stammdatenpflege aus der täglichen Planansicht entfernt und in `/admin/planning/settings` ausgelagert.
- Raum-, Fach-, Schulform-, Klassen- und Zeitrasterpflege sowie Regelprofile serverseitig auf aktive Schuladministration begrenzt.
- Versionierte Regelprofile nach Schuljahr, Schulform und Kohorte mit Quelle, Fundstelle, Prüfdatum, Prüfstatus und Prüferkonto ergänzt.
- Regelprüfbericht unterscheidet harte Zeit-/Raum-/Klassenkonflikte, weiche Hinweise und offene Quellen-/Regelstände.
- Prüfstände halten die verwendete Profilversion fest und werden bei Profiländerungen zur erneuten Prüfung markiert; Veröffentlichung ist ohne aktuellen Prüfstand gesperrt.
- Datenschutz-/Rechtsgrenze dokumentiert: gespeicherte Quellenprüfung ist keine vollständige Regelengine und kein Rechtskonformitätsnachweis.
- Wiederholbare Migration `006_planning_rule_profiles.sql` und phpMyAdmin-Update von v0.4.1 bereitgestellt.

## [0.4.1] – 2026-10-13
- Kontoverwaltung zeigt nach erfolgreicher Kontoanlage eine sichtbare Bestätigung.
- Formularhinweise erläutern Benutzername- und Passwortregeln und weisen auf die Mitgliederliste hin.
- Keine Datenbankänderung; kein SQL-Update erforderlich.


## [0.4.0] – 2026-10-13

### Hinzugefügt
- Schulbezogene Raumverwaltung mit frei definierbaren Raumtypen und optionaler Kapazität.
- Optional gepflegte Gruppengrößen für neue und bestehende Klassen/Kurse.
- Fachraumpräferenzen je Fach mit nicht blockierenden Hinweisen bei Abweichung oder fehlendem Fachraum.
- Raumzuweisung für Unterricht, inklusive harter Doppelbelegungs- und Kapazitätsprüfung sowie erneuter Prüfung bei Freigabe.
- Wiederholbares SQL-Update von v0.3.0 auf v0.4.0.

### Grenzen
- Regelprofile für Thüringen und automatische Änderungsvorschläge bleiben Folgeschritte. Nur synthetische Testdaten; keine DSGVO- oder Produktivfreigabe.
- PHP-/MySQL-Laufzeittests müssen in der Projekt-CI ausgeführt werden.


## [0.3.0] – 2026-10-13

### Hinzugefügt
- Manuelle Stundenplanung mit zeitlich gültigen Schulform-/Bildungsgangangeboten, Klassen/Kursen, Fächern und Schulzeitrastern.
- Mehrfachauswahl der Schulformen bei der Schulanlage; bestehende Schulen werden per Migration aus ihrem bisherigen Einzelfeld übernommen.
- Planentwürfe und versionierte Freigaben einschließlich Kopie einer veröffentlichten Fassung in einen neuen Entwurf.
- Einzel- und Doppelstunden sowie Konfliktprüfung für Klasse und Lehrkraft über alle Schulformen der Schule hinweg.
- phpMyAdmin-Update v0.2.0 auf v0.3.0 mit Tabellenpräfix `ordi_`.

### Grenzen
- Raumplanung, Thüringer Regelengine, automatische Vorschläge und Vertretungsplanung folgen später.
- Nur synthetische Testdaten; keine DSGVO- oder Produktivfreigabe.
- PHP-/MySQL-Laufzeittests waren in der Erstellungsumgebung nicht ausführbar.

## [0.2.0] – 2026-10-13

### Hinzugefügt
- Schulstammdaten und Schuljahre mit Statusverwaltung.
- Schulmitgliedschaften und schulweite Rollen für Schuladministration, Planung, Lehrkräfte, Klassenleitung, Fachleitung, Sekretariat und Schüler/innen.
- Geschützter Verwaltungsbereich mit serverseitiger Schul- und Rollenprüfung.
- Kontoanlage mit Startpasswort, Mitgliedschaftssperre/-reaktivierung und Audit-Einträgen.
- Migration 003 sowie phpMyAdmin-Update von v0.1.2 mit Standardpräfix ordi_.
- Migration entfernt den alten Rollen-Constraint nur dynamisch, wenn die Serverversion ihn tatsächlich gespeichert hat; Wiederholung nach einem Teilabbruch ist abgesichert.
- Installer und CLI-Migration prüfen die bestehende username-Spalte zusätzlich vor Ausführung der Migration und überspringen die Spaltenergänzung bei einem frühen Teilabbruch.

### Geändert
- Benutzername wird als eigenes eindeutiges Loginfeld geführt; Anmeldung über Benutzername oder E-Mail bleibt möglich.
- Webinstaller legt die Benutzernamen-Spalte bei Neuinstallationen mit an und erstellt das Systemkonto danach.

### Grenzen
- Rollen gelten schulweit; Klassen-/Kurs-/Fachscopes sind noch nicht umgesetzt.
- Nur synthetische Testdaten. Kein Produktiv- oder DSGVO-Freigabestatus.
- PHP-/MySQL-Laufzeittests in der Arbeitsumgebung nicht ausgeführt; CI muss vor Release-Tag bestehen.

## [0.1.2] – 2026-10-13

### Geändert
- Installer kann ohne Zertifikat im ausdrücklich bestätigten HTTP-Testmodus verwendet werden.
- HTTP-Testinstallationen erhalten `ORDI_ENV=test` und nicht sichere Session-Cookies, damit Anmeldung über HTTP technisch funktioniert.
- Installer zeigt vor der Übertragung eine Warnung und verlangt eine ausdrückliche Bestätigung; keine echten Personen-/Schuldaten verwenden.
- Dokumentation für spätere Umstellung auf HTTPS und produktive Secure-Cookies ergänzt.

### Datenbankupdate
- Kein SQL-Update erforderlich.

### Grenze
- HTTP überträgt DB- und Login-Passwörter unverschlüsselt. Nur separate Testdatenbank/Testkonto nutzen und vor produktivem Betrieb HTTPS aktivieren.


## [0.1.1] – 2026-10-13

### Hinzugefügt
- Klassischer Webinstaller für PHP-/MySQL-Webhosting mit Verbindungstest, Tabellenpräfix (Standard `ordi_`) und selbst gewähltem Admin-Konto.
- Schutz des Installers nach erfolgreicher Einrichtung; sichere lokale Konfiguration außerhalb des öffentlichen `public`-Ordners.
- Präfixunterstützung für Installation, Migrationen, Anmeldung und Login-Begrenzung.
- Anmeldung mit Benutzername oder E-Mail-Adresse; HTTPS war für die Web-Ersteinrichtung verpflichtend.
- Dokumentierter Datei-Updateprozess mit SQL-Update-Skripten nur bei Schemaänderungen.

### Datenbankupdate
- Kein SQL-Update nötig: Das Schema erhält keine neuen Tabellen oder Spalten. Das Präfix betrifft die Namen der bei Neuinstallation angelegten Tabellen.

### Grenzen
- Noch kein fachliches Schulmodul und keine Freigabe für echte Schul- oder Schülerdaten.
- PHP/Webserver-Laufzeit und MySQL konnten in dieser Arbeitsumgebung nicht ausgeführt werden.


## [0.1.0] – 2026-10-13

### Hinzugefügt
- Modulares PHP-Grundgerüst mit validierter Umgebungs-/Datenbankkonfiguration und PDO-Verbindung.
- Versionierte technische MySQL-Migrationen für Benutzerkonten und pseudonymisierte Login-Begrenzung.
- Anmeldung/Abmeldung, Argon2id sofern verfügbar (sonst PHP-Standard), generische Fehlermeldungen, Sessionwechsel, CSRF-Schutz und Login-Begrenzung.
- Einmaliger CLI-Bootstrap für eine technische Systemadministration ohne Standardpasswort.
- Geschützte Platzhalterseite, lokale Compose-Datenbank, CI mit Syntax-, Integrations-, Web-Smoke- und Restore-Smoke-Tests.
- Ein-Befehl-Teststart `bash test.sh` mit kurzlebigen Containern, automatisch erzeugten Zugangsdaten und Browserstart.
- Der Schnelltest hält die Datenbank im internen Docker-Netzwerk; nur der manuelle Host-Entwicklungsstart veröffentlicht einen lokalen DB-Port.
- Entwicklungs-, Datenschutz-, Test- und Update-/Restore-Anleitungen.

### Grenzen
- v0.1.0 ist eine technische Entwicklungsfassung und nicht für reale schulische oder personenbezogene Fachdaten freigegeben.
- PHP- und Datenbanktests müssen in der eingerichteten CI bzw. einer passenden lokalen Laufzeit ausgeführt werden; der hier verwendete Arbeitscontainer enthält PHP/Docker nicht.
- Keine unabhängige Sicherheitsprüfung, DSFA oder konkrete Betriebs-/Datenschutzfreigabe.

## [0.0.9] – 2026-10-13

### Hinzugefügt
- Priorisiertes Backlog mit IDs, Zielversion, Akzeptanzkriterien, Abhängigkeiten, Verantwortlichkeiten und Aufwandsspannen.
- Begrenzter Freigabeumfang für v0.1.0.
- Getrennte Zuständigkeiten für Produkt, Umsetzung, Fachfreigabe, Datenschutz und Betrieb.
- Verfahren für Backlogänderungen, Entscheidungen, Commits und Release-Tags.

### Grenzen
- Aufwände sind grobe Planungsschätzungen, keine Liefertermine.
- v0.1.0 ist technische Basis, kein Schulfachmodul oder Pilot.
- Keine echten Schülerdaten, Hosterwahl oder Datenschutzfreigabe.


## [0.0.8] – 2026-10-13

### Hinzugefügt
- Risikobasiertes Bedrohungsmodell für Personen-/Schuldaten, Rollen, Anhänge, Pläne, Bewertung, Logs und Backups.
- Teststrategie mit Matrix für Berechtigungen, Planregeln, Klassenbuch, Noten, Dateien, Export, Löschung, Migration und Restore.
- Merge-, Pilot- und Produktionsgates mit Blockierung bei kritischen/hohen Sicherheitsbefunden.
- Vorgabe synthetischer Daten für Entwicklung und CI.
- Referenz auf OWASP ASVS 5.0.0 und NIST SSDF 1.1.

### Grenzen
- Sicherheits- und Abnahmetests sind geplant, aber noch nicht ausgeführt.
- Keine externe Sicherheitsprüfung, DSFA oder Produktivfreigabe.
- Keine echten Personen- oder Schuldaten.


## [0.0.7] – 2026-10-13

### Hinzugefügt
- Zielarchitektur als modularer Monolith mit getrennten Anwendungsmodulen und Hintergrundjobs.
- Bewertung PHP/SQL, Hostingmodelle, Updateweg, Support und Betriebsverantwortung.
- Vorläufiges Ziel: PHP 8.5 und MySQL 8.4 LTS, mit getesteter MariaDB-LTS-Alternative.
- Einzelschulinstanz als Start; Voraussetzungen für spätere Mehrschulfähigkeit.
- Datenschutz- und Sicherheitsgates vor Produktivdaten sowie Hosting-Prüfliste.
- Offizielle technische/providerbezogene Quellen mit Abrufstand.

### Grenzen
- Kein konkreter Hostinganbieter, Tarif oder Verantwortlicher wurde ausgewählt.
- Kein Hostingvertrag/AVV, VVT, DSFA oder Produktivbetrieb freigegeben.
- Keine echten personenbezogenen Daten.


## [0.0.6] – 2026-10-13

### Hinzugefügt
- Technologieunabhängiges fachliches Datenmodell mit ER-Übersichten und Datenwörterbuch.
- Zentrale Stammdaten und zeitliche Zuordnungen für Schule, Schuljahr, Personen, Klassen, Lerngruppen, Fach, Raum und Zeitraster.
- Trennung von Planversion, Unterrichtsereignis, Vertretung und Klassenbuchnachweis.
- Versionierungs- und Freigabeprinzipien für Pläne, Regeln, Bewertungsverfahren, Sitzpläne und Zeugnisse.
- Erste Migrations-, Aufbewahrungs-, Einschränkungs- und Löschleitlinien mit Anhängen/Backups.

### Grenzen
- Keine Datenbank-, Hosting- oder Stackentscheidung und keine ausführbaren Migrationen.
- Keine endgültigen Rechtsgrundlagen, Fristen, Notenformeln oder Radar-Dimensionen.
- Keine produktive Datenverarbeitung oder echten Personen-/Schuldaten.


## [0.0.5] – 2026-10-13

### Hinzugefügt
- Klickbarer Frontend-Prototyp für Dashboard, Stunden-/Raumplanung, Konflikte und Assistenzvorschläge.
- Interaktionsabläufe für Vertretung, persönlichen Stundenplan, Klassenbuchhinweise, Aufgaben, Räume, Mitteilungen und Termine.
- Feedbackprotokoll und offene Usability-Fragen für die fachliche Durchsicht.
- Lokale, externe Bibliotheken freie HTML-Datei mit synthetischen Daten und responsiven Ansichten.

### Grenzen
- Keine Anmeldung, Datenbank, Serververbindung oder dauerhafte Speicherung.
- Keine echten Personen-/Schuldaten; Kapazitäten und Regeln sind unbestätigte Demo-Werte.
- Keine echte Rollenprüfung, Veröffentlichung, Benachrichtigung, Dateiuploads oder DSFA-/DSGVO-Freigabe.

## [0.0.4] – 2026-10-13

### Hinzugefügt
- Vorläufiges Dateninventar der Konten-, Planungs-, Klassenbuch-, Aufgaben-, Kommunikations-, Bewertungs- und Betriebsdaten.
- Logische Datenflussbeschreibung einschließlich Dienstleistern, Anhängen, Sicherungen, Exporten und Löschpfad.
- Vorläufiges Screening der Datenschutz-Folgenabschätzung (DSFA) mit Freigabeschritten.
- Datenschutz-Quellenregister mit DSGVO, ThürSchulG, ThürSchulO, ministeriellen Schul-FAQ und Statushinweis auf einen Verordnungsentwurf.
- Neue Anforderungen an Verarbeitungstransparenz, Rechtsgrundlagen, Speicherfristen, Anbieter und Rechtebearbeitung.

### Noch nicht enthalten
- Bestimmung einer konkreten verantwortlichen Stelle oder Auftragsverarbeiterrolle.
- Vollständiges VVT oder abgeschlossene DSFA für einen realen Schulbetrieb.
- Festgelegte Hostinganbieter, Datenstandorte, Unterauftragsverarbeiter oder konkrete Rechtsgrundlagen.
- Verbindliche Löschfristen außerhalb der anwendbaren schulrechtlichen Spezialregeln.
- Produktive Nutzung echter personenbezogener Daten.

## [0.0.3] – 2026-10-13

### Hinzugefügt
- Thüringer Planungsregelprofil mit Trennung von Landesrecht, zeitgebundenen Vorgaben, Schulbeschlüssen, lokalen Bedingungen und Präferenzen.
- Quellenregister zu ThürSchulO, Lehrplanportal, Übergangshinweisen und schuljahresbezogenen Verwaltungsvorschriften.
- Anforderungen an Schulart-, Jahrgangs-, Kohorten- und Schuljahresprofile.
- Dokumentierte Lücken bei Raumkapazität, Fachraumpriorität und aktuell noch nicht abrufbarem VVOrgS-2026/27-Volltext.

### Noch nicht enthalten
- Vollständig maschinenlesbare Stundentafeln für alle Schularten, Kohorten und Übergangsfälle.
- Aktivierte automatische Thüringer Regelengine oder rechtliche Freigabe.
- Lokale Kapazitätswerte, Fachraumnachweise oder Beschlüsse einer konkreten Schule.
- Ausführbarer Anwendungscode.

## [0.0.2] – 2026-10-13

### Hinzugefügt
- Vorläufiges Rollenmodell mit begrenzten fachlichen Zuständigkeiten und Rechte-Matrix.
- Arbeitsabläufe für Planung, Vertretung, Klassenbuch, Aufgaben, Bewertung, Zeugnisse, Kommunikation, Termine und Konten.
- Datenschutzleitplanken für Sichtbarkeit, Freigaben, Anhänge, Protokollierung und Korrekturen.
- Offene Entscheidungen und Abnahmekriterien für diese Version.

### Noch nicht enthalten
- Verbindlich durch eine konkrete Schule oder Schulbehörde freigegebene Rollen.
- Rechtsverbindliche Festlegung von Löschfristen, Verantwortlichkeit oder Thüringer Genehmigungswegen.
- Ausführbarer Anwendungscode.

## [0.0.1] – 2026-10-13

### Hinzugefügt
- Projektdefinition und repository-fertige Grunddokumentation für OrdiSchool.
- Anforderungskatalog für Planung, Unterrichtsorganisation, Kommunikation und Bewertung.
- Datenschutz- und Sicherheitsleitplanken als Anforderungen für alle Versionen.
- Versionszuordnung, offene Entscheidungsfragen und v0.0.1-Commit-Nachricht.
- Ausführlicher Projektplan als Referenzdokument.

### Noch nicht enthalten
- Ausführbarer Anwendungscode.
- Verbindlich ausgewählte Laufzeit, Datenbank, Hostingumgebung oder Benutzerrollen.
- Freigegebene Thüringer Regelprofile, Notenalgorithmen, Radarspinnen-Dimensionen oder Zeugnisvorlagen.

## 0.4.0
- Räume, frei definierbare Raumtypen und optionale Kapazitäten ergänzt.
- Raumzuweisung, Raumkollision und Kapazitätsprüfung für die Stundenplanung ergänzt.
- Fachraumpräferenzen je Fach werden als nicht blockierende Hinweise behandelt.
- Schulformübergreifende Stundenplanung aus v0.3.0 bleibt erhalten.

## v0.5.0

- Stammdatenpflege aus der täglichen Planansicht entfernt und in `/admin/planning/settings` ausgelagert.
- Raum-, Fach-, Schulform-, Klassen- und Zeitrasterpflege sowie Regelprofile serverseitig auf aktive Schuladministration begrenzt.
- Versionierte Regelprofile nach Schuljahr, Schulform und Kohorte mit Quelle, Fundstelle, Prüfdatum, Prüfstatus und Prüferkonto ergänzt.
- Regelprüfbericht unterscheidet harte Zeit-/Raum-/Klassenkonflikte, weiche Hinweise und offene Quellen-/Regelstände.
- Prüfstände halten die verwendete Profilversion fest und werden bei Profiländerungen zur erneuten Prüfung markiert; Veröffentlichung ist ohne aktuellen Prüfstand gesperrt.
- Datenschutz-/Rechtsgrenze dokumentiert: gespeicherte Quellenprüfung ist keine vollständige Regelengine und kein Rechtskonformitätsnachweis.
- Wiederholbare Migration `006_planning_rule_profiles.sql` und phpMyAdmin-Update von v0.4.1 bereitgestellt.
