# Architektur- und Hostingentscheidung – OrdiSchool

**Version:** 0.0.7  
**Stand:** 13. Oktober 2026  
**Status:** Zielarchitektur und vorläufige Technologieentscheidung; konkreter Hostinganbieter und Produktivbetrieb sind nicht freigegeben.

## 1. Entscheidung in Kürze

Für die erste lauffähige OrdiSchool-Ausbaustufe wird eine **modulare Webanwendung als modularer Monolith** empfohlen:

- Backend: PHP 8.5, sofern die benötigten Bibliotheken und das ausgewählte Hosting diese Version stabil unterstützen.
- Datenhaltung: relationale SQL-Datenbank; Ziel MySQL 8.4 LTS. MariaDB 11.4 LTS ist eine mögliche Alternative, wenn die vollständige Test- und Migrationsstrecke dagegen läuft.
- Betrieb: zunächst eine isolierte Instanz für eine Schule, fachlich bereits mit Schul-Scope in allen Objekten und Berechtigungsprüfungen.
- Produktivhosting: verwaltete VM/VPS oder eine gleichwertige Schul-/Trägerumgebung in Deutschland oder EU, mit vertraglich und technisch nachgewiesenen Sicherheits-, Backup-, Update- und Supportbedingungen. Anbieter ist noch offen.
- Dateien: privater Speicher getrennt vom Webroot und von öffentlichen URLs; Verknüpfung über geschützte Datei-Metadaten.
- Hintergrundaufgaben: separater Worker/Joblauf für Benachrichtigungen, Importe, Exporte und aufwendige Planvorschläge; kein unbegrenzter Webrequest für Optimierungsläufe.
- Externe Analyse-, KI-, Tracking- und E-Mail-Dienste sind standardmäßig deaktiviert.

Die Wahl eines konkreten Anbieters ist **nicht** Bestandteil dieser Freigabe. Vor Produktivdaten müssen Verantwortliche, AVV, Unterauftragsverarbeiter, Speicher-/Supportorte, technische und organisatorische Maßnahmen (TOM), Löschung, Wiederherstellung und DSFA-Prüfung für den konkreten Betrieb belegt sein. Eine Serverregion in Deutschland oder ein AVV allein macht eine Software nicht DSGVO-konform.

## 2. Anforderungen an Architektur und Betrieb

| Bereich | Ziel / Muss-Kriterium | Freigabeprüfung |
|---|---|---|
| Zugriffsschutz | Serverseitige Rollen- und Scope-Prüfung für jeden Abruf, einschließlich Suche, API, Direktlinks, Downloads und Exporten | Negativtests und Rechte-Teststrategie aus v0.0.8 |
| Trennung pädagogischer Inhalte | Technische Betriebsrolle erhält standardmäßig keinen inhaltlichen Zugriff auf Schüler-, Noten-, Anwesenheits- oder Nachrichteninhalte | Rollen-/Berechtigungsprüfung; Support- und Notfallzugriffe dokumentieren |
| Datenbank | Unterstützte, gepflegte SQL-Version; transaktionale Datenänderungen; getestete Sicherung und Wiederherstellung | Version, Updateverantwortung und Restore-Probe nachweisen |
| Dateien | Privat, nicht direkt öffentlich abrufbar; Berechtigung bei jedem Zugriff; Typ-/Größenprüfung und Löschpfad | Test mit unberechtigtem Direktlink, Backup-/Löschprüfung |
| Updates | reproduzierbare Releases, Abhängigkeitssperre, sichere Konfiguration und getrennte Test-/Produktivumgebung | Release- und Rückfallablauf praktisch testen |
| Hintergrundverarbeitung | begrenzte, beobachtbare Jobs mit Fehler-/Wiederholungsbehandlung | Plan-/Importjob mit Zeitlimit, Dubletten- und Fehlerfall testen |
| Datenschutz | vertragliche Rollen, Zweck, Datenstandorte, Unterauftragnehmer und Rechteprozess für jeden Dienst bekannt | Verantwortliche Stelle und Datenschutzbeauftragte geben den Einsatz frei |
| Betriebskontinuität | RPO/RTO und Verfügbarkeit werden vom Verantwortlichen festgelegt; Backups sind kein Ersatz für Restoretests | Zielwerte und geprobter Wiederanlauf vor Pilot |
| Entwicklungsdaten | synthetische Testdaten; keine Produktivkopien in lokalen oder Testumgebungen | Build-/Testprozess und Datenexporte kontrollieren |
| Barrierefreiheit und mobile Nutzung | semantisches HTML, Tastaturbedienbarkeit, responsive Oberfläche; konkretes Konformitätsziel noch festzulegen | D-020 vor Pilot klären |

## 3. Vorgeschlagene Anwendungsarchitektur

~~~mermaid
flowchart TB
    browser["Browser von Lehrkräften und Lernenden"] -->|"HTTPS"| edge["TLS-Eingang und Schutzschicht"]
    edge --> app["PHP-Webanwendung als modularer Monolith"]
    app --> db["Private relationale Datenbank"]
    app --> files["Privater Dateispeicher"]
    app --> jobs["Job-Warteschlange"]
    jobs --> worker["Begrenzte Hintergrund-Worker"]
    worker --> db
    worker --> files
    db --> backup["Verschlüsselte, getrennte Sicherung"]
    files --> backup
~~~

Die Schutzschicht steht für TLS-Terminierung und die vom Betreiber bereitgestellten Netzwerk-/Request-Schutzfunktionen. Sie ist keine Zusage, dass ein bestimmter WAF- oder Proxy-Dienst benötigt oder bereits gewählt ist.

### Anwendungsmodule

- Identität und Berechtigungen
- Schulstammdaten und Gruppen
- Stunden-, Raum-, Regel- und Vertretungsplanung
- Klassenbuch und Anwesenheit
- Aufgaben und Anhänge
- Kalender und Nachrichten
- Bewertung und Zeugnisse
- Protokollierung und Datenschutzabläufe

Module teilen ein fachliches Datenmodell, aber keine unkontrollierten globalen Zugriffe. Module kapseln ihre Regeln und Schnittstellen. Ein späterer Dienst wird nur ausgegliedert, wenn messbare Last-, Sicherheits- oder Organisationsgründe dies rechtfertigen.

### Architekturleitlinien

1. Serverseitige Berechtigungsprüfung ist maßgeblich. Die Oberfläche kann Aktionen verbergen, aber nie die Zugriffskontrolle ersetzen.
2. Fachliche Änderungen werden über validierte Anwendungsfälle gespeichert; wichtige Freigaben und Korrekturen erhalten nachvollziehbare Metadaten.
3. Schüler-, Noten-, Anwesenheits- und Nachrichtendaten werden nicht an externe KI-, Analyse-, Werbe- oder Telemetriedienste übermittelt.
4. Dateien liegen außerhalb des Webroots oder in einem privaten Objektspeicher. Die Anwendung streamt sie erst nach erfolgreicher Rechteprüfung.
5. Datenbankzugänge, Schlüssel und Zugangsdaten stehen nicht im Git-Repository. Produktiv-, Test- und Entwicklungsgeheimnisse sind getrennt.
6. Kein Drittanbieter-CDN ist für Kernanmeldung oder pädagogische Inhalte erforderlich; Browserressourcen können aus dem eigenen Release bereitgestellt werden.
7. Planvorschläge werden nachvollziehbar berechnet und durch eine berechtigte Person freigegeben. Aufwendige Berechnung läuft mit Zeit-/Ressourcenlimit im Worker.
8. Integrationen sind einzeln abschaltbar und erhalten eine eigene Datenfluss- und Datenschutzprüfung.

## 4. Technologieoptionen

### Backend und Betriebsform

| Option | Passung | Vorteile | Nachteile / Risiken | Bewertung |
|---|---|---|---|---|
| PHP 8.5, modularer Monolith | Gute Passung zum vorhandenen Projektkontext und üblichen Webhosting | breite Verfügbarkeit; einfache Webauslieferung; weniger Betriebsbausteine; PHP 8.5 wird laut PHP-Projekt bis Ende 2029 sicherheitsgepflegt | Framework-/Bibliothekskompatibilität muss geprüft werden; leistungsintensive Aufgaben benötigen Worker; Anbieterlimits beachten | **Empfohlen für Start**, nach CI-/Dependency-Probe |
| Node.js/TypeScript, modularer Monolith | Technisch geeignet, besonders bei stärkerem Echtzeit-/Frontend-Anteil | eine Sprache für Frontend/Backend; gute asynchrone I/O | Hosting- und Prozessbetrieb weniger passend zu einfachem PHP-Webspace; Team muss Runtime/Worker sicher betreiben | Alternative, wenn Team-/Hostingsituation es stützt |
| Python/Django oder FastAPI | Geeignet für Admin-/Datenlogik und mögliche Optimierungsdienste | produktive Webframeworks, gute Daten-/Solverbibliotheken | dauerhafte Prozesse und Deployment können Zusatzbetrieb erfordern; konkrete Hostingfähigkeit zu belegen | mögliche spätere Solverkomponente, zunächst nicht nötig |
| Microservices | Für ersten Release nicht empfohlen | unabhängige Skalierung/Releasezyklen bei großer Organisation | mehr Identitäten, Netzgrenzen, Monitoring, Verträge, Ausfälle und Datenflüsse | unnötige Komplexität vor messbarem Bedarf |

**PHP-Versionsentscheidung:** PHP 8.5 ist am Stichtag ein unterstützter Zweig; PHP-Projekt nennt Sicherheitsupdates bis 31. Dezember 2029. Daher ist 8.5 das Ziel für v0.1.0, sofern das festgelegte Framework und alle Erweiterungen dort unterstützt sind. Minimum- und Upgradepolitik werden im Repository als Laufzeitvoraussetzung dokumentiert. Vor dem Pilot wird die dann aktuelle Sicherheitsversion erneut geprüft.

### Datenbank

| Option | Passung | Wartung / Verfügbarkeit | Bewertung |
|---|---|---|---|
| MySQL 8.4 LTS | Bewährte relationale Basis für konsistente Schul-/Planungsdaten | MySQL dokumentiert LTS als stabilen Zweig mit längerem Support | **Zielstandard** für erste Installation, wenn Hosting/Managed Service die aktuelle Sicherheitswartung nachweist |
| MariaDB 11.4 LTS | Kompatible Alternative, aber nicht in allen Details identisch mit MySQL | MariaDB Foundation führt Wartung bis 29. Mai 2029 | zulässig mit eigener Integrationstest- und Migrationsstrecke |
| MariaDB 10.11 / MySQL 8.0 bei gemeinsamem Webhosting | Kann kurzfristig angeboten werden; Versions-/Supportlage hängt vom genauen Paket ab | STRATO nennt aktuell neu angelegte DBs als MySQL 8 oder MariaDB 10.11; MariaDB 10.11 Community-Pflege endet laut Foundation im Februar 2028 | als Produktivziel für einen neuen Schulbetrieb nicht empfohlen, falls Wartungsfenster vor Ende des geplanten Einsatzes zu kurz ist |
| PostgreSQL | Robuste relationale Alternative | würde eine eigenständige Treiber-, Betriebs- und Hostingprüfung erfordern | nicht Standard in v0.1.0; offen für spätere Neubewertung |

Die Anwendung vermeidet zunächst herstellerspezifische SQL-Funktionen, soweit dies nicht durch eine dokumentierte Entscheidung nötig ist. Dennoch gilt eine Datenbank nicht automatisch als austauschbar: Schema, Transaktionen, Zeichensätze, Collation, Constraints, JSON-Funktionen, Zeitverhalten und Migrationen brauchen Tests für jede offiziell unterstützte Engine.

## 5. Hostingoptionen und Empfehlung

| Modell | Update/Betrieb | Datenschutz-/Sicherheitsprüfung | Passung |
|---|---|---|---|
| Geteiltes Webhosting | einfach, geringe Betriebsarbeit; Plattformlimits können Worker, Cron, Dateispeicher und Beobachtbarkeit einschränken | Anbieter kann deutsche Rechenzentren und AVV anbieten; Paket, Unterauftragnehmer, Rollen, Support und konkrete TOM müssen dennoch geprüft werden | Demo/Entwicklung mit synthetischen Daten möglich; Produktivbetrieb erst nach Nachweis aller Anforderungen, derzeit nicht als Ziel gewählt |
| Verwaltete VM/VPS bei deutschem/europäischem Anbieter | eigener Web-/Datenbank-/Worker-Stack; OS- und App-Updates brauchen Zuständigkeit, Automatisierung und Bereitschaft | bessere technische Steuerbarkeit, aber Kunde/Betreiber trägt mehr Konfigurations- und Patchverantwortung; Vertrag und TOM bleiben zu prüfen | **Zielmodell**, wenn Betrieb durch qualifizierte Stelle gesichert ist |
| Voll verwaltete Anwendungs-/Datenbankplattform | weniger Routinebetrieb und oft integrierte Sicherungen | Unterauftragnehmer, Datenregion, Supportzugriff, Export, Wiederherstellung, Löschung und AVV genau prüfen | gleichwertige Option, wenn konkrete Nachweise und Exit-/Exportweg vorliegen |
| Server der Schule/des Schulträgers | vorhandene Identitäts-/Netzwerkprozesse können genutzt werden | Verantwortlichkeit, Exposition, Patch-/Backupkompetenz, Verfügbarkeit und Support außerhalb der Schule klären | möglich, wenn IT-Träger den Betrieb verbindlich übernimmt |

### Vorläufige Hostingentscheidung

1. Bis ein Verantwortlicher und Betreiber benannt sind, läuft der Prototyp ausschließlich lokal oder in isolierter Testumgebung mit synthetischen Daten.
2. Ein bestehender STRATO-Webspace kann als Demo-/Entwicklungsziel geprüft werden. STRATO dokumentiert PHP 8.2 bis 8.5 im Hostingbereich und MySQL 8 oder MariaDB 10.11 bei neu angelegten Datenbanken. Die FAQ weist außerdem darauf hin, dass Cronjobs auf der neuen Plattform noch nicht verfügbar sind; auf älterer Plattform sind sie best-effort und können sich verzögern. Das passt nicht ohne Zusatznachweis zu zeitkritischen Hintergrundjobs und einem langlebig gepflegten DB-Ziel.
3. Vorläufiges Produktivziel ist ein isolierter, verwalteter Server/eine VM in Deutschland oder EU mit PHP 8.5, MySQL 8.4 LTS oder freigegebener MariaDB-LTS-Version, separatem Worker und privaten Dateien. Das ist eine technische Zielarchitektur, keine Anbieterauswahl.
4. Ein Anbieter wird erst ausgewählt, wenn Verantwortliche und Datenschutz-/IT-Stelle Leistungsbeschreibung, AVV, Unterauftragnehmer, Speicher-/Supportorte, Sicherheitsnachweise, Backup/Restore und Exit/Löschung dokumentiert geprüft haben.
5. Falls kein qualifizierter Betrieb verfügbar ist, wird Produktivbetrieb verschoben; nicht auf Kosten von Updates, Rechteprüfung oder Wiederherstellbarkeit auf ein ungeeignetes Hostingmodell abgesenkt.

## 6. Einzelschule und spätere Mehrschulfähigkeit

**Entscheidung:** v0.1.0 startet als eine isolierte Installation pro Schule bzw. freigegebenem Verantwortlichen. Das fachliche Modell bleibt mehrschulfähig vorbereitet:

- jedes schulbezogene Objekt trägt einen konsistent geprüften Schul-Scope;
- Rollen, Importe, Regelprofile, Datenexporte und Dateipfade werden an diesen Scope gebunden;
- Kennungen sind innerhalb einer Installation eindeutig; keine Annahme, dass eine Klassen-/Personen-ID schulübergreifend eindeutig ist;
- v0.1.0 stellt keine zentrale Mehrschulverwaltung bereit und vermischt keine Schulbestände;
- späterer Mehrschul-/SaaS-Betrieb verlangt eine eigene Architekturprüfung, Mandantentrennungs- und Wiederherstellungstests, Datenschutzrollen, Vertragsmodell und Freigabe;
- Schulspezifische Konfiguration (Regeln, Räume, Rollen, Aufbewahrung) wird nicht als globaler Standard vererbt, solange keine explizite Freigabe vorliegt.

Damit ist spätere Mehrschulfähigkeit ein Designziel, aber kein stillschweigend aktivierter Mehrmandantenbetrieb.

## 7. Datenschutz- und Sicherheitsanforderungen an den Betreiber

Vor Aufnahme echter personenbezogener Daten muss die verantwortliche Stelle mindestens folgende Nachweise führen und freigeben:

- konkrete Verantwortlichkeit und dokumentierte Zwecke/Rechtsgrundlagen pro Verarbeitung;
- passender Vertrag nach Art. 28 DSGVO, soweit eine Auftragsverarbeitung vorliegt, samt Leistungsbeschreibung, TOM und Unterauftragnehmerliste;
- dokumentierte Datenregionen für Primärdaten, Backups, Support, Monitoring und Wiederherstellung sowie Prüfung möglicher Drittlandzugriffe;
- TLS für Übertragung und geeigneter Schutz gespeicherter Daten; Schlüssel- und Geheimnisverwaltung mit begrenzten Administratorrechten;
- getrennte Produktiv-, Test- und Entwicklungsumgebungen und ausschließlich synthetische Tests;
- minimale, zugriffsbeschränkte Betriebslogs; keine Kennwörter, Tokens, Noteninhalte, Anwesenheitsnotizen oder Nachrichtentexte in Logs;
- private Dateispeicherung, serverseitige Rechteprüfung, Uploadprüfung und Lösch-/Backupbehandlung;
- rollenbasierte Administration, MFA für privilegierte Konten soweit verfügbar, zeitlich beschränkter Support-/Notfallzugriff mit Genehmigung und Protokoll;
- Patch-/Schwachstellenverfahren für Betriebssystem, PHP, Framework, Abhängigkeiten und DB;
- Backupverschlüsselung, getrennte Zugangsdaten/-orte, dokumentierte Retention nach freigegebenem Zweck, Restoretests und Verfahren für erneute Löschung nach Restore;
- Vorfall-, Auskunfts-, Berichtigungs-, Einschränkungs-, Export- und Löschprozesse;
- DSFA-Screening der verantwortlichen Stelle und eine abgeschlossene DSFA, falls erforderlich, vor dem Pilot.

Die Anbieterangaben in Quellen zu deutschen Rechenzentren, ISO 27001 oder AVV sind nur Nachweise über einzelne Anbieter-/Produktaspekte. Sie ersetzen weder die Prüfung des konkreten Pakets noch die vollständige datenschutzrechtliche Freigabe.

## 8. Deployment-, Update- und Backupablauf

### Releaseablauf

1. Änderung im Git-Branch; Reviews für Zugriff, Datenmodell und Datenschutzwirkung.
2. Automatisierte Tests und statische Sicherheits-/Abhängigkeitsprüfung mit synthetischen Daten.
3. Release-Artefakt mit gesperrten Abhängigkeiten, Versionskennung und Änderungsnotizen bauen.
4. Testumgebung aktualisieren; Migrations- und Restore-/Rollbackplan prüfen.
5. Verantwortliche geben Änderungen mit Daten-/Berechtigungswirkung frei.
6. Produktionssicherung und Integritätskontrolle nach Betriebsplan.
7. Deployment mit versionierter Migration; danach Smoke-/Rechteprüfungen.
8. Release- und Rückfallentscheidung dokumentieren; Zugangsdaten/Schlüssel bleiben außerhalb des Repositories.

### Backup und Wiederherstellung

- Sicherungen umfassen Datenbank und zugehörige private Dateien in konsistentem Zustand.
- Backupkopien liegen getrennt von der Anwendungsinstanz und sind auf denselben Schutzbedarf ausgelegt.
- RPO (maximal tolerierter Datenverlust) und RTO (Wiederanlaufzeit) werden von Verantwortlichem und Betreiber festgelegt; diese Version erfindet keine Schulvorgabe.
- Wiederherstellung wird regelmäßig in einer isolierten Umgebung geprüft. Dabei wird verhindert, dass gelöschte/gesperrte Daten durch Restore erneut produktiv verfügbar werden.
- Backups sind nicht das Archiv und erhalten keine unbegrenzte Standardaufbewahrung. konkrete Aufbewahrung folgt dem genehmigten Betriebs-/Datenschutzkonzept.

## 9. Kosten- und Betriebsfolgen

Ein konkreter Preisvergleich ist ohne Nutzerzahl, Verfügbarkeit, Speicherbedarf, Supportmodell, Betreiber und Vertragsangebot nicht belastbar und wird hier nicht erfunden.

Die bevorzugte VM-/Managed-Umgebung verursacht mehr Betriebsaufwand als einfaches Webhosting: Betriebssystem- und Datenbankpflege, Monitoring, Backup, Wiederherstellung, Sicherheitsupdates, Bereitschaft und Support müssen einer verantwortlichen Stelle zugeordnet werden. Eine verwaltete Plattform senkt manche Arbeiten, erhöht aber Abhängigkeit vom Anbieter und braucht einen getesteten Datenexport-/Exitweg.

## 10. Entscheidungs- und Freigabestatus

| Entscheidung | Status in v0.0.7 |
|---|---|
| Modularer Monolith statt Microservices für ersten Release | empfohlen/festgelegt für v0.1.0 als Startarchitektur |
| PHP 8.5 | Zielversion, abhängig von Framework-/Paketkompatibilität und Laufzeittest |
| MySQL 8.4 LTS | Zielstandard; MariaDB 11.4 LTS als getestete Alternative |
| Einzelschulinstanz zuerst, schulbezogener Scope überall | vorläufig festgelegt für v0.1.0 |
| Mehrschul-SaaS | nicht freigegeben; spätere eigene Prüfung nötig |
| Konkreter Hoster, Tarif, Datenregion und AVV | offen; Anbieterentscheidung vor Pilot/Produktivdaten |
| Betreiber, Supportzugriff, RPO/RTO, Bereitschaft | offen; mit Verantwortlichem/Träger festlegen |
| Rechtsgrundlagen, Speicherfristen, abgeschlossene DSFA | weiterhin konkrete Freigabe vor Pilot |

## 11. Prüfliste zur Anbieterauswahl

Für jedes Angebot die Nachweise und Geltung für genau das bestellte Produkt festhalten:

- Vertragspartner, Rolle und passende AVV mit Anlagen;
- Datenbank-/PHP-Version, Supportende, Upgradefenster, Erweiterungen und ggf. Worker/Cron;
- primäre und sekundäre Datenregion, Support-/Administrationszugriffe und Unterauftragnehmer;
- Sicherheitsnachweise, TOM, Meldung von Vorfällen, Patch-/Schwachstellenprozess;
- TLS, Verschlüsselungsoptionen, Schlüsselkontrolle, private Dateispeicherung;
- Backupumfang, Ort, Frequenz, Aufbewahrung, Restorezeit, Restorekosten und Testnachweise;
- Logging/Monitoring, Supporteinsicht, Genehmigungs- und Protokollverfahren;
- Ressourcenlimits, Verfügbarkeit, Wartungsfenster und Supportzeiten;
- Datenexport in dokumentiertem, weiterverwendbarem Format, Vertragsende, Rückgabe und bestätigte Löschung;
- Unterauftragnehmerwechsel, Drittlandbezug, Benachrichtigung und Widerspruchs-/Kündigungsmöglichkeiten;
- tatsächliche Kosten bei benötigter Redundanz, Sicherung, Support und Wachstum.

## 12. Quellen und Prüfstand

Offizielle technische/providerbezogene Quellen, abgerufen am 13. Oktober 2026:

1. PHP-Projekt, unterstützte Versionen und Supportzeiträume: [php.net/supported-versions.php](https://www.php.net/supported-versions.php).
2. MySQL, Release-Modell Innovation/LTS und Supportmerkmale: [dev.mysql.com/doc/refman/8.4/en/mysql-releases.html](https://dev.mysql.com/doc/refman/8.4/en/mysql-releases.html).
3. MariaDB Foundation, Wartungszeiträume, u.a. 11.4 LTS bis 29. Mai 2029 und 10.11 bis 16. Februar 2028: [mariadb.org/about](https://mariadb.org/about/).
4. STRATO, verfügbare PHP-Versionen im Hostingbereich: [STRATO FAQ PHP-Einstellungen](https://www.strato.de/faq/hosting/wie-aendere-ich-meine-php-einstellungen-im-hosting-paket/).
5. STRATO, aktuell neu angelegte Datenbanken MySQL 8 oder MariaDB 10.11: [STRATO FAQ Datenbankzugriff](https://www.strato.de/faq/hosting/wie-kann-ich-ueber-phpmyadmin-auf-die-datenbank-zugreifen/).
6. STRATO, Plattformhinweis zu Cronjobs und Best-Effort-Verhalten: [STRATO FAQ Cronjobs](https://www.strato.de/faq/hosting/so-einfach-richten-sie-ihre-cron-jobs-ein/).
7. STRATO, Angaben zu AVV und Rechenzentren: [STRATO FAQ AVV/DSGVO](https://www.strato.de/faq/vertrag/fragen-zur-auftragsverarbeitungsvertrag-avv-und-der-neuen-eu-datenschutzgrundverordnung-dsgvo/) sowie [AVV](https://www.strato.de/agb/avv/).

Anbieterangaben sind produkt- und zeitabhängig. Tarif, Plattformgeneration, Version, AVV-Fassung, Leistungsbeschreibung und Unterauftragnehmer sind vor Bestellung erneut zu prüfen.

## 13. Abnahmekriterien v0.0.7

- Modularer Startarchitekturvorschlag mit begründeter Entscheidung gegen frühe Microservices liegt vor.
- PHP/MySQL und Alternativen sind anhand Hosting, Updateweg, Support und Sicherheit verglichen.
- Eine konkrete Zielkonfiguration mit Versions-/Supportprüfung und Kompatibilitätsbedingungen ist dokumentiert.
- Geteilter Webspace, VM/VPS, verwaltete Plattform und Schul-/Trägerbetrieb sind bewertet; STRATO-Eignungsgrenzen und offene Tarifnachweise sind explizit.
- Einzelschulstart und Voraussetzungen für späteren Mehrschulbetrieb sind entschieden und beschrieben.
- Architekturdiagramm, Daten-/Dateifluss, Jobverarbeitung, Update-, Backup- und Restoreablauf liegen vor.
- Datenschutz-, Betreiber-, AVV-, Datenstandort-, Support- und DSFA-Prüfungen sind als Produktivfreigabegates benannt.
- Kein Hostinganbieter und keine Nutzung echter Schülerdaten werden ohne konkrete Freigabe vorweggenommen.
