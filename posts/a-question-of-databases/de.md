# Eine Frage der Datenbanken

Ich habe in letzter Zeit ziemlich viele Beiträge auf X gelesen, die Anwendungen mit einer einzigen Datenbank beworben haben. Es beunruhigt mich das zu lesen, denn hier fällt die Generation nach mir auf dasselbe Problem herein wie die Generation vor mir: Ein System, das alles löst.

## Wie unterscheiden sich Datenbanken?

Datenbanken speichern Daten, das ist ihr bestimmendes Merkmal. Verschiedene Datenbanken speichern jedoch unterschiedliche Daten auf unterschiedliche Weise und sind leistungsfähiger, wenn man sie für das einsetzt, wofür sie gebaut wurden.

Relationale Daten werden beispielsweise in der Regel durch eine SQL-Datenbank abgebildet, da deren Indexierungsmechanismen genau für die Art von Datensätzen optimiert sind, bei denen wenige Felder in Tupeln mit anderen wenigen Feldern in Tupeln verknüpft werden.

MongoDB und viele andere NoSQL-Datenbanken speichern stattdessen eine Art Dokumente. Ein Dokument definiert durch seine Struktur eine einzelne Art, wie Daten konsumiert werden sollen. Man kann relationale Daten darin abbilden, aber es ist langsamer und aufwendiger. Besser ist es, hierarchische Daten darin zu speichern, zum Beispiel ein Dokument mit seinen Seiten.

Vektordatenbanken sind wieder ein ganz anderer Typ. Sie speichern Daten als Rohmaterial, aber ihr Vorteil liegt nicht darin, dass man Daten per ID abrufen kann, sondern dass man Dinge finden kann, die logisch zusammenhängen, nicht strukturell. Dies ist der Standard-Datenbanktyp für alle KI-Produkte, die Daten indexieren, anstatt alles in jede Anfrage zu packen.

Caches und Key-Value-Stores sind der vierte große Typ. Sie haben einen kleinen Funktionsumfang, bieten aber eine Geschwindigkeit, die andere Datenbanken für das, was sie tun, normalerweise nicht erreichen können: einen bestimmten Wert unter einer bestimmten, gegebenen ID auslesen. Redis ist hier ein klassisches Beispiel, weit verbreitet für Daten, die nicht persistent sein müssen oder deren Erzeugung zur Laufzeit aufwendig ist.

## Brauche ich das für meinen Chatbot mit 100 Nutzern?

Wahrscheinlich nicht, denn 100 Nutzer sind keine große Datenmenge. Ihre Chat-Daten und Metadaten sind klein genug, um mit fast jedem System verarbeitet zu werden, sogar mit dateibasierten. Das Problem ist die richtige Skalierung. Man möchte nicht an einen Punkt geraten, an dem nichts mehr funktioniert, weil die Datenbank mit etwas überlastet ist, worin sie nicht gut ist, nur weil man es ein paar Monate oder Jahre zuvor versäumt hat, das richtige Werkzeug für die Aufgabe zu finden.

## Gibt es Beispiele für Systeme mit mehreren Datenbanken?

Fast alle Closed-Source-Systeme, mit denen ich gearbeitet habe, verwendeten mindestens 2-3 Datenbanken, in der Regel eine MySQL und einen Redis-ähnlichen Cache als Kern. Im Open-Source-Bereich habe ich [Wolfgang AI](https://github.com/bjoern-buettner/roleplay-ai), das viele Datenbanken nutzt, um Lade- und Datenverarbeitungszeiten zu minimieren. Ist vielleicht auch nicht perfekt, aber wenn ihr etwas findet, das man verbessern kann, lasst es mich wissen!
