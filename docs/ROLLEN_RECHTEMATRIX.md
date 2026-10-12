# Rollen- und Rechtematrix

**Version:** 0.0.2 – fachlicher Entwurf  
**Status:** vorläufig; Freigabe durch die konkrete Schule und zuständige Stellen steht aus.

## Zweck und Grundsätze

Dieses Dokument beschreibt, wer welche Aufgaben in OrdiSchool grundsätzlich ausführen und welche Daten dabei sehen darf. Es ist ein fachliches Zielbild, keine bereits implementierte Zugriffskontrolle und keine Aussage, dass ein Zugriff rechtlich zulässig ist. Die Schule muss Rollen, Zuständigkeiten, Rechtsgrundlagen und Datenumfang für ihren konkreten Betrieb prüfen.

1. **Kleinster nötiger Zugriff:** Zugriff gilt nur für die zugewiesene Schule, Klasse, Lerngruppe, Fachgruppe, Veranstaltung oder eigene Person.
2. **Aufgabe vor Titel:** Eine Rolle gewährt nur Rechte, die für die zugewiesene Funktion nötig sind. Mehrere Rollen werden nicht automatisch zu schulweitem Vollzugriff.
3. **Freigabe ist eine eigene Handlung:** Erstellen, Bearbeiten, Prüfen und Veröffentlichen sind getrennte Rechte, sofern eine Veröffentlichung Auswirkungen auf andere hat.
4. **Gleiche Prüfung überall:** Listen, Suche, Direktlinks, Anhänge, Vorschauen, E-Mail-/Push-Benachrichtigungen, Exporte und APIs prüfen denselben Geltungsbereich.
5. **Änderungen bleiben nachvollziehbar:** Veröffentlichte oder fachlich relevante Datensätze werden nicht stillschweigend überschrieben. Korrektur, verantwortliche Person, Zeitpunkt und gegebenenfalls Begründung werden festgehalten.
6. **Technische Administration ist kein Fachzugriff:** Support- und Betriebsrechte geben standardmäßig keinen lesenden Zugriff auf pädagogische Inhalte. Ein begründeter Notfallzugriff wäre zeitlich beschränkt, freigegeben und protokolliert.
7. **Löschen bedeutet nicht automatisch physisches Löschen:** Archivierung, Berichtigung und endgültige Löschung folgen einer noch je Datenart zu bestätigenden Aufbewahrungs- und Löschregel.

## Rollenentwurf

| Rolle | Vorgesehener Geltungsbereich | Typische Aufgaben | Grenzen im Entwurf |
|---|---|---|---|
| Schulleitung | eigene Schule; gegebenenfalls delegierte Bereiche | schulweite Mitteilungen, delegierte Freigaben, Überblick über Plan- und Zeugnisprozesse | Kein pauschaler Zugriff auf private Nachrichten oder nicht erforderliche Detaildaten; lokale Delegation festlegen |
| Planungskoordination | zugewiesene Schule und Planungszeitraum | Stunden-/Raumplanentwürfe, Konfliktprüfung, Vertretungsvorschläge und Planänderungen vorbereiten | Veröffentlichung nur bei ausdrücklich delegiertem Recht; Vorschläge sind keine automatische Änderung |
| Lehrkraft | eigene Person, zugewiesene Fächer, Klassen, Kurse und Unterrichtsstunden | eigene Unterrichtsplanung, Klassenbucheinträge, Aufgaben, eigene Bewertungen und Kommunikation im erlaubten Bereich | Kein Zugriff auf fremde Klassen oder Bewertungen ohne gesonderte Zuständigkeit |
| Klassenleitung | ausdrücklich zugewiesene Klasse | Klassenübersicht, Hinweise zu Einträgen, Koordination und vorgesehene Zeugnisprüfung | Keine stille Änderung fremder Klassenbucheinträge oder Fachnoten |
| Fachkonferenz-/Fachverantwortung | zugewiesenes Fach bzw. Fachkonferenz | Entwurf und Prüfung von Bewertungsverfahren und Radar-Dimensionen | Konfigurationsverantwortung bedeutet nicht automatisch Zugriff auf personenbezogene Ergebnisse |
| Verwaltung/Sekretariat | zugewiesene Stammdaten und Verwaltungsprozesse | ausgewählte Stammdatenpflege, Termin- und Dokumentenabläufe nach lokaler Festlegung | Kein Zugriff auf Noten, vertrauliche Klassenbuchnotizen oder private Nachrichten als Standard |
| Lernende | eigenes Konto und explizit zugewiesene Klassen/Kurse | eigener Stundenplan, Aufgaben, veröffentlichte Materialien, freigegebene eigene Ergebnisse und Nachrichten | Kein Zugriff auf Anwesenheit, Notizen oder Ergebnisse anderer Lernender |
| Systemadministration | technische Mandanten-/Systemkonfiguration | Konten, Rollenvergabe nach Auftrag, technische Einstellungen, Sicherung und Wiederherstellung | Standardmäßig kein Inhaltseinblick; Sonderzugriff nur begründet, zeitlich begrenzt und protokolliert |
| Eltern/Sorgeberechtigte | **nicht Teil der aktiven Baseline** | mögliche spätere Einsicht nach schulischer Festlegung | Identität, Sorgeberechtigung, Umfang, Alters-/Einwilligungsfragen und Rechtsgrundlage vor Einführung klären |

## Rechtecodes

| Code | Bedeutung |
|---|---|
| R | Lesen innerhalb des angegebenen Geltungsbereichs |
| C | Erstellen bzw. Entwurf anlegen |
| U | Bearbeiten innerhalb der eigenen Zuständigkeit |
| A | Prüfen und freigeben/veröffentlichen |
| X | Exportieren oder herunterladen |
| D | Archivieren bzw. Löschung beantragen; endgültige Löschung nur nach freigegebener Regel |
| — | Kein Standardrecht |

Die Matrix zeigt **Standardrechte des Entwurfs**, nicht bereits bestätigte schulische Rechte. `R*` bedeutet eingeschränktes Lesen des für die Rolle erforderlichen Umfangs. Alle Rechte gelten nur im genannten Scope. `A†` bedeutet: nur bei ausdrücklich zugewiesener Freigabefunktion. `X†` bedeutet: nur bei dokumentiertem Zweck und freigegebener Exportmöglichkeit. Ein Recht in der Tabelle erlaubt keinen Zugriff entgegen einer anwendbaren gesetzlichen Einschränkung.

## Vorläufige Rechte-Matrix

| Datenbereich / Aktion | Schulleitung | Planung | Lehrkraft | Klassenleitung | Fachverantwortung | Verwaltung | Lernende | Systemadmin |
|---|---:|---:|---:|---:|---:|---:|---:|---:|
| Stunden-/Raumpläne: Entwurf und Konflikte | R* | R/C/U | R* | R* | — | R* | — | — |
| Stunden-/Raumpläne: Veröffentlichung | A† | A† | — | — | — | — | — | — |
| Vertretungen: Vorschlag und Prüfung | R* | R/C/U | R* | R* | — | R* | — | — |
| Vertretungen: Veröffentlichung | A† | A† | — | — | — | — | — | — |
| Klassenbuch: eigener Unterrichtseintrag | R* | — | R/C/U | R* | — | — | — | — |
| Klassenbuch: Hinweise einer zugewiesenen Klasse | R* | — | R* | R/U* | — | — | — | — |
| Aufgaben und Anhänge | R* | — | R/C/U | R* | — | — | R* | — |
| Fach-Bewertungskonfiguration | R* | — | R* | R* | R/C/U | — | — | — |
| Personenbezogene Bewertungen | R* | — | R/U* | R* | — | — | R* (eigene, freigegeben) | — |
| Zeugnisentwurf / Korrektur | R* | — | R/C/U* | R/C/U* | — | R* (Prozessdaten) | — | — |
| Zeugnisfreigabe und Ausgabe | A† | — | — | A† | — | R* | R* (eigenes, freigegebenes) | — |
| Gruppenmitteilungen / Nachrichten | R* | R* | R/C/U* | R/C/U* | R* | R* | R/C/U* | — |
| Eigener Stundenplan / Termine | R* | R* | R* | R* | R* | R* | R* | — |
| Rollen- und Kontenverwaltung | A† | — | — | — | — | C/U*† | — | C/U*† |
| Technische Protokolle / Betrieb | — | — | — | — | — | — | — | R/C/U* |
| Inhaltsdaten exportieren | X† | X† | X† | X† | — | X† | X† (eigene Daten) | — |
| Endgültige Löschung personenbezogener Inhalte | — | — | — | — | — | — | — | — |

**Lesart bei personenbezogenen Bewertungen:** Die Lehrkraft sieht Bewertungen nur für Lernende in zugewiesenen Unterrichtsgruppen; Klassenleitung nur, soweit die lokale Aufgabe und Regelung das erfordert; Lernende sehen ausschließlich eigene freigegebene Ergebnisse. `R*` setzt diese Einschränkungen voraus.

## Bereiche, die pro Schule konkret zu konfigurieren sind

- Zuordnung einer Person zu Schule, Klasse, Fach, Kurs, Raumplan und Vertretungsbereich.
- Wer Planentwürfe prüft und wer Stunden-, Raum- und Vertretungspläne veröffentlicht.
- Wer Klassenbuchhinweise einsehen darf; ob die Klassenleitung eine Korrektur anfordern oder selbst ausführen darf.
- Welche Rolle Bewertungsverfahren erstellt, fachlich beschließt, technisch aktiviert und Ergebnisse freigibt.
- Welche Schritte bei Zeugnissen schulisch vorgeschrieben oder delegiert sind.
- Welche Kommunikationsgruppen miteinander schreiben dürfen und welche Anhänge dort zulässig sind.
- Zulässigkeit und konkrete Ausgestaltung von Lernenden- und gegebenenfalls Elternkonten.
- Umfang, Frist und Freigabeprozess für Exporte, Supportzugriff, Archivierung und Löschung.

## Datenschutz- und Sicherheitsanforderungen an die spätere Umsetzung

- Berechtigungen serverseitig bei jedem Datenzugriff prüfen; reine UI-Ausblendung reicht nicht.
- Anhänge erhalten keine öffentlich erratbaren URLs; Berechtigung wird beim Download erneut geprüft.
- Benachrichtigungen enthalten nur nötige Metadaten und keine unnötigen Noten, Anwesenheitsdetails oder vertraulichen Notizen.
- Rollenwechsel und Entzug einer Zuständigkeit wirken zeitnah auf aktive Sitzungen, Suchergebnisse und Links.
- Freigaben, Exporte, Rollenänderungen, Korrekturen und Notfallzugriffe werden nachvollziehbar protokolliert; Protokolle enthalten selbst keine unnötigen Schülerdaten.
- Korrektur- und Löschabläufe berücksichtigen Aufbewahrungspflichten und Betroffenenrechte, die für den jeweiligen Verantwortlichen geprüft wurden.
- Es gibt keine automatisch aktivierte externe KI-Verarbeitung vertraulicher Schüler- oder Bewertungsdaten.

## Abnahmekriterien v0.0.2

1. Für jede Kernaktion sind ausführende Rolle, Scope, Sichtbarkeit und Freigabe im Workflow-Dokument benannt.
2. Kein Rollenentwurf gewährt Lernenden Zugriff auf Daten anderer Lernender.
3. Technische Administration erhält keinen standardmäßigen pädagogischen Inhaltszugriff.
4. Export, Anhang, Direktlink, Benachrichtigung und Suche sind als Berechtigungsprüfpunkte dokumentiert.
5. Ungeklärte schulische und rechtliche Entscheidungen sind sichtbar als offen/vorläufig markiert.
6. Rollen- und Workflowtexte sind untereinander konsistent und mit der Anforderungsliste verknüpft.

## Bezug

Die zugehörigen Prozessschritte stehen in [`ABLAEUFE.md`](ABLAEUFE.md). Datenschutz- und Sicherheitsanforderungen stehen in [`ANFORDERUNGEN.md`](ANFORDERUNGEN.md), insbesondere `PRIV-*` und `SEC-*`.
