# Testprotokoll v0.5.0

## Lokale Prüfung

PHP und Docker standen in der Erstellungsumgebung nicht zur Verfügung. Daher wurden keine PHP-Syntax-, MySQL-Migrations- oder Browserläufe ausgeführt. Das Release darf erst nach grüner CI als technisch geprüft gelten.

## CI-Prüffälle

- Migration 006 auf leerer Datenbank und Update von v0.4.1; erneuter Lauf des SQL-Updates ohne doppelte Spalten-/Tabellenfehler.
- Planer kann Planfassungen bearbeiten, aber Stammdaten- und Regelprofil-POSTs erhalten 403.
- Schuladministration kann Settings der eigenen Schule verwalten; keine Scope-Überschreitung zu fremder Schule.
- Profilversionen pro Schuljahr/Schulform steigen; Quellenstatus `draft` wird erst nach expliziter Prüfung `reviewed`.
- Prüfbericht zeigt harte Konflikte, weiche Raumpräferenzhinweise und fehlende/veraltete Profilstände in getrennten Abschnitten.
- Erstellung oder Prüfung einer neuen Profilversion markiert gespeicherte Prüfstände als erneut zu prüfen.
- Freigabe blockiert bei fehlendem/überholtem Prüfstand und funktioniert erst nach erneutem Prüfen jeder im Plan verwendeten Schulform.
- Systemadministration ohne Schulrolle erhält weiterhin keinen schulischen Zugriff.
