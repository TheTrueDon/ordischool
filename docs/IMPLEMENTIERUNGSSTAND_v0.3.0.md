# Implementierungsstand v0.3.0

**Status:** Entwicklungsrelease für manuelle Stundenplanung. Laufzeittests konnten in der Erstellungsumgebung nicht ausgeführt werden.

## Enthalten

- Eine Schule kann mehrere Schulform-/Bildungsgangangebote führen. Die Schulverwaltung bietet Mehrfachauswahl statt einer einzelnen maßgeblichen Schulart.
- Die Migration übernimmt bestehende `schools.school_type`-Werte als erstes Schulformangebot; das Legacy-Feld bleibt vorerst erhalten.
- Schuljahrspezifische Klassen/Kurse verweisen auf ihr Angebot. Fächer und Stundenraster sind schulweit.
- Ein Stundenplan pro Schule und Schuljahr umfasst sämtliche Schulformen in einem gemeinsamen Zeitraster.
- Planungsberechtigte können Stundenplanentwürfe anlegen, Stunden und Doppelstunden pflegen und Entwürfe manuell freigeben.
- Ein veröffentlichter Plan bleibt unverändert. Änderungen können in eine neue, kopierte Entwurfsfassung einfließen; die vorige Fassung bleibt als ersetzt erhalten.
- Klasse und Lehrkraft dürfen im gemeinsamen Wochenplan nicht zeitgleich doppelt belegt werden. Die Lehrkraftprüfung arbeitet über alle Angebote/Schulformen derselben Schule hinweg.
- Vor Freigabe werden zusätzlich aktive Schulform-, Klassen-, Fach-, Lehrkraft- und Stundenrasterzuordnungen geprüft.
- Planungsvorgänge prüfen CSRF, Rolle und Schule, validieren Eingaben serverseitig und schreiben begrenzte Audit-Ereignisse ohne Stundeninhalt.

## Datenbankänderung

`migrations/004_timetable_core.sql` ergänzt Schulformangebote, Klassen/Kurse, Fächer, Unterrichtszeiten, Planfassungen und Planpositionen. Für vorhandene Installationen liegt `sql/update_v0.2.0_to_v0.3.0_ordi.sql` vor. Vor Updates vollständiges Backup erstellen. Das Skript ist mit dem tatsächlich installierten Präfix auszuführen.

## Abgrenzungen

- Keine Räume, Raumkapazitäten oder Fachraumprioritäten; geplant für v0.4.0.
- Keine automatisch angewendeten Thüringer Regeln; geplant für v0.5.0 nach aktueller Quellen- und Fachprüfung.
- Keine automatische Planoptimierung oder Vertretungsassistenz; geplant für v0.6.0.
- Keine Klassen-/Kurs- oder Fachscope-Rechte; bestehende schulweite Rollen begrenzen v0.3.0 weiterhin auf Schulebene.
- Kein Nachweis einer DSGVO-Konformität, abgeschlossenen DSFA oder Produktivfreigabe.
