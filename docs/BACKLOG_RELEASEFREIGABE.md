# Priorisiertes Backlog und Releasefreigabe – OrdiSchool

**Version:** 0.0.9  
**Stand:** 13. Oktober 2026  
**Status:** Planungsbaseline für v0.1.0; frühe Aufwandsspannen, keine Termin- oder Lieferzusage.

## 1. Ziel

Diese Version ordnet das Zielbild in priorisierte, prüfbare Arbeit. Sie grenzt v0.1.0 auf eine technische Basis mit Datenbankverbindung, Anmeldung, initialer Administrationsrolle, automatisierten Grundtests und Restore eines synthetischen Teststands ein. Schulische Fachdaten und echte Schülerdaten sind ausdrücklich ausgeschlossen.

## 2. Priorisierungsregeln

- **P0 / MUSS:** Bestandteil von v0.1.0 oder notwendiges Freigabegate.
- **P1 / SOLL:** wichtig; Verschiebung benötigt dokumentierte Begründung.
- **P2 / SPÄTER:** spätere Version, nicht in v0.1.0 hineinziehen.
- Sicherheits- und Datenschutzanforderungen sind keine optionalen Komfortmerkmale.
- Fertig ist eine Aufgabe erst, wenn Akzeptanzkriterien, Tests, Dokumentation und Datenschutz-/Sicherheitsauswirkungen geprüft sind.

## 3. Verantwortlichkeiten

| Bereich | Zuständigkeit | Freigabe / Grenze |
|---|---|---|
| Produktziel, Funktionspriorität | Nutzer als Product Owner | entscheidet den Funktionsumfang und Änderungswünsche |
| Umsetzung und technische Dokumentation | Entwicklungsarbeit im gemeinsamen Projekt | folgt dem freigegebenen Backlog; technische Abweichungen werden begründet |
| Schulabläufe und Thüringer Regeln | Product Owner plus benannte fachliche Schulrollen | verbindliche Regeln nur mit aktueller Quelle und fachlicher Bestätigung |
| Datenschutz, Rechtsgrundlagen, DSFA | konkrete verantwortliche Schule/Träger und Datenschutzstelle | technische Freigabe ersetzt diese Prüfung nicht |
| Hosting, Betrieb, Backup, Support | noch zu benennender Betreiber/Schul-IT/Anbieter | vor Produktionszugang und echten Daten festlegen |
| Unabhängige Sicherheitsprüfung | vor Pilot zu bestimmende qualifizierte Prüfstelle | Umfang und Befundbearbeitung vor Pilot vereinbaren |
| Releasefreigabe | Product Owner fachlich; zuständige Stelle für Datenschutz/Betrieb | getrennte Verantwortlichkeiten, keine pauschale Einzelfreigabe |

Eine Person kann mehrere Projektaufgaben übernehmen. Datenschutz-, Rechts- und Betriebsfreigaben gelten aber nur, wenn die tatsächlich zuständige Stelle sie erteilt hat.

## 4. Frühe Aufwandsschätzung

Ein Personentag (PT) bedeutet einen Arbeitstag fokussierter Umsetzungszeit einer Person. Eingeschlossen sind Implementierung und grundlegende Entwicklerprüfungen, nicht Beschaffungswartezeiten, externe Beratung, Nutzerforschung oder unabhängiger Penetrationstest. Unsicherheit derzeit etwa ±40%; nach den ersten zwei Backlog-Einträgen neu schätzen.

| Größe | Orientierung |
|---|---:|
| S | 0,5–1 PT |
| M | 1–3 PT |
| L | 3–5 PT |
| BK-001 bis BK-009, v0.1.0 Basisscope | ca. 14–27 PT |
| Integrations-/Korrekturpuffer | ca. 3–8 PT |
| Planungsrahmen v0.1.0 | ca. 17–35 PT |

Bei drei bis vier fokussierten Projekttagen pro Woche entspricht dies grob fünf bis zwölf Wochen. Kalender- und Aufwandsschätzungen sind keine Zusage; Lernaufwand, Rückfragen und Hostingentscheidungen beeinflussen sie.

## 5. Priorisiertes Backlog

### v0.1.0 – Technisches Grundgerüst und Anmeldung

| ID | Prio | Liefergegenstand | Akzeptanzkriterien | Aufwand | Zuständig / Abhängigkeit |
|---|---|---|---|---:|---|
| BK-001 | P0 | Repository-Grundstruktur und Entwicklungsanleitung | lokale Einrichtung reproduzierbar; Modulgrenzen dokumentiert; Voraussetzungen klar; keine Secrets in Git | M / 1–2 PT | Entwicklung; Start |
| BK-002 | P0 | PHP-Laufzeit, Abhängigkeiten und Build | Ziel PHP 8.5 aus v0.0.7 geprüft; Abhängigkeiten festgeschrieben; Build reproduzierbar; Lizenz-/Schwachstellenübersicht | M / 1–2 PT | Entwicklung; BK-001 |
| BK-003 | P0 | Konfiguration und Fehlerbehandlung | Beispielkonfiguration ohne echte Schlüssel; Pflichtwerte validiert; Umgebungsprofile getrennt; Nutzende sehen keine Stacktraces/Secrets | M / 1–2 PT | Entwicklung; BK-001/002 |
| BK-004 | P0 | Datenbankverbindung und erste Migration | Verbindung zur Test-SQL-Engine; versionierte technische Startmigration; Migration auf leerem Teststand und Wiederholung geprüft; keine Schulstammdaten | M / 2–3 PT | Entwicklung; BK-003 |
| BK-005 | P0 | Anmeldung, Logout und Sitzungsverwaltung | angemessener Passwort-Hash; sichere Session-Cookies; Sessionwechsel nach Login; Logout beendet Session; CSRF-Schutz; Begrenzung von Loginversuchen | L / 3–5 PT | Entwicklung; BK-003/004, ASVS-Prüfung |
| BK-006 | P0 | Initiale technische Adminrolle und geschützter Platzhalter | initialer Admin durch dokumentierten einmaligen Prozess, kein Standardpasswort; ohne Anmeldung kein Zugriff; keine pädagogischen Fachrechte | M / 1–3 PT | Entwicklung; BK-005 |
| BK-007 | P0 | Grundtests und CI | automatisierter Build; Tests für Login/Logout, Zugriff ohne Login, falsches Passwort, Sessionentzug und Migration; synthetische Daten, keine Prod-Secrets | M / 2–4 PT | Entwicklung; BK-001 bis 006 |
| BK-008 | P0 | Umgebungen, Update- und Rückfallablauf | Entwicklung und Test getrennt; Produktionsprofil beschrieben, aber ohne Produktivdaten; Release-/Migrations-/Rückfallweg dokumentiert | M / 2–4 PT | Entwicklung und später Betreiber; BK-003/004/007 |
| BK-009 | P0 | Testbackup und Restore | synthetische Datenbank und Dateien sichern; Restore in isolierter Umgebung; Integritätsprüfung und Schritte dokumentiert | M / 1–2 PT | Entwicklung und später Betreiber; BK-004/008 |

### Folgestufen

| ID | Zielversion | Prio | Liefergegenstand | Abhängigkeit / Freigabebedingung |
|---|---|---|---|---|
| BK-010 | 0.2.0 | P0 | Schulstammdaten, Kontenbezug, Schul-Scope und Rollen | Verantwortliche/Betreiber benannt; Datenminimierung und Rollen abgenommen |
| BK-011 | 0.3.0 | P0 | Manuelle Stundenplanung mit mehreren Schulformen/Bildungsgängen je Schule, schulformübergreifenden Lehrkräfteeinsätzen, Entwurf und Freigabe | Angebote und Kohorten sind Klassen/Kursen zeitlich zuordenbar; Lehrkraftkonflikte werden schulformübergreifend geprüft; Doppelstunden sind darstellbar; Planungsrolle erstellt Entwürfe, aktive Schuladministration gibt frei; veröffentlichte Fassungen bleiben unverändert |
| BK-012 | 0.4.0 | P0 | Raumstamm, Kapazität, Fachraumtyp und Konfliktprüfung | lokale Raumwerte, Nachweis, Gültigkeit und Zuständigkeit bestätigt |
| BK-013 | 0.5.0 | P0 | Regelprofile mit Quelle, Gültigkeit und Prüfablauf | aktuelle Thüringer Regeln für die unterstützten Schulformen/Bildungsgänge geprüft; konkrete Regelwahl erfolgt aus Klasse/Kurs, Jahrgang, Kohorte und Schuljahr |
| BK-014 | 0.6.0 | P0 | Vertretung und Assistenzvorschläge mit menschlicher Freigabe | BK-011 bis 013 und Änderungs-/Freigabeworkflow bestätigt |
| BK-015 | 0.7.0 | P1 | Sitzplanverwaltung | Sichtbarkeit, Ausdruck/Export und Aufbewahrung geklärt |
| BK-016 | 0.8.0 | P0 | Klassenbuch, Anwesenheit, Abgleich- und Fehlerhinweise | Pflichtfelder, Rollen, Notizumfang, Spezialregeln und Lifecycle freigegeben |
| BK-017 | 0.9.0–0.11.0 | P1 | Aufgaben, Termine, Gruppen, Wiki, Nachrichten, Anhänge | Empfängerrechte, Uploadschutz, Löschung und externe Kanäle einzeln geprüft |
| BK-018 | 0.12.0 | P0 | Noten und versionierte Algorithmen | Notenarten, Gewichtung, Rundung, Fehlfälle und Fachfreigabe bestätigt |
| BK-019 | 0.13.0 | P1 | Notenfreies Radarbewertungssystem | Dimensionen, Skalen, Zweck, Empfänger, Interpretation und Fristen spezifiziert |
| BK-020 | 0.14.0–0.15.0 | P1 | Zeugnisse, Dashboard, Integration und Administration | Vorlagen, Export-/Integrationszwecke und Freigaben geprüft |
| BK-021 | 0.16.0–0.18.0 | P0 | Begrenzter Pilot, Beta, Release Candidate | Verantwortliche, Hosting, AVV soweit nötig, DSFA-Entscheidung, Restore-/Securitytests und Support liegen vor |
| BK-022 | 1.0.0 | P0 | Stabile Produktfreigabe für benannten Einsatz | Pilotbefunde geschlossen; fachliche, Betriebs-, Sicherheits- und Datenschutzfreigabe |

P0 im Gesamtplan bedeutet hohe fachliche Priorität. Es erlaubt keine Aktivierung ohne die genannten Rechts-, Daten- und Betriebsfreigaben.

## 6. Freigabeumfang v0.1.0

### Enthalten
- lokal reproduzierbare Einrichtung als modularer Monolith;
- PHP-8.5-Ziel nach Framework-/Abhängigkeitsprüfung;
- Test-SQL-Datenbank und versionierte technische Startmigration;
- Konfiguration ohne eingecheckte Geheimnisse;
- Anmeldung, Logout und sichere Sitzungsgrundlagen;
- einmalige Einrichtung einer technischen Adminrolle ohne Standardpasswort;
- geschützte Platzhalterseite nach Login;
- Entwicklungs-/Testkonfiguration, automatisierter Build und Kern-Regressionstests;
- Update-/Migrationsablauf und Restore eines synthetischen Teststands;
- Einschränkungen und Abnahmeprotokoll.

### Ausgeschlossen
- Schüler-/Lehrkraftimport oder produktive Schulstammdaten;
- Klassen, Kurse, Stunden-, Raum- oder Vertretungspläne;
- Klassenbuch, Anwesenheit, Noten, Zeugnisse und Radarbewertungen;
- Aufgaben, Gruppenräume, Nachrichten, Anhänge und Mail/Push;
- Mehrschul-/SaaS-Betrieb oder produktive Hostingfreigabe;
- echte Schülerdaten in Entwicklung, CI, Demo oder Restoretest;
- vollständiges pädagogisches Rollenmodell;
- Behauptung einer DSGVO-Konformität oder Produktivfreigabe.

Die Anmeldung ist technische Basis, noch kein vollständiges schulisches IAM. Der initiale Admin besitzt Installations-/Systemfunktionen und dadurch keinen pauschalen Zugriff auf pädagogische Inhalte.

## 7. Änderungs- und Entscheidungsverfahren

### Backlogänderung
1. Issue mit ID, Problem/Nutzen, betroffenen Anforderungen und Zielversion anlegen.
2. Dateninventar, Datenfluss, Rollen, Bedrohungsmodell, Tests und Releaseumfang auf Auswirkungen prüfen.
3. Aufwand, Abhängigkeiten, Risiko und Verschiebung anderer Arbeit dokumentieren.
4. Product Owner priorisiert fachlich; Sicherheits-/Architekturfolgen kommen ins Entscheidungslog.
5. Datenschutz-, Rechts- oder Betriebsfreigabe bei zuständigen Stellen einholen.
6. Erst danach Backlog und Zielversion ändern. Ein veröffentlichtes Release wird nicht still rückwirkend umdefiniert.

### Entscheidungslog
Jeder wesentliche Eintrag erhält ID, Datum, Status, Kontext, Optionen, Entscheidung/Begründung, Folgen, Requirement-IDs, prüfende/freigebende Rolle und Neubewertungskriterien.

### Git und Release
- Commits bleiben klein und thematisch zusammenhängend; sie referenzieren Versions- oder Backlog-ID.
- Commit-Schema: feat(v0.1.0): …, fix(v0.1.0): …, test(v0.1.0): …, docs(v0.1.0): ….
- Keine Secrets, echten Schülerdaten, Dumps oder vertraulichen Anhänge in Commits, Tags oder Release-Artefakten.
- Vor Release: Version, Changelog, Migration, Tests, Restorehinweis, offene Befunde und Freigabegrenzen dokumentieren.
- Release-Tag erst nach erfüllten Abnahmekriterien. Offene Kriterien bleiben sichtbar.

## 8. Abnahmekriterien v0.0.9

- Backlog enthält Priorität, Zielversion, Kriterien, Abhängigkeiten, Verantwortung und Aufwandsspanne.
- Schätzungen und Unsicherheiten sind als Planungswerte, nicht Liefertermine, gekennzeichnet.
- v0.1.0 ist konkret abgegrenzt und klein genug für eine überprüfbare Lieferung.
- Keine ungeprüften produktiven Schülerdaten oder pädagogischen Fachdaten im v0.1.0-Umfang.
- Datenschutz-, Rechts- und Betriebsfreigaben bleiben bei zuständigen Stellen.
- Entscheidungs-/Änderungsverfahren und Commit-/Releasekonvention sind dokumentiert.
- Spätere Versionen sind auf Anforderungen und Freigabegates bezogen.

## 9. Startkriterien v0.1.0

- PHP-Laufzeit und SQL-Engine sind lokal und in CI prüfbar.
- Technische Adminrolle ist von pädagogischem Zugriff abgegrenzt.
- Geheimnis- und Konfigurationsweg steht fest.
- Keine Produktivdaten werden für den Start benötigt.
- CI kann Tests und Migrationen ausführen.
- Offene Betriebs-/Datenschutzfragen verhindern keine lokale technische Entwicklung, bleiben aber verbindliche Gates vor Pilot.

## 10. Neubewertung

- Aufwand nach BK-001 und BK-002 kalibrieren.
- Entscheiden, ob vorhandenes Hosting nur für synthetisches Staging geprüft wird oder lokale Container genügen.
- Schule/Träger, Betreiber, Datenschutzstelle und fachliche Abnahmevertretungen benennen, bevor BK-010 oder personenbezogene Module starten.
- Falls Anmeldung/Session mehr Prüfung benötigt, v0.1.0 verschieben und den Sicherheitsumfang nicht zur Terminrettung reduzieren.


### BK-012 v0.4.0 Raumverwaltung und Planprüfung

Akzeptanz: Räume mit Typ/Kapazität anlegen; Gruppengröße optional erfassen; Raum einem Unterrichtseintrag zuordnen; Raumdoppelbelegung und bekannte Kapazitätsüberschreitung blockieren; Fachraumabweichung/fehlender Fachraum nur als Hinweis ausweisen; Hinweise verhindern keine Freigabe; kopierte Entwürfe bewahren Raumzuweisungen. Fachrechtliche Stundenplanregeln und automatische Änderungsvorschläge bleiben Folgeschritte.
