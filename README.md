# OrdiSchool

**Ordinatio Scholae – Manuelle Stundenplanung v0.4.1**

v0.4.1 enthält einen Kontoverwaltungs-Fix und baut auf der gemeinsamen, schulformübergreifenden Stundenplanung aus v0.3.0 auf. Räume, Kapazitäten und Fachraumwünsche lassen sich ergänzen. Eine Schule kann mehrere Schulformen oder Bildungsgänge anbieten. Klassen und Kurse werden ihrem Angebot und Schuljahr zugeordnet. Stundenpläne lassen sich als Entwurf pflegen, auf Zeitkonflikte prüfen und manuell freigeben.

## Update einer vorhandenen v0.3.0- oder v0.4.0-Installation

1. Sichere zuerst die vollständige Datenbank und die Anwendungsdateien. MySQL-DDL ist nicht transaktional.
2. Für v0.3.0: Wähle in phpMyAdmin die richtige Datenbank und führe `sql/update_v0.3.0_to_v0.4.0_ordi.sql` aus. Für v0.4.0 auf v0.4.1 ist kein SQL-Update nötig. Bei einem anderen Tabellenpräfix musst du im v0.3.0-Update alle Vorkommen von `ordi_` durch dein Präfix ersetzen; es ergänzt Raumverwaltung, Kapazitäten und Fachraumpräferenzen.
3. Prüfe nach einem Update von v0.3.0, dass `ordi_schema_migrations` den Eintrag `005_room_planning.sql` enthält.
4. Ersetze anschließend die Programmdateien durch diese Version. Behalte `config.local.php` und andere installationsspezifische Dateien bei.
5. Melde dich an. Bei v0.4.0-Installationen bleibt das Datenbankschema unverändert.

Das Update ergänzt die Raumstammdaten und verknüpft optionale Raumzuweisungen mit Stundenplaneinträgen. Bereits vorhandene Fächer erhalten zunächst keine Fachraumpräferenz; diese kann in der Planungsverwaltung gepflegt werden.

Bei einem Fehler nach begonnenem SQL-Update nicht blind erneut starten. Sichere den Zustand und prüfe Schema sowie Migrationsmarker. Das Skript ist für Wiederholung nach Teilfortschritt ausgelegt, die Wiederherstellung muss aber anhand des tatsächlichen Zustands erfolgen.

## Neuinstallation

1. Projektordner auf den Webserver kopieren und `public` als Dokumentenstamm konfigurieren. `app`, `migrations`, `bin`, `docs`, `sql` und `config.local.php` müssen außerhalb des öffentlichen Webpfads liegen.
2. Eine leere MySQL-Datenbank und einen Datenbankbenutzer mit Tabellenrechten bereitstellen.
3. `install.php` öffnen und DB-Host, DB-Name, DB-Benutzer, Passwort, Tabellenpräfix (Standard: `ordi_`) sowie Systemkonto angeben.
4. Nach der Installation mit dem angelegten Systemkonto anmelden. Das Systemkonto hat keine pädagogischen Datenrechte.

Der PHP-Prozess braucht beim Installieren Schreibrechte auf den Projektordner, um `config.local.php` anzulegen. Diese Datei enthält Zugangsdaten und darf weder öffentlich erreichbar noch in Git eingecheckt werden. HTTP ist nur für einen ausdrücklich bestätigten Test mit separater Datenbank und ausschließlich synthetischen Daten vorgesehen. Für produktiven Betrieb ist HTTPS erforderlich.

## Stundenplanung ausprobieren

1. Öffne **Schulverwaltung** und lege eine Schule an. Wähle dort alle angebotenen Schulformen/Bildungsgänge aus; Mehrfachauswahl ist möglich.
2. Lege mindestens ein Schuljahr an.
3. Öffne **Stundenplanung** und ergänze bei Bedarf weitere Schulformangebote, Klassen/Kurse, Fächer sowie das Stundenraster.
4. Lege einen Stundenplanentwurf an und trage Unterrichtsstunden ein. Lehrkräfte müssen als aktive Konten mit der Rolle **Lehrkraft** an der Schule geführt sein.
5. Die Anwendung verhindert zeitgleiche Einsätze derselben Lehrkraft über alle Schulformen hinweg sowie Doppelbelegungen einer Klasse. Für Doppelstunden muss auch die Folge-Stunde im Zeitraster existieren.
6. Die Planungsrolle pflegt den Entwurf; eine aktive Schuladministration muss ihn bewusst freigeben. Änderungen erfolgen über eine neue Entwurfsfassung, die aus dem freigegebenen Stand kopiert werden kann.

Eine freigegebene Fassung kann nicht direkt verändert werden. Die Freigabe ersetzt die vorherige Veröffentlichung für dasselbe Schuljahr; der vorherige Plan bleibt als ersetzte Fassung erhalten.

## Umfang und Grenzen v0.4.1

- Mehrere Schulform-/Bildungsgangangebote je Schule; Klassen/Kurse hängen an einem Angebot und Schuljahr.
- Schulweite Fächer, Stundenraster und Lehrkraftkonten als Planungsgrundlagen.
- Planentwürfe, versionierte Fassungen, Einzel- und Doppelstunden, ein gemeinsames schulweites Zeitraster, Konfliktprüfung und manuelle Freigabe.
- Eine Lehrkraft wird über Schulformen hinweg über dasselbe Benutzerkonto zugeordnet.
- Räume haben frei definierbare Typen und optionale Kapazitäten. Bekannte Kapazitätsüberschreitungen und Doppelbelegungen blockieren; Fachraumpräferenzen erzeugen nur Hinweise.
- Thüringer Regelprofile und ihre geprüfte Anwendung kommen in v0.5.0.
- Vertretungsplanung und Assistenzvorschläge kommen in v0.6.0.
- Sitzpläne, Klassenbuch, Noten und weitere Module sind noch nicht enthalten.

## Datenschutz und Einsatzgrenzen

Diese Entwicklungsfassung ist keine pauschale DSGVO-Konformitäts- oder Produktivfreigabe. Verwende ausschließlich synthetische Daten. Stundenplan-Zuordnungen können personenbezogene Beschäftigtendaten enthalten. Für einen realen Betrieb müssen die zuständigen Stellen insbesondere Verantwortlichkeit, Zweck und Rechtsgrundlage, Berechtigungen, Hosting, Auftragsverarbeitung, Datenlöschung, Backups und ein mögliches DSFA-Erfordernis prüfen und freigeben. Die Schulleitung behält die fachliche Freigabehoheit; das System veröffentlicht nicht automatisch.

## Tests

Die CI führt PHP-Syntaxprüfungen, Migrationen auf MySQL 8.4, Datenbanktests, Authentifizierungs-/Berechtigungsprüfungen und Web-/Restore-Smoke-Tests aus. Neue Tests decken Mehrfachangebote je Schule, schulformübergreifende Lehrkraftkonflikte, Raumkollisionen, Kapazitätsgrenzen, weiche Fachraumpräferenzen und versionierte Raumzuweisungen ab. PHP und Docker waren in der Erstellungsumgebung nicht verfügbar; diese Laufzeittests konnten hier nicht ausgeführt werden. Vor einem Release-Tag muss die CI erfolgreich durchlaufen.

## Release-Dateien

- [Versionsplan](docs/VERSIONSPLAN.md)
- [Implementierungsstand v0.4.1](docs/IMPLEMENTIERUNGSSTAND_v0.4.0.md)
- [SQL-Update von v0.3.0 für `ordi_`](sql/update_v0.3.0_to_v0.4.0_ordi.sql) (nur erforderlich, wenn noch v0.3.0 installiert ist)
- [Commit-Vorschlag](docs/COMMIT_MESSAGE_v0.4.1.txt)
- [Änderungsprotokoll](CHANGELOG.md)
