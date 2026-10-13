# Fachliches Datenmodell – OrdiSchool

**Version:** 0.0.6  
**Stand:** 13. Oktober 2026  
**Status:** Fachlicher, technologie- und datenbankneutraler Entwurf; nicht produktiv freigegeben.

## Zweck und Grenzen

Dieses Dokument beschreibt die fachlichen Objekte und Beziehungen als Grundlage für v0.0.7. Es legt weder Datenbank, Programmiersprache, Hosting noch Rechtsgrundlagen fest. Es ist keine vollständige Rechtsprüfung oder DSGVO-Konformitätsbescheinigung.

Stammdaten zu Schule, Schuljahr, Personen, Klassen, Lerngruppen, Fach, Lehrkraft, Raum und Zeitraster werden fachlich zentral geführt. Module referenzieren diese Objekte, statt Organisationsdaten zu duplizieren. Personenbezogene Felder sind Entwurfsfelder. Vor produktiver Nutzung sind je Feld Zweck, Erforderlichkeit, Verantwortlichkeit, Rechtsgrundlage, Sichtbarkeit, Empfänger und Aufbewahrung/Löschung im konkreten Betrieb zu bestätigen. Siehe DATENINVENTAR.md, DATENFLUSS.md und DSFA_SCREENING.md.

## Modellierungsregeln

1. Schulbezogene Daten gehören zu einer Schule (school_id), direkt oder über eine eindeutige Elternbeziehung. Mehrschulbetrieb bleibt offen. Abfragen und Rechteprüfungen berücksichtigen den Scope.
2. Person und Benutzerkonto sind getrennte Objekte. Authentifizierung, Passwörter und Sitzungen gehören in ein späteres IAM-Modell.
3. Mitgliedschaften, Zuweisungen und Rollen erhalten Beginn, optionales Ende und Status. Historische Pläne werden nicht durch heutige Zuordnungen umgedeutet.
4. Wiederkehrende Planposition, konkrete Unterrichtsstunde, Vertretungsänderung und Klassenbuchnachweis sind unterschiedliche, verknüpfte Objekte.
5. Veröffentlichte Pläne, Regelprofile, Bewertungsverfahren, Sitzpläne und Zeugnisvorlagen erhalten unveränderliche Versionen.
6. Änderungen an Regeln oder Algorithmen ändern veröffentlichte Pläne und freigegebene Ergebnisse nicht still rückwirkend. Neuberechnung und Neufreigabe sind eigene Vorgänge.
7. Eine Datenbeziehung gewährt keinen Zugriff. Rechte gelten bei Ansicht, Suche, Direktlink, Anhang, Benachrichtigung, Export und API.
8. Es gibt keine pauschalen Aufbewahrungsfristen. Lifecycle, Einschränkung und Löschung werden je Zweck und Datenklasse festgelegt.
9. Keine allgemeinen Freitextfelder für Gesundheitsdaten, Diagnosen oder familiäre Umstände. Notizfelder brauchen Zweck und eingeschränkte Sichtbarkeit.
10. Herkunft importierter oder regelbegründender Angaben wird bei Bedarf mit Quelle und Prüfzeitpunkt geführt. Externe Quell-IDs sind keine internen Schlüssel.

## Fachliche Domänen

| Domäne | Kernobjekte | Zweck |
|---|---|---|
| Organisation und Identität | Schule, Schuljahr, Zeitraum, Person, Konto-Verknüpfung, Mitgliedschaft, Rollenbindung | Organisations- und Berechtigungsscope |
| Unterricht und Planung | Klasse, Lerngruppe/Kurs, Fach, Raum, Zeitraster, Planversion, Unterrichtszuweisung, Termin, Vertretung | Geplanten Betrieb und Änderungen darstellen |
| Durchführung und Sitzordnung | Unterrichtsereignis, Klassenbucheintrag, Anwesenheit, Sitzplanversion, Sitzzuweisung | Tatsächliche Stunde und Nachweis dokumentieren |
| Lernen und Bewertung | Aufgabe, Leistungserhebung, Leistungswert, Bewertungsverfahren, Radarprofil/-beobachtung, Zeugnis | Aufgaben und fachlich verantwortete Bewertung |
| Zusammenarbeit | Gruppe, Mitgliedschaft, Nachricht, Mitteilung, Wiki-/Bloginhalt, Material, Anhang | Zielgruppenbezogene Kommunikation |
| Steuerung und Datenschutz | Regelversion, Freigabe, Änderungshistorie, Auditmetadatum, Lifecycle-Regel | Nachvollziehbarkeit und Datenlebensdauer |

## Beziehungen

Diagramme zeigen fachliche Beziehungen, keine Tabellenstruktur. M:N-Zuordnungen werden durch eigene Objekte mit Gültigkeit und Status aufgelöst.

### Organisation und Planung

~~~mermaid
erDiagram
    SCHULE ||--o{ SCHULJAHR : fuehrt
    SCHULE ||--o{ PERSON : verwaltet
    SCHULJAHR ||--o{ KLASSE : ordnet_ein
    KLASSE ||--o{ LERNGRUPPE : unterteilt
    PERSON ||--o{ MITGLIEDSCHAFT : erhaelt
    LERNGRUPPE ||--o{ MITGLIEDSCHAFT : umfasst
    SCHULJAHR ||--o{ PLANVERSION : plant
    PLANVERSION ||--o{ UNTERRICHTSZUWEISUNG : enthaelt
    LERNGRUPPE ||--o{ UNTERRICHTSZUWEISUNG : wird_unterrichtet
    FACH ||--o{ UNTERRICHTSZUWEISUNG : betrifft
    RAUM ||--o{ UNTERRICHTSZUWEISUNG : beherbergt
~~~

Eine Klasse kann Kurse und Teilungsgruppen enthalten. Mitgliedschaften ordnen Lernende zeitlich zu. Unterrichtszuweisungen verbinden Gruppe, Fach, Lehrkraft, Zeit und Raum. Einzeltermine sind datierte Ereignisse und überschreiben nicht den Wochenplan.

### Plan, Unterricht und Klassenbuch

~~~mermaid
erDiagram
    PLANVERSION ||--o{ UNTERRICHTSZUWEISUNG : versioniert
    UNTERRICHTSZUWEISUNG ||--o{ UNTERRICHTSEREIGNIS : erzeugt
    UNTERRICHTSEREIGNIS ||--o{ VERTRETUNGSEREIGNIS : kann_geaendert_werden
    UNTERRICHTSEREIGNIS ||--o| KLASSENBUCHEINTRAG : wird_dokumentiert
    KLASSENBUCHEINTRAG ||--o{ ANWESENHEIT : dokumentiert
    PERSON ||--o{ ANWESENHEIT : betrifft
    SITZPLANVERSION ||--o{ SITZPLANZUWEISUNG : versioniert
    UNTERRICHTSEREIGNIS ||--o{ SITZPLANZUWEISUNG : verwendet
~~~

Die konkrete Stunde hält den wirksamen Planstand fest. Vertretung kann Lehrkraft, Raum oder Status ändern; sie löscht nicht die Ausgangsplanung. Doppelstunden, Teilungen und mehrere Lehrkräfte werden über eigene Zuordnungen abgebildet.

### Aufgaben, Bewertung und Gruppen

~~~mermaid
erDiagram
    LERNGRUPPE ||--o{ AUFGABE : adressiert
    AUFGABE ||--o{ DATEIANHANG : enthaelt
    PERSON ||--o{ BEWERTUNGSERGEBNIS : erhaelt
    LEISTUNGSERHEBUNG ||--o{ BEWERTUNGSERGEBNIS : liefert
    BEWERTUNGSVERFAHREN ||--o{ VERFAHRENSVERSION : hat
    VERFAHRENSVERSION ||--o{ BEWERTUNGSERGEBNIS : berechnet
    SCHULE ||--o{ GRUPPE : organisiert
    GRUPPE ||--o{ GRUPPENMITGLIEDSCHAFT : hat
    NACHRICHT ||--o{ DATEIANHANG : enthaelt
~~~

Zielgruppen und Verantwortliche sind zeitlich gültig. Gruppenempfänger werden beim Zugriff anhand aktueller Berechtigungen geprüft. Anhänge haben keine öffentliche URL.

## Datenwörterbuch

Legende: **M** = fachlich erforderlich, **B** = bedingt, **O** = noch offen. Interne ID, Schulscope und technische Metadaten kommen zusätzlich hinzu.

### Gemeinsame Felder

| Feld | Regel |
|---|---|
| Interne ID | Stabil und nicht sprechend; Format folgt v0.0.7. Keine Namen, Geburtsdaten oder Schulkennungen kodieren. |
| school_id | Schulischer Geltungsbereich, direkt oder über Elternbeziehung. Rechteprüfung erfolgt zusätzlich. |
| created_at / updated_at | Technische Zeitpunkte; Zeit- und Zeitzonenkonvention folgt v0.0.7. |
| valid_from / valid_until | Fachlicher Zeitraum einer Version oder Zuordnung. Intervallgrenzen folgen späterer Technikentscheidung. |
| status | Fachlicher Lebenszyklus, z.B. Entwurf, geprüft, veröffentlicht, beendet oder eingeschränkt; kein Berechtigungsmechanismus. |
| Herkunft | Quelle/Import, Zeitpunkt und bei Bedarf prüfende/freigebende Stelle. |
| Lifecycle-Klasse | Verweist auf bestätigten Zweck, Aufbewahrungs-/Einschränkungsentscheidung, Auslöser und Löschverantwortung. Bis zur Freigabe offen. |

### Organisation, Personen und Zuständigkeit

| Objekt | Zweck/Beziehungen | Erforderlich (M) | Bedingt/offen (B/O) | Datenschutz-/Lifecycle-Hinweis |
|---|---|---|---|---|
| Schule | Organisatorischer Anker | Bezeichnung, Status | Trägerkennung, Mandantentrennung (O) | Mehrschulbetrieb und Verantwortlichkeit offen |
| Schuljahr | Rahmen für Klassen, Regeln, Pläne | Bezeichnung, Beginn, Ende, Status | Ferien-/Halbjahresperioden (B) | Keine Gleichsetzung mit gesetzlicher Speicherfrist |
| Zeitraum | Halbjahr, Trimester, Abschnitt | Name, Beginn, Ende, Typ | Schulformspezifische Typen (O) | Fachperiode ist keine Rechtsfrist |
| Schulform-/Bildungsgangprofil | Regelkontext | Profilkennung, Bezeichnung, Gültigkeit | Kohorte/Jahrgang/Übergang (B) | Verbindliche Regeln nur mit Quelle, Version, Prüfstatus, Freigabe |
| Person | Zentrale schulische Person | Personenart, Status | Name, schulische Kennung, Kontakte (B/O) | Identitätsdaten minimieren; Schüler-/Beschäftigtendaten getrennt berechtigen |
| Konto-Verknüpfung | Person und IAM-Konto verbinden | Personbezug, Konto-Referenz, Gültigkeit, Status | Login-/IdP-Kennung (O) | Keine Passwörter oder Tokens im Fachdatenmodell |
| Rollenbindung | Rolle mit Scope und Zeitraum | Rolle, Scope, Gültigkeit, Status | Begründung/Vertretung (B) | Rollentitel allein gibt keinen globalen Zugriff |
| Klasse | Klassenverband im Schuljahr | Schuljahr, Bezeichnung, Jahrgang, Gültigkeit | Schulformprofil, Klassenleitung (B) | Wechsel historisch über Zeiträume darstellen |
| Lerngruppe/Kurs | Unterrichtsgruppe inkl. Teilung | Schuljahr, Bezeichnung, Typ, Gültigkeit | Herkunftsklasse, Kapazität (B/O) | Mitgliedschaft separat führen |
| Mitgliedschaft | Person gehört Klasse/Kurs/Gruppe an | Person, Zielgruppe, Beginn, Status | Ende, Importquelle (B) | Historische Zugehörigkeit nicht überschreiben |
| Fach | Zentrale Fachreferenz | Fachkennung, Bezeichnung, Status | Landes-/Schulkennung, Fachbereich (O) | Fachname nicht als Schlüssel verwenden |
| Lehrkraftzuweisung | Person unterrichtet Planposition | Person, Unterrichtszuweisung, Funktion, Zeitraum | Team-/Teilungsrolle (B) | Abwesenheitsgrund nicht in Planposition speichern |

### Räume, Regeln und Planung

| Objekt | Zweck/Beziehungen | Erforderlich (M) | Bedingt/offen (B/O) | Datenschutz-/Lifecycle-Hinweis |
|---|---|---|---|---|
| Raum | Zentraler Raumstamm | Kennung, Bezeichnung, Status | Kapazität, Ausstattung, Zugänglichkeit, Beschränkung (B, Nachweis nötig) | Lokale Fakten brauchen Quelle, Prüfstatus und Gültigkeit |
| Zeitraster | Unterrichtszeit | Bezeichnung, Beginn, Ende, Gültigkeit | Pausen/Abweichungen (B) | Zeitmodell folgt v0.0.7 |
| Regelprofil | Regeln nach Kontext | Typ, Kontext, Gültigkeit, Status | Schuljahr, Schulform, Kohorte, Quelle (B) | Ungeprüfte Quellen nicht bindend aktivieren |
| Regelversion | Unveränderlicher Regelstand | Kennung, Profil, Quelle, Prüfstatus, Gültigkeit | Fundstelle, Prüfer/Freigabe zur Aktivierung | Neue Fassung statt Überschreiben; betroffene Pläne aufzeigen |
| Planversion | Zusammenhängender Planstand | Schuljahr, Version, Status, Gültigkeit | Grund, Ersteller, Prüfer/Freigebende (B) | Entwurf, Prüfung und Veröffentlichung trennen |
| Unterrichtszuweisung | Wiederkehrende Stunde | Lerngruppe, Fach, Zeitraster, Gültigkeit | Raum, Doppelstunde, Teilung (B) | Mehrfachzuordnungen über eigene Beziehungen |
| Raumbelegung | Raum durch Plan/Ereignis belegen | Raum, Zeit, Referenz, Gültigkeit | Kapazitäts-/Ausstattungsprüfung (B) | Nur gültige lokale Nachweise verwenden |
| Unterrichtsereignis | Konkrete Stunde am Datum | Ausgangszuweisung, Datum/Zeit, Gruppe, Status | Tatsächlicher Raum/Lehrkraft, Abweichung (B) | Bezug für Klassenbuch, Aufgabe und Vertretung |
| Vertretungsereignis | Änderung einer konkreten Stunde | betroffenes Ereignis, Änderungsart, Status | Ersatzlehrkraft/Raum, erforderlicher Grund (B) | Abwesenheitsdetails vermeiden; Freigabe nachvollziehen |
| Termin | Persönlicher, Gruppen- oder Schultermin | Titel, Beginn/Ende, Sichtbarkeit, Ersteller | Ort, Planbezug, Zielgruppe, Notiz (B) | Termin- und Benachrichtigungssichtbarkeit separat prüfen |

### Durchführung, Sitzplan, Aufgaben, Kommunikation

| Objekt | Zweck/Beziehungen | Erforderlich (M) | Bedingt/offen (B/O) | Datenschutz-/Lifecycle-Hinweis |
|---|---|---|---|---|
| Sitzplanversion | Zeitgebundene Anordnung | Zielgruppe/Raum, Version, Status, Gültigkeit | Ersteller/Freigabe (B) | Ausdruck/Export nach Zweck und Scope |
| Sitzplanzuweisung | Person einer Position zuordnen | Position, Person oder Platzstatus | Pädagogische Notiz (O, zunächst aus) | Personenbezogen; kein dauerhaftes Profil bilden |
| Klassenbucheintrag | Nachweis konkreter Stunde | Unterrichtsereignis, durchführende Lehrkraft, Thema, Status | Zeit/Stunde oder begründete Abweichung; Hausaufgabe, Doppelstunde | Pflichtfelder/Fristen mit aktueller Spezialregel prüfen |
| Anwesenheit | Status einer Person je Stunde | Klassenbucheintrag, Person, Status | Verspätungsminuten bei Verspätung; Notiz nach Zweck (B) | Eng berechtigen; keine Diagnose-/Entschuldigungsdetails im allgemeinen Notizfeld |
| Klassenbuchkorrektur | Berichtigung nachvollziehbar halten | Ziel, Korrekturstand, Zeitpunkt, Bearbeiter, Typ/Grund | Vorher-Nachher-Werte im nötigen Umfang | Nie still überschreiben; Aufbewahrung offen |
| Aufgabe | Fachliche Aufgabe, optional mit Unterricht verknüpft | Titel/Text, Fach, Ersteller, Status | Unterrichtsbezug, Zielgruppe, Verantwortliche, Termin (B) | Text und Anlagen können Schülerdaten enthalten |
| Gruppenraum | Arbeitsbereich definierter Mitglieder | Bezeichnung, Gruppe, Status | Moderation/Gültigkeit (B) | Ausgeschiedene Mitglieder verlieren Zugriff |
| Wiki-/Blogbeitrag | Versionierter Gruppeninhalt | Raum, Titel/Inhalt, Autor, Version, Sichtbarkeit | Freigabe, Gültigkeit, Kommentare (O) | Verlauf nur im erforderlichen Umfang |
| Nachricht/Mitteilung | Einzel-/Gruppenkommunikation | Absender, Inhalt, Zeitpunkt, Status | Empfänger, Anhänge, Zustellstatus (B/O) | Private Nachricht, Gruppenmitteilung und Leitungshinweis trennen |
| Dateianhang | Private Datei mit Fachbezug | Elternobjekt, Speicherreferenz, Typ, Größe, Ersteller | Prüfergebnis, Lifecycle-Klasse, Hash (B/O) | Privat speichern; Elternrechte bei jedem Zugriff; Backups einbeziehen |

### Bewertung und Zeugnis

| Objekt | Zweck/Beziehungen | Erforderlich (M) | Bedingt/offen (B/O) | Datenschutz-/Lifecycle-Hinweis |
|---|---|---|---|---|
| Leistungserhebung | Test, mündliche Leistung oder Notenart | Fach, Gruppe, Datum, Typ | Gewichtung/Termin aus freigegebenem Verfahren (B) | Leistungsdaten eng berechtigen |
| Leistungswert | Ergebnis einer Person | Person, Erhebung, Status/Wertformat | Fehlend/abwesend/ausstehend getrennt; Rückmeldung (B) | Keine Analyse über freigegebenen Zweck hinaus |
| Bewertungsverfahren | Fachlich freigegebene Berechnungslogik | Fach/Kontext, Name, Status | Schulform/Jahrgang/Zeitraum, Gewichte, Rundung, Fehlwerte (O) | Fachfreigabe nötig; nicht automatisch rechtskonform |
| Verfahrensversion | Unveränderliche Formel-/Parameterfassung | Verfahren, Version, Gültigkeit, Freigabestatus | Prüfer, Testfälle, Änderungsgrund (zur Aktivierung) | Frühere Ergebnisse behalten Versionsbezug |
| Berechnungsergebnis | Halbjahres-/Endnotenstand | Person, Fach/Kontext, Zeitraum, Version, Status | Eingaben, Gewichte, Rundung, Rechenschritte | Neuberechnung ist neuer Stand und braucht Freigabe |
| Radar-Dimensionsprofil | Fachbezogene Dimensionen/Skalen | Fach/Kontext, Version, Status | Dimension, Skala, Zeitraum, Sichtbarkeit (O) | Platzhalter bis zur separaten Spezifikation |
| Radar-Beobachtung | Beobachtung gemäß freigegebenem Profil | Person, Dimension, Zeitpunkt/Zeitraum, Erheber | Wert/Kommentar (O) | Zweck, Bedeutung, Zugriff und Aufbewahrung erst klären |
| Zeugnisvorlage/-stand | Vorgaben und konkreter Entwurf/Ausgabe | Schulformprofil, Zeitraum, Version, Status | Pflichtfelder, Texte, Freigabe, Export (O) | Pro Einsatz fachlich/rechtlich prüfen, PDFs einbeziehen |

## Versionierung und Freigabe

| Objektfamilie | Mindestprinzip |
|---|---|
| Stunden-/Raum-/Vertretungspläne | Veröffentlichte Version unveränderlich, Entwurf separat. Kopieren, ändern, Konflikte prüfen, freigeben, veröffentlichen. Vergangene Ereignisse behalten den wirksamen Stand. |
| Regelprofile | Version mit Quelle, Fundstelle, Kontext, Gültigkeit, Prüfstatus. Neue Fassung statt Überschreiben; Aktivierung nach Freigabe. Betroffene Pläne anzeigen, nicht verändern. |
| Bewertungsverfahren | Unveränderliche freigegebene Formel-/Parameterfassung. Ergebnis referenziert exakte Version und maßgebliche Eingaben; Neuberechnung wird neuer Stand. |
| Sitzpläne | Version mit Gültigkeitszeitraum. Alte Fassung nur im bestätigten erforderlichen Umfang verfügbar halten. |
| Klassenbuch | Korrekturhistorie mit Zeitpunkt und Bearbeiter; Frist und Zugriff sind noch festzulegen. |
| Zeugnisvorlage/-ausgabe | Ausgabe bleibt mit Vorlagen- und Freigabestand verbunden; Berichtigung erzeugt eine neue Ausgabe. |

Allgemeiner Entwurfstatus: **Entwurf → in Prüfung → freigegeben/veröffentlicht → abgelöst**. Zustände werden je Modul verfeinert.

## Erste Migrations- und Löschstrategie

### Migration
- Ab v0.1.0 nummerierte, versionierte Schemaänderungen. Jede Migration dokumentiert Grund, Objekte/Felder, Vorbedingungen, erwartete Wirkung, Integritätsvalidierung, Sicherung, Rollbackgrenze und Anwendungskompatibilität.
- Vorher: Sicherung nach freigegebenem Betriebskonzept, Integritätsprüfung und Test mit synthetischen/minimierten Daten. Produktionskopien in Test/Entwicklung sind ausgeschlossen.
- Keine stille semantische Umdeutung; Herkunft und fachliche Freigabe von Korrekturen bleiben nachvollziehbar.
- Importe dokumentieren Quelle, Zeitpunkt, Zuordnungsregeln und Fehler. Externe IDs bleiben getrennt. Dubletten werden nicht durch bloße Namensähnlichkeit automatisch verbunden.
- Kein destruktiver Rollback nach neuen Fachdaten; dann Korrekturmigration oder freigegebener Restore-Plan.

### Aufbewahrung, Einschränkung und Löschung
- Jedes personenbezogene Objekt und jeder Anhang braucht vor Produktivbetrieb eine freigegebene Lifecycle-Zuordnung: Zweck, Verantwortliche, Betroffene, Zugriff, Empfänger, geltende Regel/Frist, Auslöser, Löschverantwortung sowie Exporte/Backups.
- Keine pauschale Frist für Klassenbuch, Zeugnisse, Noten, Nachrichten, Aufgaben, Sitzpläne, Pläne oder Sicherheitsprotokolle. Aktuelle Spezialvorschriften und konkrete Schul-/Trägerentscheidung sind zu prüfen.
- Schritte unterscheiden: beenden, Zugriff einschränken, Anfrage/Export prüfen, Löschung vormerken, freigeben/durchführen und Ergebnis dokumentieren. Vormerkung ist keine Löschung.
- Vor Löschung Aufbewahrungspflichten, Sperren, Zuständigkeit und Sicherungen prüfen. Freigegebene Löschung bezieht Primärspeicher, Dateien, Suchindizes, Caches, Exporte und Backup-Rotation ein.
- Falls Sicherungen nicht sofort bereinigt werden können: Zugriff verhindern, Wiederherstellung mit erneuter Löschung absichern, Auslaufzeit dokumentieren.
- Auditprotokolle enthalten keine unnötigen Inhalte. Verbleibende Referenzmetadaten brauchen eigene Prüfung.
- Verantwortliche bearbeiten Betroffenenrechte. Das Modell entscheidet nicht über Rechtsgrundlage, Ausnahme oder Anspruch.

## Fachliche Invarianten

1. Unterrichtszuweisungen referenzieren gültige Schule, Schuljahr, Lerngruppe, Fach und Zeitraster; Lehrkraft/Raum müssen im Zeitraum gelten.
2. Veröffentlichte Pläne referenzieren gültige Stammdaten/Regelversionen oder zeigen fehlende Prüfungen an.
3. Assistenz schlägt Änderungen vor, gibt sie aber nicht selbst frei.
4. Vertretung referenziert die betroffene Stunde und löscht die Ausgangsplanung nicht.
5. Klassenbucheintrag referenziert eine konkrete Stunde; fehlender Planbezug ist eine sichtbare Ausnahme.
6. Eine Person hat je Stunde keinen widersprüchlichen aktiven Anwesenheitsstatus; Korrektur bleibt nachvollziehbar.
7. Leistungswerte referenzieren Person, Erhebung und maßgebliche Verfahrensversion.
8. Anhangrechte folgen dem Elternobjekt; der Speicherpfad ist nicht öffentlich.
9. ID, Suche, Export und Benachrichtigung beachten denselben Berechtigungsscope.
10. Zeitgebundene Zuordnungen verhindern unzulässige Überschneidungen; Regeln werden je Modul präzisiert.

## Offene Entscheidungen

| Thema | Stand / nächste Klärung |
|---|---|
| Einzelne Schule oder Mehrschulbetrieb | offen (D-010); Architektur/Verantwortlichkeit v0.0.7 |
| ID-Format, Zeit-/Zeitzone, physische Trennung | v0.0.7; keine Vorfestlegung auf SQL/NoSQL |
| Rollen- und Rechtsscopes | Entwurf vorhanden; Schulabnahme vor Pilot |
| Stammdatenimport/Quellsystem | Quelle, Qualität, Rechtsgrundlage und Korrektur je Import klären |
| Thüringer Schularten/Kohorten | Umfang offen (D-022); nur aktuelle geprüfte Quellen aktivieren |
| Schülerfelder/externe IDs | Erforderlichkeit je Zweck prüfen |
| Notenformeln/Fehlleistungen | fachlich offen; Spezifikation vor v0.12.0 |
| Radarspinne | Dimensionen/Skalen offen; separate Spezifikation vor v0.13.0 |
| Aufbewahrung/Löschung/Backups | je Betrieb offen; Verantwortliche/Datenschutzbeauftragte vor Pilot |
| Zeugnisvorlagen | offen; pro Schulform fachlich/rechtlich prüfen |

## Abnahme v0.0.6

- Kernobjekte aus dem Projektplan sind definiert und verknüpft.
- Stammdatenquellen sind benannt und werden von Modulen referenziert.
- Planung, Unterrichtsereignis, Vertretung und Klassenbuchnachweis sind getrennt.
- Pläne, Regeln, Bewertungsverfahren, Sitzpläne und Zeugnisse haben Versionierungs-/Freigabeprinzipien.
- Personenbezogene Objektgruppen haben Zweck-, Zugriffs- und Lifecycle-Hinweise; Rechtsgrundlagen/Fristen bleiben offen.
- Migration und Löschung berücksichtigen Tests, Sicherungen, Rückfallgrenzen, Anhänge und Exporte.
- Keine Datenbank-/Hostingentscheidung und keine echte Schülerdatenverarbeitung; nur synthetische Daten.
