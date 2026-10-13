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
| D-054 | Eine Schule kann mehrere Schulformen und Bildungsgänge unter einem organisatorischen Dach anbieten; das Schulobjekt erhält daher keine fachlich maßgebliche Einzelauswahl der Schulform. | bestätigt durch Product Owner; Datenmodell/Umsetzung ab v0.3.0 |
| D-055 | Eine Lehrkraft wird als Person einmal geführt und kann am selben Tag in mehreren Schulformen eingesetzt werden. Stundenplan-, Verfügbarkeits- und Konfliktprüfungen betrachten alle Einsätze gemeinsam. | bestätigt durch Product Owner; Datenmodell/Umsetzung ab v0.3.0 |
| D-056 | Fachraumpräferenzen (z. B. Chemie im Chemieraum) sind weiche Ziele. Eine Abweichung erzeugt einen Hinweis, blockiert Planung/Freigabe aber nicht; Raumkollisionen und bekannte Kapazitätsüberschreitungen bleiben harte Konflikte. | bestätigt durch Product Owner; ab v0.4.0 |

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
| D-056 | Müssen Einsätze an getrennten Gebäuden/Standorten Wege- und Übergangszeiten berücksichtigen, und welche Zeitwerte gelten? | vor Raum-/Vertretungsassistenz; bei gemeinsamer Campuslage ggf. nicht erforderlich |

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


## Datenmodell-Arbeitsstand v0.0.6

| ID | Arbeitsannahme / offener Punkt | Status |
|---|---|---|
| D-029 | Stammdaten werden zentral geführt; Module referenzieren gemeinsame interne Kennungen. | Arbeitsgrundsatz v0.0.6 |
| D-030 | Person und Benutzerkonto sind getrennt; Authentifizierung gehört in spätere IAM-/Architekturentscheidung. | Arbeitsgrundsatz v0.0.6 |
| D-031 | Planung, Unterrichtsereignis, Vertretung und Klassenbuchnachweis sind getrennt und verknüpft. | Arbeitsgrundsatz v0.0.6 |
| D-032 | Veröffentlichte Pläne, Regelprofile und Bewertungsverfahren sind versioniert; Änderung erzeugt neuen Stand. | Arbeitsgrundsatz v0.0.6 |
| D-033 | ID-Format, Datenbanktechnologie, Zeitmodell und physische Trennung folgen in v0.0.7. | offen |
| D-034 | Aufbewahrung, Einschränkung und Löschung sind pro Zweck/Datensatzklasse und Verantwortlichem freizugeben; dieses Modell setzt keine Fristen. | offen |
| D-035 | Pflichtfelder sind fachliche Mindestangaben; personenbezogene Felder werden erst nach Erforderlichkeits- und Datenschutzprüfung aktiviert. | Arbeitsgrundsatz v0.0.6 |


## Architektur-Arbeitsstand v0.0.7

| ID | Arbeitsentscheidung / offener Punkt | Status |
|---|---|---|
| D-036 | v0.1.0 startet als modularer Monolith; Ausgliederung in Dienste erfordert messbaren Betriebs-/Sicherheitsgrund. | empfohlen/festgelegt für Start |
| D-037 | PHP 8.5 ist Zielruntime, abhängig von Framework-/Erweiterungskompatibilität und Hostingnachweis. | vorläufige Technologieentscheidung |
| D-038 | MySQL 8.4 LTS ist Zielstandard; MariaDB 11.4 LTS ist Alternative nach eigener Teststrecke. | vorläufige Technologieentscheidung |
| D-039 | Start als isolierte Einzelschulinstanz; Schul-Scope wird im Datenmodell durchgängig geprüft. | vorläufig festgelegt |
| D-040 | Konkreter Hoster, Tarif, AVV, Supportstandorte und Verantwortlicher bleiben offen und müssen vor Produktivdaten freigegeben werden. | offen |
| D-041 | Ein vorhandener STRATO-Webspace ist für synthetische Entwicklung/Demo prüfbar; Produktiveignung ist wegen konkreter Datenbank-/Worker-/Backupbedingungen nicht festgestellt. | bedingt / Paketprüfung offen |
| D-042 | RPO, RTO, Verfügbarkeit, Bereitschaft und technische Supportrolle bestimmt der spätere Betreiber. | offen vor Pilot |
| D-043 | Mehrschul-/SaaS-Betrieb ist nicht Teil des ersten Release und benötigt eigene Rechts-/Sicherheits-/Architekturfreigabe. | nicht freigegeben |


## Sicherheits- und Testarbeitsstand v0.0.8

| ID | Arbeitsentscheidung / offener Punkt | Status |
|---|---|---|
| D-044 | OWASP ASVS 5.0.0 ist die versionierte Prüfbasis; relevante Level-2-Kontrollen bilden das anfängliche Zielprofil. | vorläufig; Kontrollauswahl durch Security Review |
| D-045 | Automatisierte/manuelle Tests verwenden synthetische Daten; Produktivdatenkopien sind ausgeschlossen. | Arbeitsgrundsatz |
| D-046 | Kritische/hohe Sicherheitsbefunde blockieren Release/Pilot; mittlere Befunde benötigen dokumentierte Risikoakzeptanz und Frist. | Freigabegrundsatz |
| D-047 | Unabhängiger Penetrationstest und Restore-/Incident-Probe sind Gates vor Pilot/Produktivbetrieb. | geplant; Umfang/Prüfer/Betreiber offen |
| D-048 | Radar, Eltern, externe E-Mail/Push, KI und Mehrschulrisiken werden bei konkreter Aktivierung neu bewertet. | weiterhin offen/nicht freigegeben |


## Backlog- und Releasearbeitsstand v0.0.9

| ID | Arbeitsentscheidung / offener Punkt | Status |
|---|---|---|
| D-049 | Aufwand für v0.1.0 wird als grobe Personentagsspanne mit etwa ±40% Unsicherheit geplant und nach den ersten zwei Einträgen neu bewertet. | Planungsannahme |
| D-050 | v0.1.0 enthält keine Schüler-/Lehrkraft-Stammdaten, Pläne, Noten, Klassenbuchdaten oder produktiven Import. | Releaseumfang festgelegt |
| D-051 | Initialer Admin ist technische Installationsrolle ohne pauschale pädagogische Inhaltsrechte. | Releaseumfang festgelegt |
| D-052 | Fachliche, Datenschutz-/Rechts- und Betriebsfreigaben bleiben von Entwicklungsfreigaben getrennt. | Freigabegrundsatz |
| D-053 | Echte schulische Daten erst nach zuständiger Freigabe und innerhalb des genehmigten Pilotumfangs verarbeiten. | Freigabegate |


