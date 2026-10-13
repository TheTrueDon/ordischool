# Bedrohungsmodell – OrdiSchool

**Version:** 0.0.8  
**Stand:** 13. Oktober 2026  
**Status:** Vorläufige Risikoanalyse; bei Architektur- und Funktionsänderungen zu aktualisieren.

## Schutzobjekte

| Schutzobjekt | Warum schützen? | Sicherheitsziel |
|---|---|---|
| Konten, Sitzungen, Rollen und Scopes | bestimmen Identität und Zugriffsreichweite | Vertraulichkeit, Integrität, zeitnaher Rechteentzug |
| Schülerstammdaten und Gruppen | personenbezogene Daten Minderjähriger | minimaler Zugriff, korrekte Zuordnung, kontrollierte Löschung |
| Klassenbuch, Anwesenheit und Notizen | zeigen Teilnahme, Verspätung und schulische Sachverhalte | eng begrenzter Zugriff, nachvollziehbare Korrektur |
| Noten, Radar-Beobachtungen, Zeugnisse | schulische Leistungs-/Entwicklungsdaten | Vertraulichkeit, Integrität und korrekte Freigabe |
| Stunden-, Raum- und Vertretungspläne | steuern Abläufe, Aufsicht und Aufenthaltsorte | korrekte Version und kontrollierte Veröffentlichung |
| Aufgaben, Nachrichten, Gruppenmaterial und Dateien | können private oder sensible Angaben enthalten | Zielgruppenrechte, sichere Speicherung und Übermittlung |
| Datenbank, Dateien, Backups, Exporte und Logs | enthalten Primär- und Kopiedaten | Schutz bei Nutzung, Sicherung, Support, Export und Löschung |
| Regel-/Bewertungsversionen und Freigaben | bestimmen pädagogische/organisatorische Ergebnisse | Herkunft, Gültigkeit, Review, keine stille Rückwirkung |
| Anwendungscode, Abhängigkeiten und Secrets | kontrollieren Datenzugriff | Lieferkettenintegrität und Geheimnisschutz |

## Akteure und Vertrauensgrenzen

| Akteur | Absicht oder Fehlerquelle | Grundannahme |
|---|---|---|
| Externer Angreifer | Konten übernehmen, Schwachstellen ausnutzen, Daten abgreifen oder Dienst stören | zunächst nicht authentifiziert |
| Angemeldeter Nutzer außerhalb seines Scopes | Neugier, Fehler oder absichtlicher Fremdzugriff | Login ist keine Berechtigung für fremde Daten |
| Kompromittiertes Nutzerkonto | Angreifer verwendet gültige Zugangsdaten/Sitzung | Aktionen müssen begrenzt, auffällig und widerrufbar sein |
| Berechtigte Person mit zu weit reichender Rolle | Fehlbedienung, Massenexport oder zweckwidrige Nutzung | Rechte minimieren; sensible Aktionen nachvollziehen |
| Technische Administration/Support | Diagnose eröffnet möglichen Einblick in Inhalte | kein Standardinhaltszugriff; Ausnahme kontrolliert |
| Hosting-/Unterauftragnehmer | Betriebszugriff, Support, Sicherung oder Subdienst | Leistung und Zugriff müssen bekannt sein |
| Fehlerhafter Import/Integration | falscher Empfänger, Zuordnung oder Überschreibung | jede Schnittstelle begrenzen und validieren |
| Versehentliche interne Aktion | falsche Freigabe, Zuordnung, Löschung oder Migration | sichere Defaults, Freigabe und Restoreweg |

**Vertrauensgrenzen:** Browser ↔ Weboberfläche; Anwendung ↔ Datenbank; Anwendung ↔ Dateispeicher; Anwendung ↔ Worker/Queue; Produktion ↔ Admin-/Supportzugriff; Hauptsystem ↔ Backup; Anwendung ↔ externe Schnittstelle. Jede Grenze benötigt Authentifizierung, minimale Rechte, sichere Übertragung, Eingabeprüfung und kontrollierte Fehlerbehandlung.

## Bedrohungs- und Fehlerszenarien

| ID | Szenario | Mögliche Folge | Vorgesehene Gegenmaßnahme | Tests |
|---|---|---|---|---|
| THR-01 | Manipulierte ID umgeht Klassen-/Fachscope | Offenlegung fremder Schüler-, Noten- oder Anwesenheitsdaten | serverseitige Scopeprüfung bei jedem Zugriff | IAM-T01–03 |
| THR-02 | Rechteentzug greift nicht in aktiver Sitzung/Suche | Zugriff bleibt nach Ende der Zuständigkeit bestehen | zeitliche Bindung, erneute Prüfung, Sitzungsentzug | IAM-T04, IAM-T06 |
| THR-03 | Systemadministration kann pädagogische Inhalte lesen | zu breiter interner Zugriff | Betriebs-/Inhaltsrolle trennen; Notfallzugriff genehmigen, befristen, protokollieren | IAM-T05 |
| THR-04 | Schülerkonto sieht fremde Ergebnisse durch fehlerhafte Filterung | Noten-/Zeugnisoffenlegung | Eigenbezug explizit autorisieren; Suche/API/Export gleich prüfen | IAM-T02, EXP-T01 |
| THR-05 | Planungsassistenz veröffentlicht falschen/konfliktbehafteten Vorschlag | fehlerhafter Unterrichtsablauf | Entwurf, Konfliktbegründung und menschliche Freigabe; Version gegen Paralleländerung prüfen | PLAN-T01–07 |
| THR-06 | ungeprüfte oder veraltete Regel wird verbindlich angewandt | Regelverstoß oder irreführende Aussage | Quelle, Gültigkeit, Prüfstatus; verbindliche Nutzung bei Unklarheit sperren | PLAN-T02, PLAN-T04 |
| THR-07 | Upload enthält Schadcode, falschen Typ oder Pfadtraversal | Serverkompromittierung oder Datenabfluss | private Speicherung, Datei-Positivprüfung/Quarantäne nach Betriebsdesign, Downloadkontrolle | FILE-T01–04 |
| THR-08 | Export/Gruppenhinweis enthält Daten außerhalb des Empfängerkreises | unkontrollierte Weitergabe | Empfängerscope prüfen, Benachrichtigung minimieren, Export protokollieren | MSG-T01, EXP-T01–02 |
| THR-09 | Freitext/Log speichert unnötige Gesundheits-/Familiendaten | übermäßige Verarbeitung und Offenlegung | Felder minimieren, Eingabehinweis, Sichtbarkeit begrenzen, Logs filtern | KB-T03, LOG-T01 |
| THR-10 | Löschung betrifft nur Primärdatensatz, nicht Datei/Export/Index | Daten bleiben abrufbar | Lifecycle-Verknüpfung, Date-/Index-/Export-/Backup-Löschpfad | DEL-T01–03 |
| THR-11 | Backup/Restorezugang ungeschützt oder Restore belebt Daten wieder | großflächiger Abfluss oder erneute Verfügbarkeit | Verschlüsselung, getrennte Zugänge/Orte, Zugriffsschutz und Löschabgleich | OPS-T03, DEL-T03 |
| THR-12 | SQLi, XSS, CSRF, SSRF oder unsichere Deserialisierung | Kontoübernahme, Rechteumgehung oder Manipulation | sichere Frameworknutzung, Kodierung, CSRF-Schutz, Eingabevalidierung, ASVS-Prüfung | DAST/manuell, ASVS |
| THR-13 | Secrets in Git, Logs oder Buildartefakt | Übernahme von Systemen | Secret-Management, Scanning, Rotation, getrennte Umgebungen | OPS-T04, Repo-Prüfung |
| THR-14 | verwundbare/manipulierte Abhängigkeit wird ausgeliefert | Codeausführung oder Datenabfluss | gesperrte Versionen, Herkunft, Schwachstellenmonitoring, Update/Review | Dependency-Scan, Release-Gate |
| THR-15 | fehlerhafte Migration/Jobwiederholung dupliziert, löscht oder veröffentlicht Daten | Integritätsverlust oder falscher Plan/Note | transaktionale Migration, Idempotenz, Backup, Rückfallgrenze, Ergebnisvalidierung | OPS-T01–02, OPS-T05–06 |
| THR-16 | Logs sind zu umfangreich oder ungeschützt | Inhaltsabfluss oder unbemerkte Manipulation | Feld-Whitelist, Maskierung, enger Zugriff, Aufbewahrung prüfen | LOG-T01, OPS-T07 |
| THR-17 | fehlende Plattformjobs verursachen Rückstau/verspätete Vertretung | unvollständige oder verspätete Abläufe | Workerfähigkeit prüfen, Jobstatus überwachen, sichere manuelle Alternative | OPS-T05–06 |
| THR-18 | spätere Mandantentrennung ist fehlerhaft | schulübergreifender Datenabfluss | Mehrschulbetrieb deaktiviert lassen; Isolationstests und Freigabe vor Aktivierung | IAM-T08, ARCH-007 |

## Vorläufige Priorisierung

| Priorität | Szenarien | Grund |
|---|---|---|
| P0 – Releaseblocker | THR-01–04, THR-07–11, THR-13, THR-15; THR-18 bei Mehrschulbetrieb | direkte Daten-/Rechteverletzung, Datenrest oder systemweite Auswirkung |
| P1 – vor Pilot beheben/prüfen | THR-05, THR-06, THR-12, THR-14, THR-16, THR-17 | fehlerhafte Schulentscheidung, verbreitete Webrisiken, Betriebs-/Lieferkettenrisiko |
| P2 – im Moduldesign beobachten | übrige und nicht anwendbare Kontrollen | Umfang/Exposition kann variieren; Begründung und Frist dokumentieren |

Die Einstufung ist eine erste technische Einschätzung. Die verantwortliche Stelle und Security Review bewerten den Produktivkontext neu. Sie ersetzt keine DSFA.

## Annahmen und blinde Flecken

- Nutzerzahlen, Hostingkonfiguration, Schnittstellen und Supportmodell sind unbekannt.
- Es wurde kein Penetrationstest und kein Test mit echten Nutzenden durchgeführt.
- Rechtsgrundlage und zulässiger Umfang einzelner Datenfelder werden hier nicht bestimmt.
- Radarspinne, Elternkonten, externe E-Mail/Push, KI und Mehrschulbetrieb sind nicht freigegeben.
- Das Modell wird nach neuen Datenflüssen, Architekturänderungen, Vorfällen und vor Pilot erneut geprüft.
