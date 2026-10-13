# Feedbackprotokoll und offene Usability-Fragen

**Version:** 0.0.5  
**Status:** Fragen für die erste fachliche Durchsicht; noch keine Nutzerbeobachtung oder Usability-Testergebnisse behauptet.  
**Prototyp:** [`prototype/index.html`](../prototype/index.html)

## Feedbackprotokoll

| ID | Datum | Rolle / Aufgabe | Beobachtung oder Rückmeldung | Auswirkung / Entscheidung | Status |
|---|---|---|---|---|---|
| FB-001 | — | — | Noch keine Rückmeldung erfasst. | — | offen |

Neue Rückmeldungen sollen ohne Schülernamen oder andere echte personenbezogene Daten dokumentiert werden. Wenn Feedback aus einer tatsächlichen Schule stammt, nur abstrahierte Beobachtungen mit notwendigem Kontext aufnehmen.

## Kernaufgaben für die fachliche Durchsicht

1. **Planung:** Findet die Planungskoordination den Konflikt, seine Ursache und den Unterschied zwischen harter Regel, lokaler Datenlücke und weicher Präferenz?
2. **Vorschlag:** Ist klar, welche Änderung vorgeschlagen wird, welche Stunden/Räume betroffen sind und was die Assistenz nicht selbst entscheidet?
3. **Freigabe:** Ist die Trennung zwischen Entwurf, Vorlage zur Freigabe und Veröffentlichung verständlich?
4. **Vertretung:** Sind Fachbezug, Verfügbarkeit und Status so angezeigt, dass der Vorschlag geprüft werden kann, ohne Abwesenheitsgründe offenzulegen?
5. **Eigener Stundenplan:** Ist nachvollziehbar, welche Änderungen erst nach Freigabe in der persönlichen Ansicht erscheinen?
6. **Klassenbuchhinweis:** Verstehen Lehrkräfte, warum der Hinweis erscheint, wie man ihn prüfen kann und dass kein Eintrag automatisch geändert wird?
7. **Aufgaben:** Sind Verantwortliche, Zielgruppe, Fach und Termin auf einen Blick auffindbar?
8. **Räume:** Ist erkennbar, dass Kapazitätswerte und Fachraumdaten einen lokalen Nachweis benötigen?

## Offene Interaktionsfragen

| ID | Frage | Betroffene Rolle / Ansicht | Folge für die Umsetzung |
|---|---|---|---|
| UX-001 | Soll die Stundenplanbearbeitung zuerst auf Klassen, Lehrkräfte, Räume oder einzelne Unterrichtsgruppen ausgerichtet sein? | Planungskoordination | bestimmt Startansicht und Filterlogik |
| UX-002 | Soll die Wochenmatrix auf Desktop als Arbeitsfläche bleiben und auf Mobilgeräten als Tagesliste erscheinen? | Planung und Lehrkräfte | bestimmt responsive Planinteraktion |
| UX-003 | Welche Konflikte brauchen eine harte Sperre und welche nur einen erklärten Warnhinweis? | Planungskoordination / Schulleitung | beeinflusst Regeln und Freigabeprozess |
| UX-004 | Welche Mindestbegründung soll ein Mensch bei einer Freigabe, Ablehnung oder Abweichung angeben? | Freigaberolle | bestimmt Nachvollziehbarkeit und Protokollfelder |
| UX-005 | Wie häufig sollen Klassenbuchhinweise angezeigt werden, ohne Lehrkräfte mit Hinweisen zu überlasten? | Lehrkräfte / Klassenleitungen | bestimmt Gruppierung, Priorität und Benachrichtigung |
| UX-006 | Welche Kennzahlen gehören auf das Dashboard pro Rolle, und welche sollten aus Datenschutz-/Aufmerksamkeitsgründen nicht erscheinen? | alle Rollen | bestimmt rollenbezogene Dashboard-Kacheln |
| UX-007 | Soll die Vertretungsliste einzelne Stunden, Tagesfälle oder einen ganzen Planänderungsvorgang gruppieren? | Vertretungskoordination | bestimmt Bearbeitungsfolge und Freigaben |
| UX-008 | Welche Planänderungen benötigen eine sofortige Mitteilung und welche erscheinen nur im persönlichen Stundenplan? | Lehrkräfte / Lernende | bestimmt Benachrichtigungs- und Aktualisierungsregeln |

## Beobachtungsbogen für einen späteren Test

Pro Aufgabe festhalten:

- Rolle und Aufgabe (ohne echte personenbezogene Falldaten);
- ob die Person die Aufgabe ohne Erklärung abschließt;
- wo sie zögert, zurücknavigiert oder eine Rückfrage stellt;
- ob sie Status, Zielgruppe, Konflikt, Freigabe und Planwirkung richtig versteht;
- Schweregrad (Blocker, erhebliches Missverständnis, kleinere Reibung, Wunsch);
- konkreter Vorschlag und Entscheidung (übernehmen, später prüfen, ablehnen);
- Prototyp-/Feedbackversion und Datum.

## Abnahmekriterien v0.0.5

- Vertretungsverantwortliche können an einer Planänderung den Konflikt, den Vorschlag und die menschliche Freigabe nachvollziehen.
- Lehrkräfte können den persönlichen Stundenplan, eine Klassenbuchwarnung und eine Aufgabe im Prototyp finden und deren Status verstehen.
- Räumliche Konflikte, Kapazitätsdaten und unverbindliche Vorschläge sind als Demo bzw. nicht bestätigte Werte erkennbar.
- Der Prototyp läuft lokal aus einer einzelnen HTML-Datei und sendet oder speichert keine Daten außerhalb des Browser-Arbeitsspeichers.
- Feedbackfragen und Protokollstruktur sind vorhanden; nicht durchgeführte Tests werden als offen markiert.
