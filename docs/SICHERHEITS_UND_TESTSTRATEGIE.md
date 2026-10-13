# Sicherheits- und Teststrategie – OrdiSchool

**Version:** 0.0.8  
**Stand:** 13. Oktober 2026  
**Status:** Prüfplan für kommende Implementierungen; noch keine ausgeführten Sicherheits- oder Abnahmetests.

## 1. Ziel und Geltungsbereich

Diese Strategie überführt die Anforderungen aus Projektplan, Datenmodell und Hostingentscheidung in überprüfbare Sicherheits- und Qualitätstests. Sie deckt Rollen und Datenbereiche, Stunden-/Raum-/Vertretungsregeln, Klassenbuch, Notenverfahren, Anhänge, Suche, Kommunikation, Exporte, Löschung, Protokolle, Migration und Wiederherstellung ab.

OrdiSchool verarbeitet im geplanten Umfang Daten von Lernenden, darunter Anwesenheit, Leistungsstände, Nachrichten und schulische Notizen. Deshalb wird OWASP ASVS 5.0.0 als technische Prüfbasis verwendet; als anfängliche Orientierung gilt die Auswahl relevanter Level-2-Anforderungen für Anwendungen mit schutzbedürftigen Daten. Die verantwortliche Stelle und ein qualifiziertes Security Review müssen den konkreten Umfang bestätigen. Das ist weder eine Zertifizierung noch ein Nachweis vollständiger Sicherheit. [OWASP ASVS](https://owasp.org/projects/asvs)

Die Umsetzung beginnt erst ab v0.1.0. Bis dahin sind die folgenden Punkte Strategie und Sollprüfungen, keine Behauptung bereits bestandener Tests. Die Review-Tiefe ist risikobasiert und ergänzt durch NIST SSDF 1.1 als Entwicklungsrahmen. [NIST SSDF 1.1](https://csrc.nist.gov/pubs/sp/800/218/final)

## 2. Leitlinien

1. **Sicherheit in jedem Release:** Jede Änderung wird nach Auswirkungen auf Daten, Rollen, Schnittstellen, Logs, Export und Löschung geprüft.
2. **Berechtigungstests aus der Matrix:** Rollen, Aktion, Datentyp und Scope werden gemeinsam getestet. Eine erfolgreiche Anmeldung belegt keine Berechtigung.
3. **Negativfälle zuerst:** Tests müssen beweisen, dass Unberechtigte nichts erhalten oder verändern, auch über Direkt-ID, Suche, Export, Benachrichtigung und Dateidownload.
4. **Datenschutzarme Testdaten:** Lokal und in CI ausschließlich synthetische Daten. Reale Schülerdaten werden nicht kopiert, auch nicht pseudonymisiert, wenn synthetische Fälle genügen.
5. **Freigabekette:** Vorschlag, Entwurf, Import oder Rechenresultat ersetzen keine fachliche Freigabe.
6. **Reproduzierbare Prüfungen:** Testfall, Eingabe, erwartetes Ergebnis, Lauf, Build-/Versionsstand und Ergebnis sind nachvollziehbar.
7. **Sichere Fehlerreaktion:** Fehler geben keine Geheimnisse, personenbezogenen Inhalte oder Stacktraces an Nutzende aus; intern werden nur nötige Metadaten erfasst.
8. **Abweichung dokumentieren:** Nicht anwendbare Kontrollen werden mit Grund, Risiko, verantwortlicher Freigabe und möglicher Ersatzmaßnahme festgehalten.

## 3. Sicherheitsziel und Prüfbasis

### ASVS-Auswahl

- Referenz: OWASP Application Security Verification Standard, Version 5.0.0; Kontrollen erhalten im Testkatalog vollständige Versionskennung.
- Startniveau: relevante ASVS-Level-2-Kontrollen für Authentifizierung, Autorisierung, Eingabe-/Ausgabebehandlung, Sitzungen, Datei- und Datenverarbeitung, Kryptografie, Datenschutz, Konfiguration und Fehlerbehandlung.
- Nicht jede Kontrolle ist automatisch anwendbar. Auswahl und Begründung werden in einer versionierten Kontrollliste festgehalten.
- Level 2 ist ein Arbeitsziel für den technischen Entwurf, kein Gütesiegel, keine Rechtskonformität und kein Ersatz für einen externen Penetrationstest.
- Eine verantwortliche Stelle kann strengere Anforderungen festlegen; ein niedrigeres Niveau braucht dokumentierte Freigabe.

### Testebenen

| Ebene | Zweck | Zeitpunkt / Verantwortung |
|---|---|---|
| Unit-Test | Einzelne Regeln, Berechnungen, Validierungen und Zustandswechsel isoliert prüfen | bei jeder Codeänderung, Entwicklung |
| Integrationstest | Rechteprüfung, Datenbank, Dateien, Transaktionen, Jobs und Migrationen zusammen prüfen | CI je Merge/Release |
| End-to-End-Test | Kernprozesse aus Sicht konkreter Rollen und Scopes durchspielen | je Modul/Release |
| Regressionstest | behobene Fehler und Zugriffsschranken erneut prüfen | jeder Release-Kandidat |
| Statische Analyse / Abhängigkeiten | Codefehler, unsichere Muster und bekannte Abhängigkeitsschwächen finden | automatisiert im CI, vor Release |
| Dynamischer Test | laufende Testinstanz auf typische Webfehler prüfen | Release-Kandidat, isolierte Testumgebung |
| Manuelle Sicherheitsprüfung | Logikfehler, Umgehung, Geschäftsabläufe und Missbrauch bewerten | Änderungen an IAM, Export, Datei, Freigaben, Bewertung und Datenschutz |
| Externer Penetrationstest | unabhängige Prüfung konkreter Umgebung und Angriffsfläche | vor Pilot/Produktion und nach wesentlicher Architekturänderung |
| Restore-/Betriebstest | Sicherung, Rücksicherung und Wiederaufnahme der Dienste belegen | vor Pilot, danach nach Betreiberplan |

Automatisierte Scanner sind Hilfsmittel. Ein grüner Lauf beweist weder vollständige Sicherheit noch rechtliche Zulässigkeit.

## 4. CI- und Testumgebungen

- Pull Requests führen relevante Unit-, Integrations-, Migrations-, Berechtigungs- und Abhängigkeitsprüfungen aus.
- Neue Endpunkte, Suchpfade, Dateizugriffe und Exporte benötigen Berechtigungstests.
- CI-Secrets sind zweckgebunden und von Produktivzugängen getrennt; keine Schlüssel in Logs oder Artefakten.
- Testdaten sind synthetisch und als Testdaten markiert, z.B. mit reservierten Namen und Domains wie example.invalid.
- CI und Testsysteme enthalten keine Produktivdaten. Testdateien sind harmlose, selbst erstellte Prüfmuster.
- Migrationstests beginnen mit leerer Datenbank und repräsentativen synthetischen Vorversionen; Vorwärtsmigration, Integrität und sicherer Fehlerabbruch werden geprüft.
- Externe E-Mail, Push, KI, Analytics und echte Drittanbieterzugriffe sind im Test standardmäßig deaktiviert oder lokal ersetzt.
- Testberichte enthalten Buildkennung, Testfall-IDs, Status, Datum und Befundreferenz, aber keine sensiblen Testinhalte.
- Ein fehlgeschlagener sicherheitskritischer Test blockiert den Merge in einen Release-Zweig.

## 5. Testfallmatrix

Status in v0.0.8: **geplant**, nicht ausgeführt. Ergebnisse werden ab Implementierung im Repository dokumentiert.

### 5.1 Identität, Rollen und Schul-/Klassenscope

| Test-ID | Prüffall | Erwartetes Ergebnis | Anforderungsbezug |
|---|---|---|---|
| IAM-T01 | Lehrkraft ruft Datensatz aus nicht zugewiesener Klasse per Direkt-ID auf | kein Inhalt; einheitliche Ablehnung/404 ohne Existenz-Leak | IAM-001, IAM-003, SEC-001 |
| IAM-T02 | Lernende suchen, exportieren oder erraten Datensätze anderer Lernender | keine fremden Treffer, Metadaten oder Dateien | IAM-003, PRIV-005 |
| IAM-T03 | Listen, Suche, API, Direktlink, Vorschau, Export und Benachrichtigung mit gleichem Rollen-/Scope-Testset | kein Zugriffspfad weicht von der Berechtigung ab | IAM-003, SEC-001 |
| IAM-T04 | Rollenentzug während aktiver Sitzung; danach alter Link oder Token | Entzug greift bei nächster geschützter Aktion | IAM-006 |
| IAM-T05 | Technische Adminrolle liest Noten, Anwesenheit, Nachrichtentext und Klassenbuchnotiz | standardmäßig verweigert; Ausnahme nur begründet, befristet und protokolliert | IAM-004, IAM-005 |
| IAM-T06 | Wechsel von Klasse, Fach, Vertretung oder Schulzuordnung mit alten/neuen Zeiträumen | aktuelle Rechte stimmen; historische Pläne bleiben nachvollziehbar | DATA-002, IAM-001 |
| IAM-T07 | Nutzer mit Erstellrecht versucht zu veröffentlichen/freizugeben | abgewiesen, sofern kein separates Freigaberecht besteht | IAM-002, FLOW-001 |
| IAM-T08 | Zugriff auf Schul-Scope A durch Konto aus Scope B im späteren Mehrschultest | keine fremden Stammdaten, Suchresultate, Dateien oder Fehlerdetails | ARCH-006, ARCH-007 |

### 5.2 Planung, Vertretung und Klassenbuch

| Test-ID | Prüffall | Erwartetes Ergebnis | Anforderungsbezug |
|---|---|---|---|
| PLAN-T01 | Raumkapazität kleiner als Gruppe oder nötige Ausstattung fehlt | Konflikt sichtbar; keine irreführende konfliktfreie Freigabe | PLAN-003, PLAN-015 |
| PLAN-T02 | Fachraumpriorität ohne gültigen lokalen Nachweis | ungeprüfter Status; keine Behauptung verbindlicher Regelkonformität | PLAN-012, PLAN-014, DATA-012 |
| PLAN-T03 | Lehrkraft, Raum oder Klasse zeitgleich doppelt belegt | Konflikt erkannt; Freigabe gemäß Regel blockiert oder begründet | PLAN-004, PLAN-006 |
| PLAN-T04 | Harte Vorgabe verletzt, weiche Präferenz erfüllt | harte Verletzung hat Vorrang und wird erklärt | PLAN-006, PLAN-014 |
| PLAN-T05 | Vertretungsvorschlag beruht auf inzwischen freigegebenem, verändertem Plan | veralteter Entwurf erkannt; erneute Prüfung vor Freigabe | PLAN-009, FLOW-001 |
| PLAN-T06 | Assistenz erstellt Planvorschlag | Ausgang, Ziel, Konflikte und Folgen sichtbar; keine automatische Veröffentlichung | PLAN-009, PLAN-010 |
| PLAN-T07 | Ersatzlehrkraft/Raum wird für Stunde geändert | ursprünglicher Plan bleibt erhalten; Änderung referenziert konkrete Stunde | DATA-003, PLAN-016 |
| KB-T01 | Abgleich erkennt fehlenden Eintrag oder Stundenabweichung | Hinweis an berechtigte Rolle; keine automatische Änderung/Löschung | KB-004, KB-006, FLOW-002 |
| KB-T02 | Verspätet ohne Minuten oder widersprüchliche Anwesenheitswerte | Eingabe wird validiert und nachvollziehbar korrigiert | KB-002, DATA-003 |
| KB-T03 | Unberechtigte Person ruft Klassenbuchnotiz ab | verweigert; Inhalt erscheint nicht in Suche, Benachrichtigung oder Logs | KB-002, IAM-003, PRIV-012 |

### 5.3 Bewertungen und Radarverfahren

Erst ausführbar, wenn Verfahren fachlich festgelegt und freigegeben sind (D-016, D-017).

| Test-ID | Prüffall | Erwartetes Ergebnis | Anforderungsbezug |
|---|---|---|---|
| GRADE-T01 | Referenzfälle für jede freigegebene Formel, Gewichtung und Notenart | Ergebnis entspricht unabhängig berechnetem, bestätigtem Sollwert | NOTE-001–003 |
| GRADE-T02 | Rundungsgrenzen sowie fehlende, ausstehende, entschuldigte und nachgeholte Leistungen | spezifizierte Regel konsistent; kein stilles Nullsetzen | NOTE-002 |
| GRADE-T03 | Ergebnis nach Versionierung des Bewertungsverfahrens | altes Ergebnis behält alte Version; Neuberechnung ist neuer Stand | NOTE-004, DATA-005 |
| GRADE-T04 | unberechtigte Notenänderung/Freigabe per Oberfläche oder API | abgewiesen und erforderlichenfalls protokolliert | NOTE-005, IAM-003, SEC-003 |
| RADAR-T01 | Wert außerhalb freigegebener Skala, unbekannte Dimension, falscher Zeitraum | Eingabe abgewiesen; keine implizite Umdeutung/Notenberechnung | RADAR-001, RADAR-002 |

### 5.4 Anhänge, Kommunikation, Exporte und Datenschutz

| Test-ID | Prüffall | Erwartetes Ergebnis | Anforderungsbezug |
|---|---|---|---|
| FILE-T01 | falsche Dateiendung/MIME, zu große oder ausführbare Datei | abgewiesen oder nach freigegebener Scanregel isoliert; niemals öffentlich ausführbar | SEC-004 |
| FILE-T02 | Datei-ID getauscht oder alter Downloadlink ohne Rechte genutzt | kein Dateiinhalt, auch nicht über erratbare ID oder Cache | DATA-008, SEC-001 |
| FILE-T03 | Dateiname mit Pfadanteil, Steuerzeichen oder Scriptfragment | sicher normalisiert/abgewiesen; kein Pfadzugriff/XSS | SEC-004 |
| FILE-T04 | Anhang eines gelöschten/gesperrten Elternobjekts | Zugriff und Lifecycle folgen Elternobjekt; kein verwaister öffentlicher Anhang | DATA-010 |
| MSG-T01 | Gruppenmitglied wird entfernt oder dessen Scope endet | Zugriff folgt gültiger Berechtigung; Benachrichtigung bleibt minimal | MSG-002, FLOW-003 |
| EXP-T01 | Export mit zu breitem Zielgruppenfilter | auf erlaubten Scope begrenzt; Export nachvollziehbar | PRIV-005, PRIV-011, IAM-005 |
| EXP-T02 | Export enthält Datei/Feld ohne freigegebenen Zweck | Feld/Datei ausgeschlossen oder Vorgang blockiert | PRIV-006, PRIV-008 |
| DEL-T01 | Löschantrag mit möglicher Aufbewahrungspflicht | Prüfung/Sperre statt unzulässiger Löschung oder unbegrenztem Liegenlassen | PRIV-008, PRIV-011 |
| DEL-T02 | freigegebene Löschung aus Dateien, Suche, Cache und Exportablage | alle definierten aktiven Speicherorte erfasst; Ergebnis dokumentiert | DATA-010, PRIV-008 |
| DEL-T03 | Backup wird nach Löschung wiederhergestellt | gelöschte/gesperrte Daten werden vor Wiederbetrieb gemäß Verfahren erneut entfernt/gesperrt | PRIV-008, SEC-005 |
| LOG-T01 | Loginfehler, Rechtefehler, Rollenänderung, Freigabe, Export, Notfallzugriff | nötiges Ereignis protokolliert; keine Passwörter, Tokens, Noten oder Freitexte | SEC-003, IAM-005 |
| PRIV-T01 | externe Netzwerkverbindungen in Testumgebung überwachen | keine ungeplante Übermittlung an Analytics, KI, CDN oder E-Mail | PRIV-013, SEC-006 |
| PRIV-T02 | zuständige Rolle bearbeitet Auskunft/Berichtigung/Einschränkung | Scope, Prüfschritte, Ergebnis und rechtlich begründete Ausnahme nachvollziehbar | PRIV-005, PRIV-011 |

### 5.5 Migration, Betrieb und Restore

| Test-ID | Prüffall | Erwartetes Ergebnis | Anforderungsbezug |
|---|---|---|---|
| OPS-T01 | Vorwärtsmigration von leerem und synthetisch befülltem Vorversionsschema | Schema und Integrität korrekt; Fehler bricht sicher ab | OPS-001, DATA-009 |
| OPS-T02 | Migration bricht in Testumgebung zwischen Schritten ab | konsistenter Zustand; Wiederholung/Korrektur gemäß Plan möglich | OPS-001 |
| OPS-T03 | Datenbank- und Dateisicherung in isolierte Umgebung zurückspielen | Inhalte und Verweise konsistent; Zugriff bleibt geschützt | SEC-005 |
| OPS-T04 | Pflichtschlüssel fehlen oder Testgeheimnis wird in Produktion erkannt | Start verweigert sicher oder Dienst bleibt geschlossen; Geheimnis wird nicht ausgegeben | SEC-002 |
| OPS-T05 | Hintergrundjob doppelt zugestellt oder Worker startet neu | keine doppelte Vertretung, Nachricht, Löschung oder Berechnung | ARCH-003, FLOW-001 |
| OPS-T06 | Job überschreitet Zeit-/Ressourcenlimit oder schlägt fehl | kontrollierter Abbruch, keine Teilveröffentlichung; Fehlerstatus sichtbar | ARCH-003 |
| OPS-T07 | Logspeicher voll oder Logziel nicht erreichbar | keine Datenkorruption; kontrolliertes Fehlerverhalten/Alarm | SEC-003, OPS-002 |

## 6. Fehler- und Risikoklassen

| Einstufung | Beispiele | Freigabefolge |
|---|---|---|
| Kritisch | Scope-übergreifender Zugriff auf Schülerdaten; Kontoübernahme; unkontrollierte Noten-/Planveröffentlichung; Datenverlust ohne Restore | Release-/Pilotstopp, Behebung, Regressionstest |
| Hoch | reproduzierbare Rechteumgehung; öffentliche vertrauliche Datei; Injection mit Datenwirkung; unkontrollierter Export; fehlgeschlagene Wiederherstellung | kein Release/Pilot bis Behebung und Nachtest |
| Mittel | begrenzte Kontrollverletzung oder Betriebsrisiko mit möglicher Kompensation | nur mit Risikofreigabe, Frist und Ticket |
| Niedrig | geringe Auswirkung ohne Daten-/Rechtewirkung | dokumentieren und priorisiert einplanen |

Einstufung berücksichtigt Auswirkung, Betroffene, Umfang, Ausnutzbarkeit, Exposition, Entdeckbarkeit und Wiederherstellung. Ein Scannerwert allein entscheidet nicht.

## 7. Release-, Pilot- und Produktionsgates

### Merge in Hauptzweig / Release-Kandidat
- Pflicht-CI ist grün; neue Funktionen haben positive und negative Tests.
- Rechte-/Scope-Tests umfassen betroffene Endpunkte, Exporte und Dateien.
- Abhängigkeiten und bekannte Schwachstellen sind geprüft; Ausnahmen haben Begründung.
- Migrationen laufen mit synthetischen Daten; keine Secrets/Produktivdaten in Diff, Artefakt oder Log.
- Keine offenen kritischen oder hohen Sicherheitsbefunde.

### Vor Pilot mit echten Daten
- Verantwortliche, Rollen, Zwecke, Rechtsgrundlagen und Datenschutzhinweise stehen fest.
- Hosting/AVV, Unterauftragnehmer, Daten-/Supportorte, TOM, Zugriff und Rückgabe/Löschung sind geprüft.
- DSFA-Screening ist erfolgt; falls erforderlich, ist die DSFA vor Pilot abgeschlossen.
- IAM-, Datei-, Export-, Lösch-, Backup-/Restore- und Vorfalltests sind für den Pilotumfang bestanden.
- Unabhängige Sicherheitsprüfung und Befunde sind bewertet; kritische/hohe Befunde geschlossen.
- Restore, Notfallkontakt, Rückfall, Support, RPO/RTO und Incident-Ablauf sind geprobt/freigegeben.
- Fachverantwortliche bestätigen aktivierte Regeln, Notenverfahren und Klassenbuchabläufe.
- Pilotumfang, Laufzeit, Teilnehmerkreis, Support und Abbruchkriterien sind begrenzt dokumentiert.

### Vor allgemeinem Produktivbetrieb
- Pilotbefunde sind geschlossen oder nachvollziehbar akzeptiert; keine kritischen/hohen offenen Befunde.
- Berechtigungen, Logs, Backup/Restore und Löschung werden mit Zielkonfiguration geprüft.
- Betrieb hat Support, Updates, Vorfälle und regelmäßige Reviews zugewiesen.
- Nicht aktivierte Module/Integrationen bleiben deaktiviert.
- Releaseartefakt und Rückfallpfad sind versioniert.

Kein Gate garantiert vollständige Sicherheit oder DSGVO-Konformität. Es verlangt, bekannte Risiken zu prüfen und bewusst zu behandeln.

## 8. Befunde und Vorfälle

- Befunde erhalten ID, Version/Komponente, Reproduktionsschritte ohne echte Schülerdaten, Einstufung, Verantwortliche und Frist.
- Ausnutzbare Details werden nicht öffentlich dokumentiert, bevor koordinierte Behebung abgestimmt ist.
- Nach Behebung kommt ein Regressionstest hinzu; sicherheitsrelevante Änderungen erhalten fokussiertes Review.
- Mögliche Datenschutzverletzungen werden unverzüglich an die für den Betrieb zuständige Stelle eskaliert. Diese prüft die konkrete Lage und entscheidet über Meldungen/Fristen.
- Vorfallprotokolle enthalten nur erforderliche Angaben und sind geschützt.
- Ein externer Sicherheitskontakt wird erst veröffentlicht, wenn der Betrieb den Prozess eingerichtet hat.

## 9. Testdaten und Testnachweise

Synthetische Fixtures enthalten keine echten Namen, Kennungen, Nachrichten, Noten oder Anwesenheiten. Sie bilden dennoch Beziehungen ab: mehrere Schulen für spätere Isolationstests, überlappende Rollen, abgelaufene Zuständigkeiten, Teilungsgruppen, Doppelstunden, Kapazitätsgrenzen, Versionen und Löschzustände.

Je Lauf werden Test-ID, Requirement-ID(s), Build/Commit, Umgebung, Prüfer/Automatisierung, Ergebnis, Datum, Befundverweis und Freigabestatus festgehalten. Berichte enthalten keine vollständigen Testinhalte. Ausführbare Testautomatisierung entsteht ab v0.1.0 im Code-Repository.

## 10. Abnahmekriterien v0.0.8

- Bedrohungsmodell benennt Schutzobjekte, Akteure, Vertrauensgrenzen, Szenarien und Gegenmaßnahmen.
- Teststrategie deckt Rechte, Planregeln, Noten, Exporte, Löschung, Anhänge, Migration und Restore ab.
- Testfälle sind Anforderungen und Datenrisiken zugeordnet.
- Merge-, Pilot- und Produktionsgates blockieren bei kritischen/hohen Befunden.
- Entwicklung/CI verwenden keine realen Schülerdaten.
- OWASP ASVS 5.0.0 und NIST SSDF 1.1 sind versioniert referenziert; keine Zertifizierung wird behauptet.
- Kein Produktivcode und keine echten personenbezogenen Testdaten in dieser Version.

## 11. Offene Punkte für v0.0.9 / Pilot

- Exakte ASVS-Kontrollauswahl und Verantwortliche.
- Schulspezifische Folgenabschätzung und Umgang mit besonderen Datenkategorien.
- Penetrationstestverfahren, Prüfer und konkreter Umfang.
- Incident-Kontakte, Meldeweg, Supportbereitschaft, RPO/RTO und Verfügbarkeit.
- Priorisierung im Backlog; Testautomatisierung ab v0.1.0.
