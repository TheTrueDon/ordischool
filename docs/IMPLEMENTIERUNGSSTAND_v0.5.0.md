# Implementierungsstand v0.5.0

## Enthalten

- Getrennter Bereich `/admin/planning/settings` für Planungsgrundlagen; Stammdaten-POSTs verlangen eine aktive Schuladministrationsrolle im jeweiligen Schul-Scope.
- Die tägliche Stundenplanansicht enthält keine Pflegeformulare mehr und zeigt stattdessen einen Link für Schuladministratoren beziehungsweise einen Hinweis für Planer.
- Versionierte Quellenprofile mit Schuljahr, Angebot, Kohorte, Quelle, Fundstelle, Prüfdatum, Entwurfs-/Prüfstatus und verantwortlicher Person.
- Prüfstand pro Stundenplan und verwendeter Schulform. Nach Anlegen oder fachlicher Prüfung einer neuen Profilfassung müssen betroffene Planstände erneut geprüft werden.
- Veröffentlichung erfordert einen gespeicherten aktuellen Prüfstand für alle im Plan belegten Schulformen.
- Bericht unterscheidet technische Konflikte, weiche Fachraumhinweise und offene/überholte Regelprofile.

## Fachliche Grenze

Die Thüringer Regeln sind noch nicht als vollständige ausführbare Regelbibliothek umgesetzt. Das Formular und der Bericht helfen, Quellen nach Schulform, Schuljahr und Kohorte nachzuweisen. Nicht abgebildete Stundentafeln, Übergangskohorten und befristete Verwaltungsvorgaben müssen fachlich manuell geprüft werden. Die Anwendung erzeugt keine Aussage „rechtskonform“.

## Sicherheit und Datenschutz

- Stammdaten- und Profilverwaltung benötigt `school_admin` für genau die angefragte Schule; technische Systemadministration erhält keine impliziten schulischen Datenrechte.
- Schreibvorgänge verwenden CSRF-Schutz, validierte Scope-IDs und Audit-Ereignisse.
- Profile enthalten keine Schülerdaten. Audit-Nachweise sollen nur die für die Nachvollziehbarkeit benötigten Konten-/Zeitdaten umfassen.
- Keine DSGVO-Konformitätszusage. Rechtsgrundlage, Verantwortlicher, Hosting, Fristen, AVV, DSFA und betriebliche Freigabe bleiben offen.

## Migration und Teststand

- `migrations/006_planning_rule_profiles.sql` wird bei Neuinstallation automatisch ausgeführt.
- `sql/update_v0.4.1_to_v0.5.0_ordi.sql` ist für phpMyAdmin vorhanden und bei abweichendem Präfix anzupassen.
- CI soll Migration, Rollenabgrenzung, Regelprofil-Änderung/Prüfmarker, Konfliktausgabe und Veröffentlichungsblockade testen.
- In dieser Arbeitsumgebung waren PHP und Docker nicht installiert/verfügbar; keine Laufzeittests wurden als bestanden markiert.
