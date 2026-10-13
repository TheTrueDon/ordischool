# Änderungsprotokoll

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
