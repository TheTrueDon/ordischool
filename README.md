# OrdiSchool

**Version:** 0.0.2 – Rollen und Arbeitsabläufe  
**Projektname:** Ordinatio Scholae (kurz: OrdiSchool)  
**Stand:** 13. Oktober 2026

OrdiSchool soll eine integrierte Schulsoftware für Stunden-, Raum- und Vertretungsplanung, Sitzpläne, Klassenbuch, Aufgaben, Termine, Kommunikation und Bewertung werden. Thüringen ist der erste fachliche Bezugspunkt. Datenschutz und Sicherheit sind Anforderungen für jede Entwicklungsstufe.

## Stand dieser Version

v0.0.2 ergänzt die Projektdefinition um ein vorläufiges Rollenmodell, eine Rechte-Matrix und fachliche Arbeitsabläufe. Damit werden Akteure, Sichtbarkeit, Freigaben und Protokollierung beschrieben. Es ist weiterhin keine ausführbare Software enthalten; die Rollen müssen vor einem konkreten Schuleinsatz bestätigt werden.

## Projektdateien

- [`docs/ANFORDERUNGEN.md`](docs/ANFORDERUNGEN.md) – funktionale und nicht-funktionale Anforderungen mit IDs
- [`docs/ENTSCHEIDUNGEN.md`](docs/ENTSCHEIDUNGEN.md) – bestätigte Arbeitsannahmen und offene Entscheidungen
- [`docs/ROLLEN_RECHTEMATRIX.md`](docs/ROLLEN_RECHTEMATRIX.md) – Rollen, Geltungsbereiche und Rechte
- [`docs/ABLAEUFE.md`](docs/ABLAEUFE.md) – fachliche Abläufe mit Sichtbarkeit, Prüfung und Freigabe
- [`docs/VERSIONSPLAN.md`](docs/VERSIONSPLAN.md) – Entwicklungsstufen und Zuordnung der Anforderungen
- [`docs/OrdiSchool_Projektplanung_v0.0.1_bis_v1.0.0.docx`](docs/OrdiSchool_Projektplanung_v0.0.1_bis_v1.0.0.docx) – ausführlicher Projektplan und Datenschutzfahrplan
- [`CHANGELOG.md`](CHANGELOG.md) – Änderungen je Version
- [`VERSION`](VERSION) – aktuelle Projektversion
- [`docs/COMMIT_MESSAGE_v0.0.2.txt`](docs/COMMIT_MESSAGE_v0.0.2.txt) – vorgeschlagene Git-Commit-Nachricht

## Arbeitsregeln

1. Anforderungen erhalten stabile Kennungen und werden einer Version zugeordnet.
2. Änderungen an Plänen, Bewertungsverfahren und Regelprofilen müssen nachvollziehbar versioniert werden.
3. Planungsvorschläge werden vor Veröffentlichung durch eine berechtigte Person geprüft und freigegeben.
4. Entwicklung und Tests verwenden synthetische Daten. Echte Schülerdaten werden erst nach Freigabe des konkreten Betriebs berücksichtigt.
5. Eine Rechtsgrundlage, Löschfrist oder schulrechtliche Regel wird nicht aus der Softwarefunktion abgeleitet. Zuständige Stellen prüfen sie für den jeweiligen Einsatz.
6. Jede Version erhält eine passende Commit-Nachricht, Änderungsnotizen und klare Abnahmekriterien.
7. Berechtigungen gelten für Listen, Suche, Direktlinks, Anhänge, Benachrichtigungen und Exporte gleichermaßen.

## Datenschutz-Hinweis

Dieses Repository dokumentiert ein Entwicklungsziel und ist keine Rechtsberatung oder Konformitätsbescheinigung. Die DSGVO-Prüfung hängt vom konkreten Verantwortlichen, Einsatz, Datenfluss, Hosting und den Verträgen ab.
