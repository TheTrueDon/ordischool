# Vorläufiges Dateninventar für OrdiSchool

**Version:** 0.0.4  
**Prüfstand:** 13. Oktober 2026  
**Status:** Produktentwurf; keine vollständige oder freigegebene Verarbeitungsübersicht einer konkreten Schule.

## Zweck

Dieses Inventar sammelt vor dem Bau, welche personenbezogenen Daten die geplanten Module voraussichtlich berühren, wofür sie gebraucht werden, wer sie sehen soll und welche Entscheidungen vor einem realen Einsatz fehlen. Es unterstützt die verantwortliche Stelle bei ihrem Verzeichnis von Verarbeitungstätigkeiten (VVT) und ersetzt dieses nicht.

Die konkrete verantwortliche Stelle, Rechtsgrundlage, Informationspflicht, Empfängerkreise und Aufbewahrungsregeln hängen von Träger, Schulart, Betriebsmodell, konkreter Funktion und geltenden Vorschriften ab. In dieser Planung wird daher **keine** pauschale Rechtsgrundlage und keine allgemeine Löschfrist festgelegt.

## Gemeinsame Definitionen

- **Sichtbarkeit:** nur für eine bestätigte Zuständigkeit in Schule, Klasse, Kurs, Fach oder eigener Person; Rollenentwurf siehe `ROLLEN_RECHTEMATRIX.md`.
- **Speicherort:** reale Hosting-, Datenbank-, Dateispeicher- und Backup-Standorte sind noch offen (`D-010`, `D-012`). Die Angaben unten sind logische Speicherklassen, keine Architekturentscheidung.
- **Empfänger:** schulinterne Rollen sowie externe Auftragsverarbeiter und andere Stellen, soweit jeweils konkret bestimmt und zulässig.
- **Rechtsgrundlage:** für jede einzelne Verarbeitung festzustellen. Das Datenfeld im Produkt soll eine freigegebene Verknüpfung aus Verantwortlichem, Zweck und Rechtsgrundlage abbilden können; ein Softwaremodul entscheidet diese Rechtsfrage nicht selbst.
- **Aufbewahrung:** nur nach bestätigter Spezialregel bzw. dokumentiertem Zweck. Sicherungskopien und Exporte müssen in den Löschprozess einbezogen werden.

§ 57 ThürSchulG nennt die Erforderlichkeit für gesetzlich zugewiesene Schulaufgaben und enthält zusätzliche Regeln für Daten, die zur Nutzung digitaler Lehr-/Lernmittel und Lernumgebungen erforderlich sind, einschließlich dabei entstehender Nutzungsdaten. Daraus wird **nicht** pauschal abgeleitet, dass jede OrdiSchool-Funktion oder jede spätere Analyse durch diese spezielle Regel gedeckt ist. Anwendbarkeit und Zweck müssen einzeln geprüft werden.

## Dateninventar nach Funktionsbereich

| ID / Bereich | Betroffene Personen | Voraussichtliche Datenarten | Zweck / Funktion | vorgesehener Zugriff | Speicherklassen | Rechtsgrundlage / Aufbewahrung |
|---|---|---|---|---|---|---|
| DI-01 Konten und Schulzuordnung | Lernende, Sorgeberechtigte (falls später aktiviert), Lehrkräfte, Verwaltung | Name/Anzeigename, schulische Kennung, Rolle, Schule, Klasse/Kurs/Fach, Kontakt-/Login-Metadaten, Zuordnungszeiträume | Anmeldung, rollenbezogene Berechtigung und Zuständigkeitsprüfung | betroffene Person (eigene Daten); autorisierte Schulverwaltung; technischer Admin nur für Kontometadaten | Konten-/Berechtigungsdatenbank; technische Sicherheitsprotokolle | Verantwortlichkeit, erforderliche Felder und Konto-Aufbewahrung vor Pilot festlegen |
| DI-02 Stammdaten und Gruppen | Lernende und pädagogisches Personal | Namen/IDs, Schulart, Jahrgang, Klasse/Kurs, Fächer, Gruppen, Lehrkraftzuordnung, Gültigkeitszeitraum | Planung, Unterrichtszuordnung, Aufgaben- und Mitteilungszielgruppen | jeweils zuständige Lehrkräfte, Planungskoordination und Verwaltung; Lernende nur eigene Gruppen-/Ansichtsdaten | Stammdatenbank; Import-/Änderungsprotokoll | Datenumfang nach geltender Schulregel prüfen; keine privaten Zusatzfelder ohne Zweck |
| DI-03 Stunden-, Raum- und Vertretungsplan | Lehrkräfte, Lernende; ggf. weitere Beschäftigte | Klasse/Kurs, Fach, Lehrkraft, Zeit, Raum, Planversion, Abwesenheits-/Vertretungszuordnung und Änderungsmetadaten | Unterricht organisieren, Änderungen abstimmen, eigene Stundenpläne bereitstellen | Planungsrollen; betroffene Lehrkräfte; Lernende sehen freigegebene eigene Klassen-/Kursansicht | Planungsdatenbank; Änderungs-/Freigabeprotokoll | Abwesenheitsgründe möglichst gar nicht oder getrennt und eng begrenzt speichern; Aufbewahrung der veröffentlichten Planstände klären |
| DI-04 Sitzpläne | Lernende und Lehrkräfte | Lernendenkennung, Sitz-/Raumposition, Klasse/Kurs, Planversion | Unterrichtsorganisation und berechtigter Sitzplanzugriff | zuständige Lehrkräfte; ggf. begrenzte Druckansicht | Planungs-/Klassendatenbank; optional geschützter Export | Zweck, Sichtbarkeit, Druck und Export je Schule bestätigen; keine unnötigen Zusatzmerkmale |
| DI-05 Klassenbuch und Anwesenheit | Lernende, Lehrkräfte | Unterrichtsbezug, Anwesenheitsstatus, Verspätungsminuten, Fach/Klasse/Zeit, Thema, Vertretung, Hausaufgabe, Korrekturhistorie, ggf. Notiz | vorgeschriebene/organisatorisch bestätigte Dokumentation von Unterricht und Teilnahme; Abgleich mit Plan | erfassende Lehrkraft; eng begrenzte zuständige Klassenleitung/Schulleitung; weitere Empfänger nur gemäß Aufgaben- und Rollenprüfung | Klassenbuchdaten; Hinweis-/Änderungsprotokoll | Inhalt und Pflichtfelder mit § 136 ThürSchulO bzw. geltender Folgeregelung abgleichen; keine freie Notiz als unbegrenztes Sammelfeld; schuljahres-/datensatzbezogene Frist bestätigen |
| DI-06 Aufgaben und Dateianhänge | Lernende, Lehrkräfte | Aufgabe, Fach, Text, verantwortliche Person, Empfängergruppe, Termin, Abgabe-/Statusdaten, Dateien und Metadaten | Aufgaben stellen, bearbeiten und Materialien zugänglich machen | Ersteller und zugewiesene Mitverantwortliche; adressierte Lernende; Zugriff auf Datei nur mit Berechtigung auf Aufgabe | Fach-/Aufgabendatenbank; privater Objektspeicher; Viren-/Dateiprüfung | Erforderlichkeit von Abgabedaten und Fristen, Dateitypen/-größen, Anbieter und Löschung festlegen |
| DI-07 Nachrichten, Mitteilungen, Gruppenräume und Wikis | Lernende, Lehrkräfte, Verwaltung, ggf. Sorgeberechtigte | Absender-/Empfängerkennungen, Inhalt, Zeitstempel, Gruppenmitgliedschaft, Blog-/Wiki-Versionen, Anhänge und Zustellstatus | schulische Kommunikation und Zusammenarbeit | nur erlaubte Kommunikationsgruppen bzw. adressierte Konten; Gruppeninhalte gemäß Scope | Kommunikations-/Inhaltsdatenbank; privater Dateispeicher; minimale Zustellprotokolle | Inhaltsaufbewahrung, Moderation, Export, Archivierung und Löschung vor Umsetzung entscheiden; keine Veröffentlichung außerhalb der Zielgruppe |
| DI-08 Noten und notenfreie Bewertung | Lernende und Lehrkräfte | Leistung, Bewertungsart, Gewicht, Algorithmus-/Regelversion, Berechnung, Ergebnis, Radar-Dimensionen und Beobachtungen | Leistung erfassen, nachvollziehbar berechnen und nach Freigabe mitteilen | zuständige Lehrkraft; fachlich erforderliche Freigaberollen; Lernende nur eigene freigegebene Ergebnisse | geschützte Bewertungsdatenbank; Berechnungs-/Freigabehistorie | Schulrechtliche Erhebungsbefugnis, Zwecke, besondere Daten, Einsicht, Korrektur und Aufbewahrung vor jeder Bewertungsfunktion prüfen; Radar-Dimensionen bleiben gesondert offen |
| DI-09 Zeugnisse | Lernende, Lehrkräfte, Verwaltung | Name/Schulzuordnung, bewertungs- und zeugnisrelevante Angaben, Textbausteine, Entwurfs-/Freigabestatus, Ausgabedatum | Zeugnisentwürfe und bestätigte Ausgabe | jeweils beteiligte Rollen; Empfänger nur eigenes bzw. rechtmäßig zugestelltes Dokument | Zeugnisdatenbank; geschützte PDF-/Dokumentenablage; Ausgabeprotokoll | amtliche Vorlage, Pflichtfelder, Freigabe und Fristen nach Schulform/Verantwortlichem bestätigen; Exporte kontrollieren |
| DI-10 Termine und Dashboard | alle aktiv zugewiesenen Nutzer | Termin, Zielgruppe, Unterrichts-/Raumbezug, Mitteilungen, Aufgabenstatus und Planhinweise | persönliche und gruppenbezogene Übersicht; Informationen aus verbundenen Modulen | Nutzende sehen nur eigene Termine/Planänderungen und freigegebene Gruppeninformationen | Kalender-/Indexdatenbank; ggf. Benachrichtigungsdienst ohne sensible Inhaltsdetails | Sichtbarkeit einzelner Quelldatensätze darf durch Dashboard/Suche nicht erweitert werden; Protokoll- und Push-Minimierung |
| DI-11 Technik, Sicherheit und Support | alle Nutzer | Anmeldeereignis, Rolle/Scope zum Ereigniszeitpunkt, Zeit, Ergebnis, nötige Geräte-/Netzwerkmetadaten, Fehlermeldungen, Supportfall | Sicherheit, Missbrauchserkennung, Betriebsfehler und nachvollziehbarer Support | Sicherheits-/Betriebspersonal mit begrenztem Zugriff; fachliche Inhalte nicht im Standard-Supportlog | getrenntes Sicherheitsprotokoll; Monitoring; Supporttickets | Logzweck, Datenfelder und Frist risikobasiert festlegen; keine Passwörter, Sitzungstokens, Noten oder Klassenbuchnotizen in Logs |
| DI-12 Sicherungen und Datenexporte | alle in Quelldatensätzen erfassten Personen | Kopie der für Wiederherstellung oder freigegebenen Export benötigten Datensätze | Systemwiederherstellung und rechtmäßig angeforderter/organisierter Datenexport | Backupadministration; exportberechtigte Rolle; Empfänger des konkreten Exports | verschlüsselte, getrennte Backups; kontrollierter Exportbereich | Backuprotation, sichere Löschung, Restore-Zugriff, Exportprotokoll und Aufbewahrung müssen mit den Quelldatenregeln zusammenpassen |

## Daten mit erhöhtem Schutzbedarf

- **Gesundheitsangaben, Behinderungen und sonderpädagogische Informationen** können besondere Kategorien personenbezogener Daten berühren. Sie sind kein Standardfeld des Klassenbuchs, der Aufgaben oder des Planungsassistenten. Eine Verarbeitung setzt gesonderte Prüfung, konkrete Rechtsgrundlage, minimierte Erfassung und besonders begrenzten Zugriff voraus.
- **Anwesenheits-/Verspätungsdaten, Noten und Radar-Beobachtungen** sind nicht automatisch Art.-9-Daten; sie können dennoch erhebliche Auswirkungen auf Kinder und Jugendliche haben und benötigen eng begrenzte Sichtbarkeit und nachvollziehbare Korrekturen.
- **Freitext und Anhänge** können unbeabsichtigt Gesundheits-, Familien-, Verhaltens- oder andere sensible Informationen enthalten. Für jedes Freitextfeld ist zu prüfen, ob es überhaupt erforderlich ist, wie es angeleitet wird, wer es lesen kann und wie es gelöscht wird.
- **Sorgeberechtigungsnachweise** sollen nicht als Dokumentkopie in OrdiSchool abgelegt werden. Ein minimaler Vermerk über eine schulisch bestätigte Zuordnung ist gesondert zu prüfen.
- **Elternkonten, KI-Funktionen, externe Kommunikation, Analyse-/Trackingdienste und Drittlandtransfers** sind nicht Teil der aktiven Datenschutz-Baseline; sie benötigen jeweils eigene Zweck-, Rechtsgrundlagen- und Empfängerprüfung.

## Offene Felder, die vor Einsatz je Vorgang ausgefüllt werden müssen

1. Verantwortlicher, ggf. gemeinsam Verantwortliche, Datenschutzbeauftragte und interne Kontaktstelle.
2. Zweck in konkreter schulischer Aufgabe und dokumentierte Rechtsgrundlage je Verarbeitung.
3. Pflicht-/Optionalfelder und Quelle der Daten.
4. Empfänger, Rollen-/Gruppenumfang und externe Dienstleister.
5. Verarbeitungsspeicher, Standorte, Backup-/Supportzugriff und ggf. Drittlandübermittlung.
6. Informationspflicht und Zeitpunkt der Information.
7. Berichtigungs-, Export-, Einschränkungs-, Widerspruchs- und Löschablauf.
8. Aufbewahrung pro Datensatzklasse einschließlich Jahreswechsel, Schulabgang, Export und Sicherung.
9. Bewertung des Risikos und Entscheidung, ob vollständige Datenschutz-Folgenabschätzung erforderlich ist.
10. Fachliche und datenschutzrechtliche Freigabe mit Datum und gültigem Produkt-/Regelprofil.

## Quellenhinweise

Die § 136 ThürSchulO/§ 57 ThürSchulG und DSGVO-Quellen sowie ihre Grenzen sind im [`Datenschutz-Quellenregister`](DATENSCHUTZ_QUELLENREGISTER.md) verzeichnet. § 136 enthält für staatliche allgemeinbildende Schulen konkrete Vorgaben zu Datenerhebung, Klassen-/Kursbüchern, Information, Einschränkung und Aufbewahrung. Die konsultierte Fassung nennt beispielsweise eine Einschränkung ein Jahr nach dem Ausscheiden sowie besondere Aufbewahrungsfristen für bestimmte Unterlagen. Diese Regeln sind vor Umsetzung in der aktuellen konsolidierten Fassung zu prüfen und dürfen nicht pauschal auf jede neue App-Funktion übertragen werden.
