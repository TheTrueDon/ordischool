# Implementierungsstand v0.6.0

## Enthalten

- Der Webinstaller erstellt weiterhin das technische Systemadministrationskonto. Beim Anlegen einer Schule wird dieses Konto zusätzlich als schulweite Schuladministration zugeordnet und erhält sämtliche katalogisierten Schulberechtigungen.
- Das Anlegen einer Schule richtet ein Hauptgebäude, mehrere angebotene Schulformen/Bildungsgänge sowie Grundrollen ein.
- Verwaltungsbereich mit Einrichtungscheckliste: Gebäude/Räume, Fächer, Personal/Rollen, Schuljahr, Klassen und Schüler.
- Raum- und Fachverwaltung bleibt im selten benötigten Bereich Planungsgrundlagen; Planer müssen diese Stammdaten nicht bei jeder Planänderung bearbeiten.
- Personenkonten erfassen Vor- und Nachnamen. Einer Person lassen sich mehrere Rollen schulweit oder für Schulformen zuordnen.
- Rollenberechtigungen können in einer Checkbox-Tabelle angepasst und um schulindividuelle Rollen erweitert werden.
- Klassen/Kurse können erst nach Anlage eines Schuljahres eingerichtet werden. Klassen und Kurse verlangen eine Klassenleitung und Stellvertretung aus dem aktiven Lehrkräftebestand.
- Schüler können ohne Benutzerkonto mit minimalen Namensdaten angelegt und optional einer Klasse zugeordnet werden.
- Stundenplanentwürfe werden erst angeboten, wenn Gebäude, Räume, Fächer, Lehrkräfte und mindestens eine Klasse vorhanden sind.
- Migration `007_school_setup_and_permissions.sql`; phpMyAdmin-Update `sql/update_v0.5.0_to_v0.6.0_ordi.sql`.

## Datenschutz und Grenzen

Erfasst werden bei Personal Kontoname, Vor- und Nachname, Schulzuordnung und Rollen. Bei Schülern werden in dieser Version Vorname, Nachname und optionale Klasse erfasst. Geburtsdaten, Anschriften und weitere nicht benötigte Schülerdaten werden nicht erhoben. Rollenänderungen, Konten und Stammdaten werden über bestehende Audit-Ereignisse protokolliert. Vor Einsatz mit realen Daten bleiben Rechtsgrundlage, Verantwortlichkeit, Aufbewahrung/Löschung, Hosting, Auftragsverarbeitung und gegebenenfalls DSFA zu klären. Die Version ist keine pauschale DSGVO-Konformitäts- oder Produktivfreigabe.

## Prüfung

PHP, MySQL und Docker waren in der Arbeitsumgebung nicht verfügbar. Deshalb sind PHP-Laufzeittests und die Ausführung der Migration gegen MySQL/MariaDB hier nicht bestätigt. Vor einem Release-Tag müssen Syntax-, Migrations-, Berechtigungs- und Restoretests in CI erfolgreich sein. Bis dahin ausschließlich synthetische Testdaten verwenden.
