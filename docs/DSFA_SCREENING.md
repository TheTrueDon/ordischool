# Vorläufiges DSFA-Screening

**Version:** 0.0.4  
**Status:** Vorprüfung, keine Datenschutz-Folgenabschätzung und keine rechtliche Entscheidung. Verantwortliche Stelle und Datenschutzbeauftragte müssen die Bewertung für den konkreten Einsatz vornehmen.

## 1. Prüfmaßstab

Nach Art. 35 DSGVO ist eine Datenschutz-Folgenabschätzung (DSFA) vor der Verarbeitung durchzuführen, wenn eine geplante Verarbeitung voraussichtlich ein hohes Risiko für Rechte und Freiheiten natürlicher Personen zur Folge hat. Die DSGVO nennt unter anderem bestimmte umfangreiche Verarbeitungen sensibler Daten und systematische umfangreiche Bewertungskonstellationen. Ein einzelnes Softwaremerkmal beantwortet die Frage nicht; maßgeblich sind die tatsächlichen Zwecke, Daten, Personen, Reichweite, Verknüpfungen und Schutzmaßnahmen.

## 2. Vorläufige Risikofaktoren des Produkts

| Faktor | Im OrdiSchool-Zielbild | Vorläufige Bedeutung |
|---|---|---|
| Schutzbedürftige Betroffene | überwiegend Kinder/Jugendliche und pädagogisches Personal | erhöhte Folgen bei Offenlegung oder falschem Zugriff; Begrenzung und klare Informationen erforderlich |
| Verknüpfte Datenbereiche | Planung, Anwesenheit, Aufgaben, Kommunikation, Noten, Radar-Bewertungen und Zeugnisse | Zusammenführung kann detaillierte schulische Profile entstehen lassen; Zweckgrenzen technisch und organisatorisch trennen |
| Daten mit möglicher besonderer Kategorie | Angaben zu Gesundheit/Behinderung könnten in Stammdaten, Notizen oder Förderkontext vorkommen | nicht als freie Standarderfassung vorsehen; nur mit eigener Rechts-/Zugriffsprüfung |
| Leistungsbewertung und Vorschläge | algorithmische Notenberechnung und spätere Planungsassistenz | Rechenschritte offenlegen; keine automatisierte verbindliche Entscheidung; Auswirkungen auf Betroffene prüfen |
| Viele Nutzer und Empfänger | Schule, Klassen, Gruppen und ggf. Eltern/Sorgeberechtigte | Fehlkonfiguration kann Daten breit offenlegen; Rollen, Scopes, Anhänge und Benachrichtigungen gesondert testen |
| Externe Dienstleister und Standort | Hosting, Dateien, Mail/Push, Support und Backups noch nicht festgelegt | Datenflüsse, Zugriffsorte, Unterauftragsverarbeiter und Transferkonstellationen vor Auswahl bewerten |
| Dauer und Aufbewahrung | schulbezogene Daten können über Schuljahre und Übergänge betroffen sein | Frist pro Datensatzklasse und Lösch-/Einschränkungsweg festlegen; keine pauschale Produktfrist |
| Neue Technik/Automatisierung | integrierte Planungsassistenz, Radar und verknüpfte Dashboards | Kriterien, Eingabedaten und menschliche Prüfung dokumentieren; Auswirkungen der Nutzung testen |

## 3. Vorläufige Einschätzung

Das Vorhaben enthält mehrere Faktoren, die eine vertiefte Risikoprüfung nahelegen: Minderjährige, Daten aus mehreren schulischen Lebensbereichen, Leistungs- und Anwesenheitsdaten, mögliche sensible Freitexteinträge und noch offene externe Betriebsstellen. Deshalb wird eine vollständige DSFA **vor Pilot und Produktivbetrieb als Projektfreigabepunkt eingeplant**.

Diese Planung behauptet nicht, dass Art. 35 DSGVO zwingend in jedem Betriebsmodell automatisch erfüllt ist oder dass eine DSFA die einzige rechtliche Möglichkeit wäre. Die verantwortliche Stelle und ihre Datenschutzbeauftragten müssen anhand der konkreten Verarbeitung, einschlägiger Aufsichtsbehördenlisten und der finalen Architektur die rechtliche Erforderlichkeit feststellen und dokumentieren. Bis dahin bleibt der Status `DSFA_SCREENING_OFFEN` und es werden keine echten Schülerdaten verwendet.

## 4. Screening-Fragen vor technischer Umsetzung

- Wer bestimmt Zwecke und wesentliche Mittel je Betriebsform (öffentliche Schule, Schulträger, privater Träger, zentraler Dienst)?
- Welche konkrete Aufgabe und Rechtsgrundlage besteht für jedes Modul und Datenfeld? Sind zusätzliche Funktionen durch geltende Schulregeln gedeckt?
- Welche Daten nach § 136 ThürSchulO bzw. einer späteren gültigen Rechtsverordnung dürfen erfasst werden? Welche Angaben sind optional oder zu vermeiden?
- Gibt es Gesundheits-, Förder-, Religions- oder sonstige Daten besonderer Kategorie? Welche sind wirklich erforderlich und wer erhält sie?
- Werden Noten, Anwesenheit, Radar-Dimensionen oder Planungsdaten über Zeit und Module zusammengeführt?
- Welche Folgen können Fehler, falsche Zuordnungen, automatisierte Vorschläge oder unbefugte Offenlegung für Lernende/Lehrkräfte haben?
- Welche Daten verlassen den Verantwortungsbereich (Hosting, Mail, Support, KI, Backups)? Werden Drittländer oder weitere Unterauftragnehmer einbezogen?
- Welche betroffenen Personen werden nach Art. 13/14 informiert, wann, durch wen und in welcher verständlichen Form?
- Wie können Auskunft, Berichtigung, Einschränkung, Widerspruch, Löschung, Export und Beschwerden bearbeitet werden, ohne gesetzliche Aufbewahrungspflichten zu verletzen?
- Welche Schutzmaßnahmen sind belegt und wie werden sie getestet (Berechtigungen, Verschlüsselung, Protokolle, Wiederherstellung, Vorfallverfahren)?
- Bleibt nach Maßnahmen ein hohes Restrisiko, das eine vorherige Konsultation der Aufsichtsbehörde erfordert?

## 5. Freigabeschritte

1. Vollständiges Dateninventar und reale Datenflüsse erstellen; alle leeren Felder der verantwortlichen Stelle füllen.
2. Datenschutzbeauftragte und ggf. IT-/Schulträgerstellen frühzeitig beteiligen.
3. Anwendbare Thüringer Schuldatenschutzregeln und aktuelle Aufsichtsbehördenlisten prüfen; Entwürfe/FAQs nicht als Gesetz behandeln.
4. DSFA-Erforderlichkeit begründen. Falls erforderlich: Verarbeitung beschreiben, Notwendigkeit/Verhältnismäßigkeit, Risiken und Maßnahmen bewerten.
5. Maßnahmen umsetzen und testen; Restrisiken und Verantwortliche dokumentieren.
6. Bei verbleibendem hohem Risiko vor Beginn die Voraussetzungen für vorherige Konsultation nach Art. 36 DSGVO prüfen.
7. Freigabe, Version, Umfang, offenen Rest und Wiederprüftermin dokumentieren; bei wesentlichen Änderungen erneut bewerten.

## 6. Nicht Teil dieser Vorprüfung

- Keine konkrete Rechtsgrundlagenprüfung für eine Schule.
- Keine Festlegung der Verantwortlichen- oder Auftragsverarbeiterrolle.
- Keine Aussage zu einer bereits erfüllten DSGVO-Konformität oder Zertifizierung.
- Keine Bewertung eines bestimmten Hosters, KI-Anbieters oder Vertrags.
- Keine endgültige Löschfrist oder Genehmigung für Radar-Bewertung, Elternkonten oder Freitextfelder.
