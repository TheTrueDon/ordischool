# Entscheidungen und offene Punkte

**Version:** 0.0.2

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
| D-014 | Welche Thüringer Rechts- und Verwaltungsvorgaben gelten für welche Schulformen, und wer prüft die Regelprofile? | v0.0.3 |
| D-015 | Welche Daten sind für Anwesenheit, Klassenbuchnotizen, Aufgaben, Nachrichten und Sitzpläne notwendig? | v0.0.4 |
| D-016 | Notenarten, Gewichtungen, Rundung, Halbjahres-/Endnoten, fehlende Leistungen und Rückwirkung. | vor v0.12.0 |
| D-017 | Radarspinnen-Dimensionen, Skalen, Beobachtungslogik, Zielgruppe, Einsicht und Zeugnisbezug. | vor v0.13.0 |
| D-018 | Zeugnisvorlagen, Ausgabe, Freigabe und Aufbewahrung je Schulform. | vor v0.14.0 |
| D-019 | Benachrichtigungskanäle, Datenexporte, Schnittstellen und externe Dienste. | vor jeweiligem Modul |
| D-020 | Verbindliche Barrierefreiheits- und mobile Nutzungsziele. | vor v0.1.0 |

## Entscheidungsregel

Keine offene Datenschutz-, Hosting- oder Rechtsfrage wird stillschweigend als entschieden behandelt. Entscheidungen werden mit Datum, Begründung, betroffenen Anforderungen und verantwortlicher Freigabe ergänzt.

## In v0.0.2 vorgeschlagene Arbeitsannahmen

- Rollen sind mit einer konkreten Schule, Klasse, Lerngruppe, Fachgruppe oder Funktion verknüpft; ein Rollentitel allein gewährt keinen schulweiten Zugriff.
- Es gibt kein standardmäßiges „Admin darf alles“. Technischer Betrieb und pädagogischer Zugriff sind getrennt.
- Freigaben und Korrekturen an veröffentlichten Planungen, Bewertungen und Zeugnissen bleiben nachvollziehbar.
- Elternkonten werden nicht als aktive Standardrolle angenommen. Ob und wie sie eingesetzt werden, ist vor einer Implementierung zu klären.
