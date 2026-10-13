# Implementierungsstand v0.2.0

**Status:** Entwicklungsrelease; PHP-/MySQL-Laufzeittests sind in dieser Umgebung nicht ausgeführt.

## Umgesetzt

- Datenbankschema für Schule, Schuljahr, Schulmitgliedschaft, schulweite Rollen und Auditprotokoll.
- Upgrade-Migration von v0.1.2 sowie phpMyAdmin-SQL-Skript mit dem Standardpräfix ordi_.
- Entfernung des bisherigen Rollen-Checks ist versionsabhängig dynamisch; Spalte, Indexe und neue Tabellen sind gegen einen erneuten Lauf nach einem frühen Teilabbruch abgesichert.
- Installer und CLI prüfen die username-Spalte vorab direkt über INFORMATION_SCHEMA und überspringen den optionalen Add-Column-Block, wenn ein vorheriger Versuch ihn bereits angelegt hat.
- Webinstaller führt bei Neuinstallationen alle Migrationen durch und legt das technische Systemadministrationskonto mit eindeutigem Benutzernamen an.
- Systemadministration legt Schulen an und kann Konten mit einer Rolle an einer Schule verbinden.
- Schuladministration verwaltet Schuljahre und Mitglieder ausschließlich für die eigene Schule. Sie kann keine weitere Schuladministration vergeben.
- Mitgliedschaften können deaktiviert und reaktiviert werden; wiederholte Statusänderung ist idempotent.
- Anfragen zu Verwaltungsseiten prüfen aktive Sitzung, aktive Mitgliedschaft, Zeiträume, Schulbezug und Rolle auf dem Server.
- Konto-/Mitgliedschafts- und Stammdatenaktionen schreiben Audit-Einträge. Der Eintrag enthält Schule, handelnden Account, optional betroffenen Account, Ereignis, Rolle und UTC-Zeitpunkt; keine IP-Adresse und keine Klartextpasswörter.
- Systemadministration wird weiterhin nicht als pädagogische Rolle verwendet und bekommt keine Fachdatenrechte.

## Daten und Datenschutz

v0.2.0 speichert Loginname, interne Login-Adresse unter ordischool.invalid, Passwort-Hash, Aktivstatus, Schulzuordnung, schulweite Rolle, Zeiträume und Auditdaten. Es werden keine Schülerprofile oder Fachinhalte angelegt. Login-Adresse und Account-IDs bleiben personenbezogene Daten; eine interne/reservierte Adresse macht sie nicht anonym.

Die Migration setzt den Benutzernamen bestehender v0.1.2-Konten auf den lokalen Teil der bisherigen Login-Adresse, begrenzt auf 64 ASCII-Zeichen. Für den v0.1.2-Installer waren diese Werte bereits auf 64 Zeichen und einen Login-Namen beschränkt. Bei individuell veränderten Altinstallationen vorab Datenbank sichern und auf doppelte oder ungültige Benutzernamen prüfen.

Die Anwendung enthält keine fertig geprüften Fristen für Audit- oder Kontodaten und keinen Betroffenen-Export, Löschworkflow oder Passwortwechsel. Diese Punkte müssen fachlich, technisch und rechtlich für den konkreten Betrieb entschieden werden. Es wird keine DSGVO-Konformität oder Zertifizierung zugesichert. Bis zu einer Prüfung durch Schule/Träger, Datenschutzbeauftragte und Betrieb nur synthetische Testdaten verwenden.

## Grenzen

- Rollen gelten in dieser Version schulweit. Klassen-, Kurs-, Fach- und Funktionsscopes folgen mit den jeweiligen Fachmodulen.
- Ein Account kann im aktuellen ersten Verwaltungsfluss nur bei einer Schule erstellt werden; späteres schulübergreifendes Kontenmanagement ist noch nicht enthalten.
- Schulart-Auswahl ist derzeit ein kontrolliertes Stammdatenfeld. Sie aktiviert keine Thüringer Fach- oder Stundenplanregeln.
- Ein Passwortwechsel durch Nutzer, Einladung, E-Mail-Versand, Mehrfaktor-Login und Rollenänderung für bestehende Konten sind noch nicht enthalten.
- Das Auditprotokoll ist nicht manipulationsgeschützt und kein vollständiges Sicherheits-/Aufbewahrungskonzept.

## Updatehinweis

Vor dem Dateiaustausch vollständiges Datenbank- und Dateibackup anlegen. Bei Standardpräfix ordi_ das Skript sql/update_v0.1.2_to_v0.2.0_ordi.sql in phpMyAdmin auf der richtigen Datenbank ausführen. Bei eigenem Präfix jedes ordi_ im Skript ersetzen. Nach erfolgreicher Migration die Projektdateien überschreiben und config.local.php beibehalten. DDL in MySQL ist nicht transaktional; nach einem Abbruch keine blinde Wiederholung ausführen, sondern Schema und schema_migrations kontrollieren.
