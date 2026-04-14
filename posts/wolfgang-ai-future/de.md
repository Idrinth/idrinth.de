# Wolfgang AI - Ein Spielleiter in Schwierigkeiten

Ich habe im letzten Jahr viel Zeit in Wolfgang gesteckt. Er wird von mir und ein paar Mitwirkenden gebaut und bietet Schreibsitzungen für Rollenspiele für jeden, der ein bisschen Zeit übrig hat.

Während der KI-Teil voll funktionsfähig ist, scheint es, dass wir etwas an der MariaDB kaputt gemacht haben, die die Metadaten hostet, was wiederum die gesamte Anwendung am Starten hindert. Wenn du schon immer mal mit dem Debuggen einer dockerisierten MariaDB herumspielen wolltest, ist jetzt deine Chance, denn im Moment kann ich mir die nötige Zeit dafür nicht nehmen.

## Was steht in Zukunft an?

Neben Reparaturen besteht der Bedarf, die KI mit mehr Trainingsdaten zu füttern, damit die Spezialisten bessere Ergebnisse liefern als jetzt. Dazu braucht es nicht mehr als eine Tastatur und einen GitHub-Account, also bin ich zuversichtlich, dass die Gesamtzahl der Trainingstexte langsam wachsen wird.

Zusätzlich plane ich eine UI-Überarbeitung, die schon einige Male begonnen wurde und nie wirklich vorangekommen ist. Die aktuelle UI ist funktional und relativ performant, sieht aber sehr veraltet aus. Design-Input ist auch hier sehr willkommen, aber bitte plant inkrementelle Änderungen, keine riesige Überarbeitung. Große Überarbeitungen sind für ein Projekt dieser Größe einfach nicht machbar.

## Finanzierung

Die Infrastruktur zu hosten ist teuer. Nicht massiv, aber die Anforderung an große Kontextfenster in den Modellen erfordert große VRAM-Kapazitäten. Wir nutzen dafür Beam Cloud und sind in der Regel durch deren monatliche Basisabdeckung abgedeckt, wobei für diesen Teil allein 20-30 $ an Kosten anfallen.

Ich habe darüber nachgedacht, Werbung einzubauen, aber vorerst stelle ich mir eine andere Richtung vor: Merchandise und Upgrades.

Merch ist klar, wir haben ein schönes Logo, also können wir es den Leuten ermöglichen, es zu tragen. Wenig Aufwand, wenig Nutzen. Upgrades hingegen verursachen Kosten für Wolfgang, weil sie auf irgendeine Weise die Anzahl der genutzten Nachrichten erhöhen. Neben kostenlosen Upgrades für Beiträge zum Repo gibt es einige bezahlte Stufen, die helfen würden, das auszugleichen.

Ich bin mir nicht sicher, ob das funktionieren wird, aber wir werden sehen.
