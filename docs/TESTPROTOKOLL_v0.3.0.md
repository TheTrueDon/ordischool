# Testprotokoll v0.3.0

## Vorgesehene automatische Prüfungen

- PHP-Syntax für alle PHP-Dateien.
- Migration 004 auf leerer Datenbank und erneuter Lauf des Migrationsbefehls.
- Upgrade v0.2.0 auf v0.3.0 einschließlich Übernahme vorhandener Schularten.
- Neuinstallation führt Migration 004 vor der Kontoanlage aus.
- Mehrere Angebote pro Schule und passende Klassen-/Kurszuordnung.
- Zeitgleiche Belegung durch dieselbe Lehrkraft in unterschiedlichen Schulformen wird erkannt.
- Überlappende Doppelstunden, Klassenkonflikte, fehlende Folge-Stunde und nicht zur Schule gehörende Lehrkräfte werden abgewiesen.
- Zeitraster lehnt sich überschneidende oder in der Periodenfolge rückwärts laufende Unterrichtszeiten ab.
- Freigabe eines konfliktfreien Entwurfs; neue Entwurfsfassung übernimmt Stunden, ohne die freigegebene Fassung zu ändern.
- Freigabe einer Entwurfsfassung wird blockiert, wenn eine zugewiesene Lehrkraft inzwischen deaktiviert wurde.
- Schul-/Rollenprüfung und CSRF-Schutz aller Planänderungen.

## Ausführungsstatus

In der Erstellungsumgebung waren weder PHP noch Docker verfügbar. Die oben aufgeführten CI- und MySQL-Laufzeittests wurden dort nicht ausgeführt. Das Release darf erst getaggt werden, wenn die Repository-CI für MySQL 8.4 und PHP 8.5 erfolgreich ist. Ein phpMyAdmin-Update auf dem Zielserver ist mit synthetischen Testdaten zu erproben.
