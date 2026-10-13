# Entscheidungen und offene Punkte

**Version:** 0.0.4

## Bestätigte Arbeitsannahmen

| ID | Arbeitsannahme | Status |
|---|---|---|
| D-001 | Produktname ist Ordinatio Scholae, kurz OrdiSchool. | bestätigt |
| D-002 | Thüringen ist der erste fachliche Bezugspunkt; Schulformprofile müssen erweiterbar sein. | bestätigt |
| D-003 | Stunden-, Raum- und Vertretungsplanung gehören zu einem verbundenen Planungskern. | bestätigt |
| D-004 | Planungsassistenz zeigt begründete Vorschläge; Freigabe bleibt bei berechtigten Personen. | bestätigt |
| D-005 | Datenschutz und Sicherheit gelten für jede Entwicklungsstufe. | bestätigt |
| D-006 | Git-Versionen erhalten nachvollziehbare Commit-Nachrichten. | bestätigt |
| D-007 | PHP/MySQL ist nur eine zu prüfende Option, keine endgültige Architekturentscheidung. | vorläufig |
| D-008 | Das Rollenmodell in `ROLLEN_RECHTEMATRIX.md` ist ein fachlicher Entwurf für den Projektstand; es muss pro Schule und Betriebsmodell angepasst und freigegeben werden. | vorläufig |
| D-009 | Systemadministration erhält standardmäßig keinen fachlichen Lesezugriff auf Schüler-, Bewertungs- oder Kommunikationsinhalte. | Arbeitsgrundsatz; technische Umsetzung ab v0.1.0 zu prüfen |

## Vor Implementierungsbeginn zu entscheiden

| ID | Entscheidung | Benötigter Zeitpunkt |
|---|---|---|
| D-010 | Betrieb pro Schule, zentral beim Schulträger oder Mehrschul-/SaaS-Betrieb. | vor v0.0.6/v0.0.7 |
| D-011 | Wer ist je Betriebsform Verantwortlicher, wer Auftragsverarbeiter, wer bearbeitet Auskunft/Löschung? | vor v0.0.4 und vor Pilot |
| D-012 | Hostinganbieter, Serverstandort, Unterauftragnehmer, Backup, Verschlüsselung, Supportzugriff und Vertrag. | vor Architekturfreigabe |
| D-013 | Finale Benutzergruppen und Zugriffsrechte, einschließlich Lernenden- und möglicher Elternkonten. | vor Pilot; Baseline-Entwurf liegt in v0.0.2 vor |
| D-014 | Welche Thüringer Rechts- und Verwaltungsvorgaben gelten im konkreten Betrieb für welche Schulformen, und wer gibt die Regelprofile frei? | Quellen-Erstprüfung liegt in v0.0.3 vor; fachliche Aktivierung vor Regelengine |
| D-015 | Welche Daten sind für Anwesenheit, Klassenbuchnotizen, Aufgaben, Nachrichten und Sitzpläne notwendig? | v0.0.4 |
| D-016 | Notenarten, Gewichtungen, Rundung, Halbjahres-/Endnoten, fehlende Leistungen und Rückwirkung. | vor v0.12.0 |
| D-017 | Radarspinnen-Dimensionen, Skalen, Beobachtungslogik, Zielgruppe, Einsicht und Zeugnisbezug. | vor v0.13.0 |
| D-018 | Zeugnisvorlagen, Ausgabe, Freigabe und Aufbewahrung je Schulform. | vor v0.14.0 |
| D-019 | Benachrichtigungskanäle, Datenexporte, Schnittstellen und externe Dienste. | vor jeweiligem Modul |
| D-020 | Verbindliche Barrierefreiheits- und mobile Nutzungsziele. | vor v0.1.0 |
| D-021 | Welche lokalen Nachweise bestimmen zulässige Raumkapazitäten, Ausstattung, Zugänglichkeit und verbindliche Nutzungsbeschränkungen? | vor Raumplanung/Regelengine |
| D-022 | Welche Schulart, Bildungsgänge, Jahrgänge, Kurse und Übergangskohorten werden im ersten Thüringen-Profil tatsächlich unterstützt? | v0.0.5/v0.0.6 |
| D-023 | Wie erhält OrdiSchool den jeweils gültigen Volltext schuljahresbezogener Verwaltungsvorschriften und Lehrpläne und wer bestätigt die Aktualisierung? | vor v0.5.0 |
| D-024 | Wer ist im gewählten Betriebsmodell Verantwortlicher, welche Stelle führt/freigibt VVT und Datenschutzhinweise? | vor Architektur-/Pilotfreigabe |
| D-025 | Welche OrdiSchool-Datenfelder sind im konkreten staatlichen oder privaten Schulbetrieb für welchen Zweck rechtlich vorgesehen? | vor jeweiligem Modul/Pilot |
| D-026 | Welche Aufbewahrungs-/Einschränkungsregeln gelten zusätzlich zur einschlägigen Fassung von ThürSchulO/Schuldatenschutzrecht für Aufgaben, Nachrichten, Anhänge, Pläne und Logs? | vor Produktivbetrieb |
| D-027 | Ist im konkreten Betriebsmodell eine vollständige DSFA nach Art. 35 DSGVO erforderlich, und wer zeichnet diese Entscheidung? | vor Pilot |
| D-028 | Ist der 2025 dokumentierte Entwurf einer ThürSchulDSVO später in Kraft getreten oder geändert worden? | vor rechtlicher Profilfreigabe |

## Entscheidungsregel

Keine offene Datenschutz-, Hosting- oder Rechtsfrage wird stillschweigend als entschieden behandelt. Entscheidungen werden mit Datum, Begründung, betroffenen Anforderungen und verantwortlicher Freigabe ergänzt.

## Erkenntnisse aus der Quellenrecherche v0.0.3

- ThürSchulO und Rahmenstundentafeln bilden eine verbindliche Grundlage; § 44 weist die Festsetzung des Stundenplans der Schulleitung zu.
- Übergänge zwischen den ab 2024 geltenden und älteren Rahmenstundentafeln erfordern eine Kohorten-/Gültigkeitsprüfung.
- Landesweite Quellen begründen keine im Release belastbar festgestellte allgemeine Raumkapazität oder allgemeine Fachraumrangfolge. Diese Werte müssen lokal belegt und bestätigt werden.
- Die VVOrgS 2026/27 ist als aktuelle Schuljahresvorschrift identifiziert; der Volltext wurde im Recherchezugang nicht inhaltlich verifiziert und wird daher nicht als maschinenlesbare Regel verwendet.

## Datenschutz-Arbeitsstand v0.0.4

- § 57 ThürSchulG verknüpft schulische Datenverarbeitung mit den gesetzlich zugewiesenen Aufgaben und der Erforderlichkeit; nähere Bereiche sind spezialgesetzlich geregelt.
- Die Thüringer Schulordnung enthält in § 136 konkrete schulische Daten- und Aufbewahrungsregeln. Diese werden nicht pauschal auf alle OrdiSchool-Module übertragen.
- Die Rechtsgrundlage, Rollenverteilung, Auftragsverarbeitung, Informationspflichten und Fristen sind je Betrieb und Datensatz zu bestätigen.
- Ein DSFA-Screening ist begonnen; eine rechtliche Entscheidung oder vollständige DSFA liegt nicht vor.

## In v0.0.2 vorgeschlagene Arbeitsannahmen

- Rollen sind mit einer konkreten Schule, Klasse, Lerngruppe, Fachgruppe oder Funktion verknüpft; ein Rollentitel allein gewährt keinen schulweiten Zugriff.
- Es gibt kein standardmäßiges „Admin darf alles“. Technischer Betrieb und pädagogischer Zugriff sind getrennt.
- Freigaben und Korrekturen an veröffentlichten Planungen, Bewertungen und Zeugnissen bleiben nachvollziehbar.
- Elternkonten werden nicht als aktive Standardrolle angenommen. Ob und wie sie eingesetzt werden, ist vor einer Implementierung zu klären.
