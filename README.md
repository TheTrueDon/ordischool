# OrdiSchool

**Version:** 0.0.7 – Architektur- und Hostingentscheidung  
**Projektname:** Ordinatio Scholae (kurz: OrdiSchool)  
**Stand:** 13. Oktober 2026

OrdiSchool soll eine integrierte Schulsoftware für Stunden-, Raum- und Vertretungsplanung, Sitzpläne, Klassenbuch, Aufgaben, Termine, Kommunikation und Bewertung werden. Thüringen ist der erste fachliche Bezugspunkt. Datenschutz und Sicherheit sind Anforderungen für jede Entwicklungsstufe.

## Stand dieser Version

v0.0.7 ergänzt die Zielarchitektur, Technologie- und Hostingbewertung sowie eine Prüfliste für konkrete Anbieter. Die Hostingempfehlung ist vorläufig; Produktivanbieter, Verantwortlichkeit und Datenschutzfreigaben sind offen.

## Projektdateien

- [ARCHITEKTUR_HOSTINGENTSCHEIDUNG.md](docs/ARCHITEKTUR_HOSTINGENTSCHEIDUNG.md) – Architektur, Technologievergleich, Hosting, Datenschutzgates
- [HOSTING_PRUEFLISTE.md](docs/HOSTING_PRUEFLISTE.md) – Checkliste für konkrete Anbieter und Tarife
- [FACHLICHES_DATENMODELL.md](docs/FACHLICHES_DATENMODELL.md) – Entitäten, Beziehungen, Datenwörterbuch, Versionierung und Lifecycle
- [ANFORDERUNGEN.md](docs/ANFORDERUNGEN.md) – funktionale und nicht-funktionale Anforderungen mit IDs
- [`docs/ENTSCHEIDUNGEN.md`](docs/ENTSCHEIDUNGEN.md) – bestätigte Arbeitsannahmen und offene Entscheidungen
- [`docs/ROLLEN_RECHTEMATRIX.md`](docs/ROLLEN_RECHTEMATRIX.md) – Rollen, Geltungsbereiche und Rechte
- [`docs/ABLAEUFE.md`](docs/ABLAEUFE.md) – fachliche Abläufe mit Sichtbarkeit, Prüfung und Freigabe
- [`docs/REGELPROFIL_THUERINGEN.md`](docs/REGELPROFIL_THUERINGEN.md) – Planungsbedingungen, Regelklassen und Umsetzungshinweise für Thüringen
- [`docs/QUELLENREGISTER.md`](docs/QUELLENREGISTER.md) – geprüfte Quellen, Gültigkeit, Fundstellen und offene Abrufe
- [`docs/DATENINVENTAR.md`](docs/DATENINVENTAR.md) – Datenarten, Zwecke, Rollen, Speicherklassen und offene Prüfungen
- [`docs/DATENFLUSS.md`](docs/DATENFLUSS.md) – logische Flüsse, Empfänger, Anbieter und Lebenszyklus
- [`docs/DSFA_SCREENING.md`](docs/DSFA_SCREENING.md) – vorläufige Risikofaktoren und Prüfschritte zur Datenschutz-Folgenabschätzung
- [`docs/DATENSCHUTZ_QUELLENREGISTER.md`](docs/DATENSCHUTZ_QUELLENREGISTER.md) – DSGVO- und Thüringer Schuldatenschutzquellen
- [`prototype/index.html`](prototype/index.html) – lokal klickbarer Frontend-Prototyp
- [`prototype/README.md`](prototype/README.md) – Start, Demo-Abläufe und Grenzen des Prototyps
- [`docs/FEEDBACK_OFFENE_FRAGEN.md`](docs/FEEDBACK_OFFENE_FRAGEN.md) – Feedbackprotokoll, Durchsicht-Aufgaben und Usability-Fragen
- [`docs/VERSIONSPLAN.md`](docs/VERSIONSPLAN.md) – Entwicklungsstufen und Zuordnung der Anforderungen
- [`docs/OrdiSchool_Projektplanung_v0.0.1_bis_v1.0.0.docx`](docs/OrdiSchool_Projektplanung_v0.0.1_bis_v1.0.0.docx) – ausführlicher Projektplan und Datenschutzfahrplan
- [`CHANGELOG.md`](CHANGELOG.md) – Änderungen je Version
- [`VERSION`](VERSION) – aktuelle Projektversion
- [`docs/COMMIT_MESSAGE_v0.0.7.txt`](docs/COMMIT_MESSAGE_v0.0.7.txt) – vorgeschlagene Git-Commit-Nachricht

## Arbeitsregeln

1. Anforderungen erhalten stabile Kennungen und werden einer Version zugeordnet.
2. Änderungen an Plänen, Bewertungsverfahren und Regelprofilen müssen nachvollziehbar versioniert werden.
3. Planungsvorschläge werden vor Veröffentlichung durch eine berechtigte Person geprüft und freigegeben.
4. Entwicklung und Tests verwenden synthetische Daten. Echte Schülerdaten werden erst nach Freigabe des konkreten Betriebs berücksichtigt.
5. Eine Rechtsgrundlage, Löschfrist oder schulrechtliche Regel wird nicht aus der Softwarefunktion abgeleitet. Zuständige Stellen prüfen sie für den jeweiligen Einsatz.
6. Jede Version erhält eine passende Commit-Nachricht, Änderungsnotizen und klare Abnahmekriterien.
7. Berechtigungen gelten für Listen, Suche, Direktlinks, Anhänge, Benachrichtigungen und Exporte gleichermaßen.
8. Regelprofile speichern Quelle, Gültigkeit, Schulform, Bildungsgang, Jahrgang und Kohorte; nicht belegte Regeln werden nicht als Landesvorgabe ausgegeben.
9. Jedes personenbezogene Datenfeld benötigt vor einer produktiven Nutzung einen dokumentierten Zweck, Berechtigungsumfang und eine bestätigte Rechts-/Aufbewahrungsentscheidung.

## Datenschutz-Hinweis

Dieses Repository dokumentiert ein Entwicklungsziel und ist keine Rechtsberatung oder Konformitätsbescheinigung. Die DSGVO-Prüfung hängt vom konkreten Verantwortlichen, Einsatz, Datenfluss, Hosting und den Verträgen ab.
