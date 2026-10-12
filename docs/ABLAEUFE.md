# Fachliche Arbeitsabläufe

**Version:** 0.0.2 – Entwurf  
**Status:** vorläufig; Freigabewege und Verantwortlichkeiten sind je Schule zu bestätigen.

## Einheitliches Ablaufmodell

Jeder Vorgang mit Auswirkungen auf andere Personen folgt, soweit fachlich passend, diesen Schritten:

1. Eine zuständige Person erstellt einen Entwurf im eigenen Scope.
2. Das System prüft Pflichtfelder, Konflikte und Berechtigungen und zeigt verständliche Hinweise.
3. Eine zweite zuständige Person prüft den Entwurf, falls der Vorgang eine Veröffentlichung oder verbindliche Festlegung darstellt.
4. Eine berechtigte Person gibt frei oder lehnt mit Begründung ab.
5. Das System veröffentlicht nur die freigegebene Fassung an die vorgesehene Zielgruppe.
6. Spätere Korrekturen erzeugen eine nachvollziehbare Änderungshistorie. Alte Fassungen sind nur für berechtigte Rollen sichtbar.

Für jeden Ablauf werden mindestens Akteur, Scope, sichtbare Personen, Freigabe, Benachrichtigung, Protokoll und Fehler-/Ausnahmefall berücksichtigt. Diese Beschreibung ist kein Ersatz für eine schulische Dienstanweisung.

## 1. Stunden-, Raum- und Vertretungsplanung

| Schritt | Zuständig / sichtbar | Systemverhalten und Kontrolle |
|---|---|---|
| Planungsgrundlage pflegen | Planungskoordination; Stammdaten nur für zugewiesene Rollen | Kapazitäten, Fachraumpriorität, Klassen, Lehrkräfte, Fächer und Zeitraster werden als versionierte Grundlage geführt. Gültigkeitszeitraum und Datenquelle werden kenntlich gemacht. |
| Plan erstellen oder ändern | Planungskoordination erstellt; betroffene Lehrkräfte sehen ggf. eigene Entwürfe, Lernende sehen nichts vor Veröffentlichung | System prüft Raumkapazität, Doppelbelegung, Lehrkraft-/Klassenkonflikte und Fachraumprioritäten. Regelverletzung, Warnung und unverbindlicher Optimierungsvorschlag werden unterschieden. |
| Konfliktlösung vorschlagen | Planungsassistenz schlägt der Planungskoordination Alternativen vor | Vorschläge enthalten verständliche Gründe und betroffene Konflikte. Keine automatische Planänderung; Nutzende können vergleichen, übernehmen oder verwerfen. |
| Prüfen und freigeben | Schulleitung oder ausdrücklich delegierte Freigaberolle | Prüft Änderungen und Auswirkungen. Freigabe, Ablehnung und Rückfrage werden mit Akteur, Zeit und Versionsstand erfasst. |
| Veröffentlichen | freigabeberechtigte Person | Nur die freigegebene Version wird für betroffene Personen sichtbar. Persönliche Stundenpläne und Kalender erhalten nur relevante Änderungen. |
| Abwesenheit erfassen / Vertretung vorbereiten | dafür zuständige Rolle; Details nur erforderlichen Personen sichtbar | Planungskoordination erhält die für die Vertretungsplanung erforderlichen Informationen. System erzeugt geeignete Besetzungsvorschläge mit Konflikt- und Qualifikationshinweisen, soweit solche Regeln verifiziert sind. |
| Vertretung freigeben | Schulleitung/delegierte Rolle laut lokalem Prozess | Vorschlag wird geprüft, freigegeben und erst danach im Vertretungsplan veröffentlicht. Ablehnung kann eine erneute Suche auslösen. |
| Änderung widerrufen/korrigieren | zuständige Freigaberolle | Betroffene erhalten Korrekturhinweis; ältere Fassung bleibt revisionsfähig und nur zuständigen Personen zugänglich. |

**Datenschutz:** Gründe für eine Abwesenheit sollen nicht weiter sichtbar sein als für die konkrete Aufgabe erforderlich. Quellen für schulrechtliche und Thüringer Planungsregeln bleiben in v0.0.3 zu prüfen. Siehe `PL-*`, `SEC-*` und `PRIV-*`.

## 2. Klassenbucheintrag und Anwesenheit

1. Das System zeigt einer Lehrkraft die zugewiesene Unterrichtsstunde aus freigegebenem Stunden- oder Vertretungsplan.
2. Lehrkraft erfasst Anwesenheit je Lernendem mit Status Anwesend, Abwesend, Entschuldigt oder Minuten verspätet sowie optionaler erforderlicher Notiz. Sie ergänzt Stundenthema, Datum, Zeit, Stunde, Fach, Klasse, Vertretungsstatus, Doppelstundenkennzeichen und Hausaufgaben.
3. Der Eintrag ist zunächst für die Lehrkraft und jene schulischen Rollen sichtbar, die für den jeweiligen Klassenbuchprozess freigegeben wurden. Lernende sehen keine personenbezogenen Anwesenheitslisten anderer Personen.
4. Das System vergleicht Datum, Zeit, Klasse, Fach und Lehrkraft mit dem gültigen Plan. Fehlender Eintrag und Abweichung erzeugen einen Hinweis, keine automatische Korrektur.
5. Lehrkraft kann Hinweis bestätigen, Eintrag ergänzen/korrigieren oder eine Abweichung begründen. Korrekturen an abgeschlossenen Einträgen behalten eine Änderungshistorie.
6. Klassenleitung erhält eine begrenzte Übersicht offener Hinweise für die zugewiesene Klasse, soweit das nach lokaler Regelung vorgesehen ist. Sie kann Klärung anfordern; eine Änderung durch Dritte ist ein eigener, berechtigter Vorgang.

**Ausnahmen:** Ersatzstunde, Doppelstunde, Planänderung, Unterrichtsausfall und technische Störung dürfen nicht pauschal als Fehlverhalten oder Fehler der Lehrkraft bewertet werden. Hinweise müssen den erkannten Grund und die Referenzversion des Plans zeigen.

## 3. Aufgaben und Materialien

1. Lehrkraft erstellt eine Aufgabe im Kontext eines Klassenbucheintrags oder unabhängig davon.
2. Lehrkraft legt Fach, verantwortliche Person, Zielklasse/-gruppe bzw. ausgewählte Lernende, Text, Termin und Anhänge fest.
3. System zeigt vor Veröffentlichung die Empfängergruppe und Vorschau der Sichtbarkeit; Dateien werden auf Typ/Größe geprüft und privat gespeichert.
4. Ersteller veröffentlicht oder speichert als Entwurf. Beteiligte Co-Verantwortliche können entsprechend ihrer Zuweisung bearbeiten.
5. Lernende sehen nur Aufgaben und Anhänge, die ihrer zugewiesenen Zielgruppe entsprechen. Eine Weitergabe eines Links an Nichtberechtigte darf den Zugriff nicht ermöglichen.
6. Änderungen an Termin, Empfänger oder Inhalt werden markiert; bei wesentlicher Änderung werden Betroffene informiert.
7. Archivierung oder Löschung erfolgt gemäß einer später bestätigten Aufbewahrungsregel; sie wird nicht durch bloßes Entfernen aus der Aufgabenliste ersetzt.

## 4. Bewertung und Bewertungsverfahren

1. Fachverantwortliche erstellen einen Entwurf für Notenalgorithmus, Notenarten, Gewichtungen, Rundung, fehlende Leistungen und Gültigkeitszeitraum oder für Dimensionen der Radarbewertung.
2. Fachkonferenz bzw. lokal benannte Stelle prüft und beschließt die fachliche Regel. Das System protokolliert Version, Gültigkeitszeitraum und Freigabe.
3. Lehrkraft erfasst Leistungen nur in zugewiesenen Lerngruppen. Berechnungen zeigen die verwendeten Eingaben, Gewichte, Rundung und Algorithmusversion.
4. Lehrkraft prüft Ergebnis und markiert es je nach lokalem Prozess als freizugebend. Das System darf keine nicht beschlossene Berechnungsregel als verbindlich darstellen.
5. Freigabe an Lernende erfolgt durch die lokal zuständige Rolle. Lernende sehen nur eigene freigegebene Ergebnisse.
6. Eine nachträgliche Änderung von Eingabe oder Verfahren zeigt, welche Ergebnisse betroffen wären. Bereits abgeschlossene oder freigegebene Ergebnisse werden nicht still rückwirkend verändert.
7. Korrektur erfolgt mit Grund, Akteur und Zeitpunkt. Einsicht in Änderungsdetails bleibt auf berechtigte Rollen begrenzt.

**Offen:** konkrete Thüringer Regeln, Notenrundung, Beschlusswege, Radar-Dimensionen, Interpretationen und Sichtbarkeit. Siehe `NOTE-*`, `RADAR-*` sowie die Entscheidungen in v0.0.1.

## 5. Zeugniserstellung

1. Verwaltung oder zuständige Koordination öffnet einen Zeugnislauf für Zeitraum und Schul-/Klassenbereich.
2. Das System erzeugt Entwürfe nur aus freigegebenen und für den Prozess vorgesehenen Daten. Fehlende oder widersprüchliche Angaben werden als Prüfliste gezeigt.
3. Lehrkräfte ergänzen den ihnen zugewiesenen fachlichen Anteil; Klassenleitung prüft den Klassenentwurf. Diese Entwürfe sind nur Beteiligten sichtbar.
4. Berechtigte Freigaberolle prüft und gibt Zeugnisse nach dem lokal bestätigten Verfahren frei. Rollen und Reihenfolge sind noch nicht verbindlich.
5. Nach Freigabe erhält die berechtigte Person bzw. der berechtigte Empfänger nur das eigene Zeugnis. Ausgabe, Korrektur und Neuausgabe werden protokolliert.
6. Vorlagen, Aufbewahrung, Berichtigungsweg und Ausgabeform müssen für Schulform und Einsatz geklärt sein. Kein Zeugnis wird durch einen ungeprüften Standard automatisch rechtlich verbindlich.

## 6. Kommunikation und Gruppenräume

1. Eine berechtigte Person erstellt Nachricht oder Gruppenmitteilung und wählt Empfängergruppe und Anhänge.
2. System prüft, ob Absender die Zielgruppe anschreiben darf und zeigt die konkrete Empfängerliste bzw. Gruppe vor Versand.
3. Nachricht wird an die erlaubten Empfänger zugestellt; Empfänger sehen keine unberechtigten Gruppeninhalte. Anhänge erfordern dieselbe Berechtigung wie die Nachricht.
4. Gruppenleitung kann Materialien/Blogbeiträge im eigenen Gruppenraum veröffentlichen. Wiki-Bearbeitung und Versionssicht richten sich nach Gruppenrechten.
5. Schulweite Mitteilungen dürfen nur durch Schulleitung oder ausdrücklich Beauftragte veröffentlicht werden.
6. Export, Weiterleitung, Aufbewahrung und Löschung von Nachrichten sind vor Implementierung und Betrieb gesondert zu spezifizieren.

**Datenschutz:** Vorschau/Push soll nur erforderliche Metadaten enthalten. Keine vertraulichen Schülerdetails in Betreffzeilen oder Sperrbildschirmtexten als Standard.

## 7. Kalender und persönlicher Stundenplan

1. Planungskoordination veröffentlicht Stunden- und Vertretungsplan. Gruppen oder Leitung erstellen Termine in ihrem Scope.
2. Vor Freigabe prüft das System Termin-/Stundenplankonflikte und zeigt Betroffene.
3. Nach Freigabe sehen Nutzende ihren eigenen Stundenplan, relevante Vertretungen, Raumänderungen und Termine. Gruppenweite Termine erscheinen nur für berechtigte Gruppen.
4. Änderung oder Absage aktualisiert die betroffenen Ansichten und erzeugt eine angemessene Mitteilung.
5. Persönliche private Termine bleiben privat, sofern keine ausdrückliche Freigabe erteilt wird.

## 8. Konten, Rollen und Zuständigkeitswechsel

1. Eine zuständige Verwaltungs- oder Schulrolle beantragt Konto und konkrete Zuordnungen (Schule, Klasse, Fach, Funktion, Zeitraum).
2. Eine dafür benannte Freigaberolle bestätigt die Zuordnung. Konten werden nicht allein aufgrund einer E-Mail-Adresse automatisch einer Schülerakte zugeordnet.
3. Systemadministration richtet bestätigte Konten technisch ein, ohne dadurch standardmäßig fachlichen Inhaltszugriff zu erhalten.
4. Bei Rollen-/Klassenwechsel oder Austritt wird die Zuordnung zeitnah angepasst; aktive Zugriffe, Links und Suche müssen den geänderten Scope berücksichtigen.
5. Konto-Sperre, Export, Aufbewahrung und Löschung folgen der für den konkreten Betrieb bestätigten Regel. Technische Logs sollen keine unnötigen pädagogischen Inhalte enthalten.

## Durchgängige Freigabe- und Protokollregeln

- Jede Freigabe benennt, was freigegeben wird, für welchen Zeitraum und für welche Zielgruppe.
- Vorschläge aus automatisierter Assistenz sind als Vorschläge erkennbar; sie verändern oder veröffentlichen keine Daten allein.
- Jede Ablehnung oder wesentliche Korrektur kann mit einer angemessenen Begründung versehen werden.
- Protokolliert werden mindestens Akteur, Aktion, Objekt/Version, Zeit und Ergebnis. Inhaltliche Protokolldaten werden minimiert und selbst zugriffsbeschränkt.
- Fehlende Rolle, abgelaufene Zuweisung oder unklare Zuständigkeit führt zu verweigertem Zugriff bzw. Wartestatus, nicht zu einem pauschalen Adminzugriff.
- Datenexporte, Wiederherstellung und Notfallzugriffe benötigen dokumentierten Zweck und einen begrenzten Berechtigungspfad.

## Abnahmekriterien v0.0.2

- Für Planänderung, Vertretung, Klassenbuch, Aufgabe, Bewertung, Zeugnis, Kommunikation, Kalender und Kontowechsel sind Akteur, Sichtbarkeit, Freigabe und wesentliche Ausnahmen beschrieben.
- Kein Workflow veröffentlicht Vorschläge automatisch ohne freigegebene Zuständigkeit.
- Klassenbuchhinweise lösen keine automatische Änderung aus.
- Berechtigungen gelten auch für Anhänge, Suche, Direktlinks und Benachrichtigungen.
- Schul- und Rechtsfragen sind als offen markiert und an v0.0.3/v0.0.4 oder vor den jeweiligen Modulen gebunden.
