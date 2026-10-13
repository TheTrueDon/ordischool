# Thüringen: Planungsregeln und fachliche Bedingungen

**Version:** 0.0.3  
**Prüfstand:** 13. Oktober 2026  
**Geltungsbereich:** erster fachlicher Regelprofil-Entwurf für allgemeinbildende Schulen; besonders Gymnasium und Regelschule.  
**Status:** Quellen recherchiert, fachliche Einzelprüfung für einen konkreten Schulbetrieb noch erforderlich. Dieses Dokument ist keine Rechtsauskunft.

## 1. Zweck und Geltungslogik

OrdiSchool soll Thüringer Vorgaben bei der Stunden- und Vertretungsplanung berücksichtigen, ohne schulinterne Praxis als Landesrecht auszugeben. Dazu werden Regeln nach ihrer Quelle und Bindung getrennt verwaltet:

| Regelklasse | Bedeutung im System | Beispiel | Behandlung durch die Planung |
|---|---|---|---|
| `LANDESRECHT` | Geltende gesetzliche oder verordnungsrechtliche Anforderung | Rahmenstundentafel und Festsetzung des Stundenplans durch Schulleitung | Harte Prüfung, sobald Schulform, Jahrgang, Kohorte und Gültigkeit eindeutig bestimmt sind |
| `LANDESVORGABE_ZEITRAUM` | Zeitlich befristete Verwaltungsvorschrift bzw. Schuljahresregel | VV zur Organisation eines konkreten Schuljahres | Nur innerhalb des Gültigkeitszeitraums anwenden; Version und Quelle speichern |
| `SCHULBESCHLUSS` | Durch zuständiges schulisches Gremium beschlossene Ausgestaltung | Ergänzungsstunden, Wahlpflicht-/Wahlfächer und Arbeitsgemeinschaften im Rahmen der Zuständigkeit | Nur nach dokumentiertem Beschluss und Gültigkeitszeitraum als verbindliche lokale Regel aktivieren |
| `SCHULORGANISATION` | Von der Schule verantwortete Organisation und Zuordnung | Zeitraster, Teilungsgruppen, verfügbare Lehrkräfte, Planverantwortliche | Konfigurierbar; Zuständigkeit und Freigabe dokumentieren |
| `GEBÄUDE_SICHERHEIT` | Nachweisbare Raum-/Gebäudebedingung, die durch zuständige Stelle festgelegt wird | zulässige Raumbelegung, Ausstattung, Zugänglichkeit, Sperrzeit | Raumdaten mit Quelle/Nachweis pflegen; keine landesweite Kapazität erfinden |
| `PRÄFERENZ` | Optimierungswunsch ohne zwingende Bindung | Fachraum möglichst für Fach X reservieren, freie Randstunden reduzieren | Als weiches Ziel gewichten; Vorschlag begründen und nicht als Rechtsverstoß anzeigen |

Ein Regelprofil ist kein einzelner globaler Schalter. Es wird mindestens für **Bundesland, Schulart/Schulform, Bildungsgang, Klassenstufe bzw. Kurs, Kohorte, Schuljahr und Gültigkeitszeitraum** bestimmt. Wo diese Merkmale fehlen oder eine Übergangsregel nicht geklärt ist, darf die Software keine scheinbar verbindliche automatische Entscheidung treffen.

## 2. Aus den geprüften Quellen abgeleitete Planungsbedingungen

| Regel-ID | Quelle / Fundstelle | Fachliche Aussage für OrdiSchool | Art / Status |
|---|---|---|---|
| TH-PLAN-001 | ThürSchulO § 44 Abs. 1, Anlagen 1–12a | Der Unterricht richtet sich nach der einschlägigen Rahmenstundentafel. Die Planung muss für die jeweilige Schulart und Jahrgang/Kohorte die dort ausgewiesenen Stundenumfänge abbilden und prüfen. | Landesrecht; Regelstruktur bestätigt, Tabellenwerte je Profil noch zu erfassen |
| TH-PLAN-002 | ThürSchulO § 44 Abs. 1 | Die schulinterne Stundentafel muss eine Planung des Lehrens und Lernens ausweisen; Projekte und Epochen können den entsprechenden Fachstundenzahlen angerechnet werden. | Landesrecht; Datenmodell muss block-/epochale Planung und nachvollziehbare Stundenanrechnung zulassen |
| TH-PLAN-003 | ThürSchulO § 44 Abs. 2 | Unterrichtsinhalte richten sich nach den vom zuständigen Ministerium vorgegebenen Lehrplänen; Bildungsplan und Bildungsstandards sind zu beachten. | Landesrecht; Lehrplanreferenzen nach Fach/Schulart/Jahrgang mit Versionsstand verwalten |
| TH-PLAN-004 | ThürSchulO § 44 Abs. 3 | Den Stundenplan setzt die Schulleitung fest. Bei bestimmten Kooperationen stimmen die beteiligten Schulleitungen die Pläne ab. | Zuständigkeit; OrdiSchool kann vorbereiten und prüfen, darf die gesetzliche Zuständigkeit nicht durch automatische Veröffentlichung ersetzen |
| TH-PLAN-005 | ThürSchulO § 45 Abs. 2 | In der Regelschule gibt es ab Klassenstufe 7 in Deutsch, Mathematik und Englisch Kursdifferenzierung; ab Klassenstufe 9 zusätzlich in einem naturwissenschaftlichen Fach nach der für die jeweilige Fassung geltenden Regel. | Landesrecht; Teilungs-/Kursgruppen im Planmodell erforderlich; genaue Fach-/Jahrgangsregel ist versionsabhängig |
| TH-PLAN-006 | ThürSchulO § 45 Abs. 3 | Unterricht kann fach-, klassen-, jahrgangs- und zeitweise kursübergreifend organisiert werden. Für die genannten Angebote entscheidet die Lehrerkonferenz über Grundsätze der schulinternen Stundentafel nach Anhörung der Schulkonferenz. | Landesrecht + Schulbeschluss; Gruppenzusammenführung ist möglich, Angebotsentscheidungen benötigen dokumentierte Zuständigkeit |
| TH-PLAN-007 | ThürSchulO § 44 Abs. 1 und § 155; amtliche Änderungs-FAQ | Rahmenstundentafeln ab 1. August 2024 werden schrittweise eingeführt; Übergangsbestimmungen gelten für bestehende Sek-I-Kohorten. Im Schuljahr 2026/27 darf nicht allein aus „Schulart + Klassenstufe“ eine Tabelle gewählt werden, ohne Kohorte/Übergangsregel zu prüfen. | Übergangsrecht; blockiert automatische Tabellenwahl ohne Kohortenprofil |
| TH-PLAN-008 | ThürSchulO § 149 und amtliche Änderungsunterlagen | Für die integrierte Gesamtschule gelten schulartspezifische Differenzierungsregeln; für das Fach Physik und Astronomie ist der Beginn ab Schuljahr 2026/27 zu berücksichtigen. | Landesrecht mit Stichtag; nur auf passende Schulform und Jahrgang anwenden |
| TH-PLAN-009 | Thüringer Lehrplanportal / Fachlehrpläne | Inhalte, Kompetenzen und Schulart unterscheiden sich nach Fach und Lehrplanfassung. Ein Stundenplan allein kann die Einhaltung fachlicher Lehrplaninhalte nicht beweisen. | Offizielle fachliche Grundlage; OrdiSchool speichert Bezug und Stundenkontingent, ersetzt keine didaktische Planung |
| TH-PLAN-010 | ThürSchulO Anlagen und schulische Quellen | Die geprüften landesweiten Quellen begründen keine einheitliche allgemeine Raumkapazität oder universelle Priorität „Fachraum vor Klassenraum“ für jede Schule. | Recherchebefund; lokale Raum-/Gebäudevorgaben und Prioritäten müssen mit Quelle eingetragen werden |

### 2.1 Konsequenz der Kohortenübergänge

Die Novelle der ThürSchulO führte neue Rahmenstundentafeln ab dem Schuljahr 2024/25 schrittweise ein, beginnend bei Klassenstufe 5; für bestimmte bereits bestehende Sek-I-Kohorten gelten Übergangsregeln. Für OrdiSchool folgt daraus:

- Schuljahr, Jahrgang **und Einschulungs-/Übergangskohorte** sind getrennte Merkmale.
- Eine Änderung des Schuljahres darf ein bestehendes Regelprofil nicht unbemerkt überschreiben.
- Für jedes Profil muss nachvollziehbar sein, welche Fassung/Anlage verwendet und ob eine Übergangsbestimmung angewandt wurde.
- Die konkrete Tabelle wird erst aktiviert, wenn Schulform und Übergangsfall geprüft sind. Die Stundenzahlen werden nicht aus einem abgelaufenen PDF oder einer Suchmaschinenzusammenfassung importiert.

Die genaue Zuordnung jeder Klasse und jedes Bildungsgangs wird im nächsten fachlichen Prüfschritt anhand der aktuellen konsolidierten ThürSchulO einschließlich § 155 und der Anlagen validiert.

## 3. Raum-, Kapazitäts- und Fachraummodell

Die Landesquellen in diesem Release liefern keine pauschal belastbare Maximalbelegung jedes Raums und keine universelle landesweite Fachraumrangfolge. Daher führt OrdiSchool Raumkapazitäten und Raumprioritäten als **belegte lokale Daten**, nicht als vermeintliche gesetzliche Standardwerte.

Ein Raumprofil soll mindestens enthalten:

- stabile Raum-ID, Gebäude/Standort und Nutzungsstatus;
- zulässige Belegung als Kapazitätswert plus Quelle, Prüfer, Prüfdatum und Gültigkeitszeitraum;
- Raumtyp und fachbezogene Ausstattung;
- Barriere-/Zugänglichkeitsmerkmale, soweit für die Planung erforderlich;
- planbare Zeitfenster, Sperrzeiten und verantwortliche Stelle;
- verbindliche Nutzungseinschränkungen getrennt von weichen Prioritäten;
- Änderungsnachweis, sodass ein bereits veröffentlichter Plan auf den damals gültigen Raumstand zurückgeführt werden kann.

**Fehlende/abgelaufene Kapazitätsquelle:** keine Freigabe als konfliktfreier Plan; stattdessen ungeklärter Prüfhinweis. Das System soll keinen Standardwert (etwa pauschale Klassenstärke oder Raumgröße) als zulässige Kapazität annehmen.

Fachraumplanung erhält getrennte Regeln:

1. **Zwingende Voraussetzung** (z. B. Ausstattung, die für eine geplante praktische Einheit erforderlich ist): harter Filter, wenn fachlich bestätigt.
2. **Fachraumpräferenz** (z. B. ein bestimmter Raum ist bevorzugt): gewichtetes Optimierungsziel.
3. **Freigabe/Abweichung:** eine zuständige Planungsrolle darf einen Vorschlag mit sichtbarem Grund abweichend planen; gesetzliche oder Sicherheitsvorgaben können nicht durch eine Präferenzübersteuerung aufgehoben werden.

Kapazität, Raumbelegung und Stundenzuordnung werden mit dem Stundenplan gekoppelt. Bei Änderung eines Raums muss das System betroffene Stunden, Klassen, Lehrkräfte, Barriereanforderungen, Ausstattung und Vertretungs-/Kalendereinträge neu prüfen.

## 4. Regelprüfung im Planungsassistenten

### Harte Prüfungen

- Stundenumfang gegen aktive Rahmenstundentafel und bestätigte schulinterne Stundentafel;
- Doppelbelegung von Klasse/Kurs, Lehrkraft oder Raum;
- Gruppenzuordnung und Kurs-/Teilungsbedarf im betreffenden Schulformprofil;
- Zeitliche Gültigkeit von Regelprofil, Unterrichtszuordnung, Ressource und Raumdaten;
- nachgewiesene Kapazitätsgrenze bzw. verbindliche Ausstattung/Verfügbarkeit;
- noch offene oder nicht freigegebene Stunden-/Raumplanversion.

### Weiche Ziele und Vorschläge

- Fachraumpräferenzen und Vermeidung unnötiger Fachraumwechsel;
- gleichmäßige Verteilung von Unterrichtsstunden über die Woche;
- möglichst wenige Leerlaufzeiten, Randstunden oder Raumwechsel;
- im Vertretungsfall Fachbezug, Verfügbarkeit und Zusatzbelastung, sofern die Schule diese Kriterien definiert und zulässige Datenquellen dafür freigegeben hat.

Jeder Vorschlag zeigt betroffene Regeln, Datenstand, Regelquelle und eine kurze Begründung. Er markiert explizit, ob eine **harte Regel eingehalten**, **verletzt** oder **mangels belastbarer Daten nicht prüfbar** ist. Weiche Ziele dürfen nicht als Rechtsverstöße bezeichnet werden. Bei unbekannter Rechtslage wird nicht automatisch optimiert und veröffentlicht.

## 5. Fachliche Geltungsgrenzen

- Dieser Release ist das Ausgangsprofil für Thüringen, nicht eine vollständige Umsetzung aller Schulformen und Sonderfälle.
- Berufsbildende Schulen, Förderzentren, Gemeinschaftsschulen, Gesamtschulen, Spezialgymnasien, Kooperationen und Oberstufen-/Kursmodelle benötigen eigene Teilprofile und zusätzliche Quellenprüfung.
- Regelungen der einzelnen Schulträger, Brandschutz-/Gebäudenachweise, Barrierefreiheit, schulische Beschlüsse, Dienstvereinbarungen und konkrete Ressourcenzuweisungen sind nicht durch allgemeine Landesquellen ersetzt.
- Die VVOrgS für ein konkretes Schuljahr ist zeitlich begrenzt. Das Dokument 2026/27 ist in diesem Recherchelauf als veröffentlichte aktuelle Vorschrift identifiziert; der amtliche PDF-Volltext war über den direkten Webzugang nicht verlässlich abrufbar. Daher werden daraus in v0.0.3 keine Detailregeln operationalisiert.
- Rechtsquellen ändern sich. Vor Import, Produktivfreigabe oder Schuljahreswechsel ist der aktuelle konsolidierte Wortlaut durch fachlich zuständige Personen zu prüfen.

## 6. Abnahmekriterien v0.0.3

1. Regelklassen Landesrecht, zeitgebundene Vorgabe, Schulbeschluss, Organisation, Gebäudenachweis und Präferenz sind unterscheidbar.
2. Jede in diesem Dokument abgeleitete Regel verweist auf eine konkrete Quelle/Fundstelle und weist Geltungs-/Prüfstatus aus.
3. Schulform, Jahrgang, Kohorte, Schuljahr und Gültigkeitszeitraum sind als notwendige Profilmerkmale dokumentiert.
4. Stundentafeln und Lehrpläne werden getrennt referenziert; Übergangsregeln sind berücksichtigt.
5. Raumkapazität und Fachraumrangfolge werden nicht als landesweit feststehende Werte erfunden.
6. Der Planungsassistent trennt harte Verstöße von weichen Optimierungszielen und ungelöster Quellenlage.
7. Unsichere oder nicht abgerufene aktuelle Vorschriftentexte sind als nicht operationalisiert markiert.

## Quellen

Die vollständige Quellenliste mit Herausgeber, Fassung, Zugriff und Verwendungsgrenze steht in [`QUELLENREGISTER.md`](QUELLENREGISTER.md).
