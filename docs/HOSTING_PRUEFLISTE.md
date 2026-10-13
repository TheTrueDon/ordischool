# Hosting-Prüfliste – OrdiSchool

**Zugehörig zu:** v0.0.7  
**Status:** Vorlage zur Prüfung eines konkreten Tarifs/Anbieters; nicht ausgefüllt und keine Anbieterfreigabe.

Vor einer Bestellung mit der verantwortlichen Stelle und IT-/Datenschutzverantwortlichen anhand der aktuellen Vertrags- und Produktunterlagen ausfüllen. Jede Anbieterantwort soll auf ein konkretes Produkt/Tarif und eine aktuelle Vertragsfassung verweisen.

| Prüffeld | Nachweis / Antwort | Ergebnis / offene Frage |
|---|---|---|
| Anbieter, Produkt, Tarif, Plattformgeneration |  |  |
| Verantwortliche Stelle und Betreiber |  |  |
| Rolle des Anbieters; AVV nach Art. 28, falls einschlägig |  |  |
| Leistungsbeschreibung/AVV-Fassung und Anlagen |  |  |
| Hauptdatenregion; Speicherort von Backups/Logs |  |  |
| Support-/Adminzugriffe und mögliche Drittlandzugriffe |  |  |
| Unterauftragnehmer und Änderungsbenachrichtigung |  |  |
| PHP-Version, Wartungsstatus, Extensions, Upgradeweg |  |  |
| SQL-Engine und exakte Version; Wartungsende |  |  |
| Worker, Cron, Zeitlimits, Queue, Prozesslimits |  |  |
| TLS, gespeicherte Verschlüsselung und Schlüsselkontrolle |  |  |
| Private Dateispeicherung außerhalb des öffentlichen Webroots |  |  |
| Rollen, MFA, Supportzugriff, Notfallzugriff und Protokoll |  |  |
| Sicherheitsupdates, Schwachstellen- und Vorfallprozess |  |  |
| Backup: Umfang, Takt, getrennte Region, Verschlüsselung, Aufbewahrung |  |  |
| Restore: Test, Zeit, Umfang und Kosten |  |  |
| RPO / RTO und zugesicherte Verfügbarkeit |  |  |
| Löschung aus Primärsystemen, Dateien und Backups |  |  |
| Export, Rückgabe und Exit bei Vertragsende |  |  |
| Ressourcen-/Upload-/Datenbanklimits und Skalierungsweg |  |  |
| Supportzeiten, Reaktionszeit und Eskalation |  |  |
| Vollkosten mit Sicherung, Support und Wachstum |  |  |
| Datenschutzprüfung/VVT/DSFA-Gate abgeschlossen |  |  |
| Entscheidung und Freigabe durch zuständige Stelle, Datum |  |  |

## Ausschlusskriterien für Produktivdaten

Kein Produktivbetrieb, solange eines der folgenden Kriterien zutrifft:

- Anbieterrolle, Vertrag oder Unterauftragnehmerlage ist ungeklärt.
- Datenregionen oder Supportzugriff sind nicht nachvollziehbar.
- Benötigte PHP-/Datenbankversion erhält keine zugesicherte Sicherheitswartung.
- Nichtöffentlicher Dateispeicher, aktuelle Rechteprüfung oder sichere Zugangsdaten sind nicht möglich.
- Backup und Wiederherstellung wurden nicht nachweisbar getestet.
- Erforderliche Aufgabenjobs, Updates oder Monitoring funktionieren nur über unsichere/manuelle Workarounds.
- Export, Vertragsende oder Löschung sind nicht geregelt.
- Verantwortliche Datenschutz-/Betriebsfreigabe und gegebenenfalls erforderliche DSFA fehlen.

Die Liste ist ein technischer Beschaffungs- und Freigabebehelf, kein vollständiges VVT, keine abgeschlossene DSFA und keine Rechtsberatung.
