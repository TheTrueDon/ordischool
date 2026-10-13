# Testprotokoll v0.4.0

## Statische Prüfungen

- PHP-Syntaxprüfung und MySQL-/Browserlauf sind in der aktuellen Arbeitsumgebung nicht verfügbar, da weder PHP noch Docker installiert sind.
- SQL-Migration und manuelles Update-Skript werden auf identische Schemaoperationen geprüft; ZIP-Integrität und Shell-Syntax werden vor Auslieferung geprüft.
- Die CI-Testfälle wurden um Raumkollision, Raumzuordnung, Kapazität und Kopieren der Raumzuordnung ergänzt; ein Lauf gegen MySQL muss in der Projekt-CI erfolgen.

## Restprüfung vor Einsatz

Migration zuerst auf einer Kopie der bestehenden v0.3.0-Testdatenbank ausführen und die CI mit PHP 8.5/MySQL 8.4 laufen lassen. Keine Echtdaten für diese Entwicklungsfassung verwenden.
