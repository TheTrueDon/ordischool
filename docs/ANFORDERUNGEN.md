# OrdiSchool Anforderungen

**Status:** Zielanforderungen, fachlich noch weiter zu spezifizieren  
**Version:** 0.0.5
**Normative Wörter:** „MUSS“ bezeichnet eine Abnahmeanforderung. „SOLL“ bezeichnet ein Ziel, dessen Abweichung begründet wird. „OFFEN“ bezeichnet eine noch nicht fachlich beschlossene Ausgestaltung.

## A. Planung

| ID | Anforderung | Zielversion |
|---|---|---:|
| PLAN-001 | Stundenpläne müssen erstellt, bearbeitet, kopiert, zeitlich befristet, versioniert und freigegeben werden können. | 0.3.0 |
| PLAN-002 | Unterrichtsstunden müssen Klasse/Kurs, Fach, Lehrkraft, Zeit, Raum und optionale Doppelstunden/Teilungsgruppen verknüpfen. | 0.3.0 |
| PLAN-003 | Raumplanung muss Kapazität, Ausstattung, Fachraumtyp, Verfügbarkeit, Sperrzeiten und Prioritäten berücksichtigen. | 0.4.0 |
| PLAN-004 | Planprüfung muss Mehrfachbelegungen, fehlende Räume, Kapazitätsüberschreitungen und weitere bekannte Konflikte anzeigen. | 0.3.0–0.5.0 |
| PLAN-005 | Raum-, Stunden-, Vertretungs- und Terminplan müssen auf gemeinsame Planstände und Stammdaten zugreifen. | 0.3.0–0.10.0 |
| PLAN-006 | Bindende Regeln, schulinterne Regeln und weiche Optimierungswünsche müssen unterscheidbar sein. | 0.5.0 |
| PLAN-007 | Regelprofile müssen Bundesland, Schulform, Gültigkeit, Quelle, Versionsstand und Prüfstatus speichern. | 0.5.0 |
| PLAN-008 | Thüringer Vorgaben sind zuerst zu erfassen; konkrete Regeln müssen vor Nutzung anhand aktueller offizieller Quellen fachlich geprüft und freigegeben werden. | 0.0.3 / 0.5.0 |
| PLAN-009 | Änderungsvorschläge müssen Begründung, erfüllte/verletzte Bedingungen und Folgen für betroffene Pläne zeigen. | 0.6.0 |
| PLAN-010 | Kein kritischer Planvorschlag darf ohne Freigabe einer berechtigten Person veröffentlicht werden. | 0.6.0 |
| PLAN-011 | Vertretungsvorschläge SOLLEN Abwesenheit, Verfügbarkeit, Fachbezug, Belastung, Raum und Folgewirkungen berücksichtigen, soweit die Daten freigegeben und definiert sind. | 0.6.0 |
| PLAN-012 | Jedes aktive Regelprofil MUSS Quelle, Fundstelle, Fassung, Gültigkeitszeitraum, Schulart, Bildungsgang, Jahrgang und Prüfstatus ausweisen. | ab 0.5.0 |
| PLAN-013 | Die Auswahl einer Rahmenstundentafel MUSS Übergangsbestimmungen, Kohorte und Schuljahr berücksichtigen; bei unklarer Zuordnung darf keine verbindliche automatische Prüfung erfolgen. | 0.5.0 |
| PLAN-014 | Harte Vorgaben, schulische Beschlüsse, lokal nachgewiesene Gebäude-/Raumrestriktionen und weiche Planungspräferenzen MÜSSEN getrennt behandelt und angezeigt werden. | 0.5.0 |
| PLAN-015 | Raumkapazität und verbindliche Ausstattung MÜSSEN eine lokale Quelle, prüfende Stelle und Gültigkeit besitzen; ohne gültigen Nachweis darf die Planprüfung keine konfliktfreie Belegung behaupten. | 0.4.0–0.5.0 |
| PLAN-016 | Jede Regeländerung MUSS erkennen lassen, welche Planversionen und Schuljahre betroffen sind; eine neue Regel darf veröffentlichte Pläne nicht stillschweigend umschreiben. | ab 0.5.0 |

## B. Sitzpläne und Klassenbuch

| ID | Anforderung | Zielversion |
|---|---|---:|
| SITZ-001 | Sitzpläne müssen pro Klasse, Kurs oder Raum erstellt, geändert, dupliziert und zeitlich eingeordnet werden können. | 0.7.0 |
| SITZ-002 | Sichtbarkeit, Ausdruck und Export von Sitzplänen müssen berechtigungs- und zweckbezogen sein. | 0.7.0 |
| KB-001 | Ein Klassenbucheintrag muss Datum, Zeit, Stunde, Fachlehrkraft, Vertretungsstatus, Klasse, Doppelstunde und Stundenthema abbilden. | 0.8.0 |
| KB-002 | Anwesenheit muss mindestens „anwesend“, „abwesend“, „entschuldigt“ und „verspätet“ mit Verspätungsminuten und einer berechtigungsgesteuerten Notiz unterstützen. | 0.8.0 |
| KB-003 | Hausaufgaben müssen im Klassenbucheintrag erfasst werden können. | 0.8.0 |
| KB-004 | Das Klassenbuch muss Einträge mit Stunden- und Vertretungsplan abgleichen und fehlende oder abweichende Einträge erkennen. | 0.8.0 |
| KB-005 | Klassenleitungen müssen eine nachvollziehbare Übersicht offener Klassenbuchhinweise erhalten können. | 0.8.0 |
| KB-006 | Hinweise dürfen Einträge nicht automatisch ändern oder löschen; Korrekturen müssen Bearbeiter, Zeitpunkt und Änderungsgrund nachvollziehbar halten. | 0.8.0 |

## C. Aufgaben, Termine und Zusammenarbeit

| ID | Anforderung | Zielversion |
|---|---|---:|
| AUF-001 | Aufgaben müssen mit einem Klassenbucheintrag verknüpft oder unabhängig angelegt werden können. | 0.9.0 |
| AUF-002 | Aufgaben müssen Text, Fach, Verantwortliche, betroffene Klasse/Gruppe oder Lernende, Terminierung und Anhänge unterstützen. | 0.9.0 |
| AUF-003 | Dateianhänge müssen privat gespeichert und bei jedem Zugriff gegen die Berechtigungen des Inhalts geprüft werden. | 0.9.0 |
| GRP-001 | Gruppenräume müssen Blogbeiträge und Materialien mit gruppenbezogener Berechtigungssteuerung bereitstellen. | 0.11.0 |
| WIKI-001 | Wikis müssen versionierte, durchsuchbare Inhalte mit abgestuften Lese- und Bearbeitungsrechten ermöglichen. | 0.11.0 |
| MSG-001 | Das Nachrichtensystem muss Einzel- und Gruppennachrichten samt Anhängen unterstützen. | 0.11.0 |
| MSG-002 | Berechtigungen müssen definieren, welche Gruppe welche Gruppe/Rolle anschreiben darf. | 0.11.0 |
| MSG-003 | Gruppen- und Leitungsmitteilungen müssen zielgruppengerecht veröffentlicht und von privaten Nachrichten unterscheidbar sein. | 0.11.0 |
| TERM-001 | Ein Terminplan muss eigene, Gruppen- und Leitungstermine sowie Sichtbarkeit, Verantwortliche und Änderungen abbilden. | 0.10.0 |
| TERM-002 | Termine müssen mit Stundenplänen verbunden werden können; Konflikte sind vor Freigabe anzuzeigen. | 0.10.0 |
| TERM-003 | Persönliche Stundenpläne müssen freigegebene Vertretungen, Raumänderungen und relevante Terminnotizen übernehmen. | 0.10.0 |
| DASH-001 | Ein Dashboard muss je Rolle anstehende Aufgaben, Planänderungen, Klassenbuchhinweise, Mitteilungen und Termine anzeigen. | 0.14.0 |

## D. Bewertung und Zeugnisse

| ID | Anforderung | Zielversion |
|---|---|---:|
| NOTE-001 | Das Notensystem muss mehrere auswählbare und fachlich freigegebene Berechnungsverfahren unterstützen. | 0.12.0 |
| NOTE-002 | Berechnungsverfahren müssen Notenarten, Gewichtungen, Rundung, fehlende Leistungen, Halbjahres- und Endnoten sowie Gültigkeitszeitraum abbilden. | 0.12.0 |
| NOTE-003 | Jede Berechnung muss Eingaben, Rechenschritte, Gewichte, Rundung und Verfahrensversion nachvollziehbar darstellen. | 0.12.0 |
| NOTE-004 | Änderungen eines Verfahrens dürfen abgeschlossene Ergebnisse nicht stillschweigend rückwirkend verändern. | 0.12.0 |
| NOTE-005 | Notenänderungen und Freigaben müssen rollenbasiert und nachvollziehbar sein. | 0.12.0 |
| RADAR-001 | Notenfreie Bewertung muss fachbezogene Dimensionen für eine Radarspinne unterstützen. | 0.13.0 |
| RADAR-002 | Dimensionen, Skalen, Beobachtung, Interpretation, Sichtbarkeit und Verwendung im Zeugnis bleiben bis zu einer gesonderten Spezifikation OFFEN. | 0.13.0 |
| ZG-001 | Zeugnisse müssen mit freigegebenen Vorlagen, Datenfeldern, Korrekturläufen, Freigaben und PDF-Ausgabe erzeugt werden können. | 0.14.0 |
| ZG-002 | Zeugnisvorlagen müssen für die jeweilige Schulform und den konkreten Einsatz fachlich/rechtlich geprüft werden, bevor sie produktiv freigegeben werden. | 0.14.0 / 1.0.0 |

## E. Rollen, Freigaben und Arbeitsabläufe

| ID | Anforderung | Zielversion |
|---|---|---:|
| IAM-001 | Rollen müssen auf Schule, Klasse, Kurs, Fach, Funktion und Gültigkeitszeitraum begrenzt werden können; ein Rollentitel allein begründet keinen globalen Zugriff. | 0.1.0–0.2.0 |
| IAM-002 | Erstellen, Bearbeiten, Prüfen und Veröffentlichen müssen für fachlich relevante Vorgänge unterscheidbar und pro Zuständigkeit zuweisbar sein. | 0.2.0 |
| IAM-003 | Berechtigungsprüfungen müssen bei Ansichten, Suchen, Direktlinks, Anhängen, Benachrichtigungen, Exporten und APIs greifen. | ab 0.1.0 |
| IAM-004 | Technische Systemadministration darf standardmäßig keinen pädagogischen Lesezugriff auf Schüler-, Noten-, Anwesenheits- oder Nachrichteninhalte erhalten. | ab 0.1.0 |
| IAM-005 | Freigaben, Rollenänderungen, Exporte, Korrekturen und administrative Notfallzugriffe müssen nachvollziehbar protokolliert und im Umfang begrenzt werden. | ab 0.1.0 |
| IAM-006 | Entzug oder Änderung einer Zuständigkeit muss für aktive Sitzungen und alle Zugriffspfade wirksam werden. | ab 0.1.0 |
| FLOW-001 | Planungs- und Vertretungsvorschläge dürfen Pläne nicht ohne Prüfung und Freigabe einer berechtigten Person veröffentlichen. | 0.3.0–0.6.0 |
| FLOW-002 | Klassenbuchhinweise müssen prüfbar sein, dürfen aber keinen Eintrag automatisch ändern oder löschen. | 0.8.0 |
| FLOW-003 | Sichtbarkeit und Empfänger einer Aufgabe, Mitteilung oder eines Termins müssen vor Veröffentlichung erkennbar sein. | 0.9.0–0.11.0 |
| FLOW-004 | Bewertungsverfahren müssen fachlich freigegeben und versioniert sein; Änderungen dürfen abgeschlossene Ergebnisse nicht still rückwirkend verändern. | 0.12.0–0.13.0 |
| FLOW-005 | Zeugnisfreigabe und Ausgabe müssen rollenbezogen, nachvollziehbar und an die für Schule/Schulform bestätigte Vorlage gebunden sein. | 0.14.0 |

## F. Datenschutz, Sicherheit und Betrieb

| ID | Anforderung | Zielversion |
|---|---|---:|
| PRIV-001 | Für jeden Datenbereich müssen Zweck, verantwortliche Stelle, Datenarten, Empfänger, Zugriff und Lösch-/Aufbewahrungsregel dokumentiert werden. | ab 0.0.4 |
| PRIV-002 | Entwicklung und Tests müssen synthetische Daten verwenden; Produktivdaten dürfen nicht in lokale Entwicklungsumgebungen kopiert werden. | alle Versionen |
| PRIV-003 | Die Software darf Rechtsgrundlage, Einwilligung oder Aufbewahrungsdauer nicht allein aus einer Funktion ableiten. | alle Versionen |
| PRIV-004 | Vor Pilot/Produktion müssen Verantwortlichkeit, Rechtsgrundlagen, Datenschutzinformationen, Auftragsverarbeitung, Hosting und gegebenenfalls Folgenabschätzung geprüft werden. | 0.0.4 / 0.16.0 |
| PRIV-005 | Betroffenenanfragen, Berichtigung, Export, Einschränkung und Löschung müssen durch die verantwortliche Stelle im anwendbaren Rahmen bearbeitbar und nachvollziehbar sein. | 0.15.0–1.0.0 |
| PRIV-006 | Für jede Verarbeitung personenbezogener Daten müssen Datenarten, Zweck, Betroffene, Empfänger, Geltungsbereich, Speicherklassen und verantwortliche Freigabe dokumentiert werden. | ab 0.0.4 |
| PRIV-007 | Rechtsgrundlagen und Informationspflichten müssen je konkretem Verantwortlichen, Zweck und Datenbereich freigegeben werden; sie dürfen nicht als Produktstandard unterstellt werden. | ab 0.0.4 |
| PRIV-008 | Aufbewahrungs-, Einschränkungs-, Lösch- und Archivierungsregeln müssen pro Datensatzklasse festgelegt und auf Exporte, Anhänge, Sicherungen und Unterauftragsverarbeiter abgestimmt sein. | 0.0.4 / ab 0.1.0 |
| PRIV-009 | Externe Empfänger und Anbieter müssen einschließlich Rolle, Weisung, Unterauftragsverarbeitern, Zugriffsorten, Drittlandbezug, Rückgabe und Löschung dokumentiert und vor Aktivierung freigegeben sein. | vor Pilot |
| PRIV-010 | Vor Pilot muss ein dokumentiertes DSFA-Screening durch die verantwortliche Stelle erfolgen; erforderliche DSFA und ggf. vorherige Konsultation müssen vor Beginn abgeschlossen sein. | 0.0.4 / 0.16.0 |
| PRIV-011 | Informations-, Auskunfts-, Berichtigungs-, Einschränkungs-, Widerspruchs-, Export- und Löschvorgänge müssen durch berechtigte Verantwortliche bearbeitbar sein und Spezial-Aufbewahrungsregeln berücksichtigen. | 0.0.4 / 0.15.0–1.0.0 |
| PRIV-012 | Daten besonderer Kategorien oder zusätzliche Freitextangaben dürfen nicht ohne bestätigte Erforderlichkeit, Rechtsgrundlage, Zweck, Zugriff und Aufbewahrungsregel als allgemeine Standardfelder aktiviert werden. | ab 0.0.4 |
| PRIV-013 | Externe KI-, Analyse- oder Benachrichtigungsdienste erhalten standardmäßig keine Noten-, Anwesenheits-, Schülernachrichten- oder sonstigen pädagogischen Inhaltsdaten. | alle Versionen |
| PRIV-014 | Produktiv- und Nichtproduktivumgebungen müssen getrennte Zugänge, Speicher und Protokolle verwenden; Produktivdaten dürfen nicht in Testumgebungen kopiert werden. | ab 0.1.0 |
| UX-001 | Vor der Implementierung müssen die Kernabläufe für Planänderung, Konfliktprüfung, Vertretung, persönlichen Stundenplan, Klassenbuchhinweise und Aufgaben anhand eines klickbaren Prototyps nachvollziehbar geprüft werden. | 0.0.5 |
| UX-002 | Der Prototyp muss klar als Demo mit synthetischen Daten und nicht freigegebenen Regel-/Raumwerten gekennzeichnet sein und darf keine personenbezogenen Daten außerhalb der lokalen Ansicht speichern oder übertragen. | 0.0.5 |
| UX-003 | Offene Usability-Fragen und später erhobenes Feedback müssen versioniert protokolliert werden; nicht durchgeführte Tests dürfen nicht als Ergebnisse dargestellt werden. | 0.0.5 |
| SEC-001 | Rollen und Gruppenrechte müssen bei Anzeige, Suche, Direktlinks, Anhängen, Benachrichtigungen und Export erneut geprüft werden. | ab 0.1.0 |
| SEC-002 | Zugangsdaten und Schlüssel dürfen nicht im Repository gespeichert werden. | alle Versionen |
| SEC-003 | Sicherheits- und Adminereignisse müssen protokolliert werden; Passwörter, Sitzungstokens und unnötige Schülernotizen dürfen nicht in Logs erscheinen. | ab 0.1.0 |
| SEC-004 | Dateien müssen auf Typ/Größe geprüft, privat gespeichert und kontrolliert gelöscht werden; ausführbare Uploads sind auszuschließen. | 0.9.0 |
| SEC-005 | Backups, Restore, Updates, Abhängigkeiten, Vorfallverfahren und Rollback müssen vor Produktivbetrieb dokumentiert und geprüft sein. | 0.1.0–1.0.0 |
| SEC-006 | Externe KI-Dienste dürfen nicht standardmäßig Schüler-, Noten-, Anwesenheits- oder Nachrichtendaten erhalten. | alle Versionen |
| OPS-001 | Datenbankänderungen müssen versioniert migrierbar und mit geprüfter Sicherungs-/Wiederherstellungsstrategie verbunden sein. | ab 0.1.0 |
| OPS-002 | Hosting und Unterauftragnehmer müssen vor Auswahl hinsichtlich Standort, Zugriffen, Backups, Löschung, Sicherheit und Vertragsbedingungen geprüft werden. | 0.0.7 / 0.16.0 |

## Nicht-Ziele von v0.0.5

- Keine produktive Anwendungscode- oder Datenbankimplementierung.
- Keine Verwendung echter Schüler-, Noten-, Anwesenheits- oder Nachrichtendaten.
- Keine produktiv freigegebenen oder vollständig maschinenlesbaren Thüringer Regelprofile.
- Keine endgültige Festlegung von Hosting, Stack, Rollen oder Mehrschulbetrieb.
- Keine finalen Notenalgorithmen, Radarspinnen-Dimensionen oder Zeugnisvorlagen.


## G. Fachliches Datenmodell

| ID | Anforderung | Zielversion |
|---|---|---:|
| DATA-001 | Zentrale Stammdaten für Schule, Schuljahr, Personen, Klassen/Lerngruppen, Fach, Lehrkräfte, Räume und Zeitraster werden referenziert; Parallelbestände sind zu vermeiden. | 0.0.6 |
| DATA-002 | Zeitlich gültige Mitgliedschaften, Zuständigkeiten und Rollenbindungen bilden Scope und Zeitraum ab. | 0.0.6 / 0.2.0 |
| DATA-003 | Planposition, Unterrichtsereignis, Vertretung und Klassenbuchnachweis sind unterscheidbare verknüpfte Objekte. | 0.0.6 |
| DATA-004 | Veröffentlichte Plan-, Regel- und Bewertungsverfahrensstände sind mit Version, Gültigkeit, Herkunft, Status und Freigabe abbildbar. | 0.0.6 |
| DATA-005 | Änderungen an Regeln/Verfahren verändern veröffentlichte Pläne und freigegebene Ergebnisse nicht still rückwirkend. | ab 0.0.6 |
| DATA-006 | Personenbezogene Objektgruppen sind Zweck, Zugriffsscope, Empfängern und Lifecycle-Entscheid zuordenbar. | ab 0.0.6 |
| DATA-007 | Beziehungen/Exporte ersetzen keine Berechtigungsprüfung; derselbe Scope gilt an allen Zugriffspfaden. | ab 0.0.6 |
| DATA-008 | Anhänge sind einem Elternobjekt zugeordnet und privat; Zugriff/Löschung berücksichtigen dessen Berechtigungen. | 0.0.6 / 0.9.0 |
| DATA-009 | Schemaänderungen sind versioniert migrierbar und dokumentieren Voraussetzungen, Integritätsprüfung, Sicherung und Rückfallgrenzen. | ab 0.1.0 |
| DATA-010 | Lösch-/Einschränkungsprozesse berücksichtigen Primärdaten, Anhänge, Exporte, Suchindizes und Sicherungen; Fristen je Klasse bestätigen. | ab 0.0.6 |
| DATA-011 | Externe Kennungen sind von internen IDs getrennt; Personenmerkmale werden nicht in IDs kodiert. | ab 0.0.6 |
| DATA-012 | Regeln und lokale Raumrestriktionen haben Herkunft, Gültigkeit, Prüfstatus und erforderliche Freigabe. | ab 0.0.6 |
