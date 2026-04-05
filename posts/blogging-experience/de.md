# Blogging-Erfahrung - Oder warum ich einen Blog von Grund auf gebaut habe

Wie man vielleicht bemerkt hat, ist dies größtenteils eine statische Seite, bestehend aus HTML, CSS und einer kleinen Menge JavaScript.

Das Backend besteht aus JSON- und MD-Dateien, wobei so gut wie alles daraus während der Build-Zeit generiert wird, damit das Betrachten der Inhalte schnell geht.

## Was fehlt bisher und was sind meine Pläne?

- Kommentare: Ich habe die Kommentare absichtlich weggelassen, damit ich keine regelmäßige Moderation durchführen und sie auf potenziell schädliche Inhalte prüfen muss
- Tag-Suche: Das wird noch kommen, ich sehe es nur noch nicht als relevant an
- Open-Source: Dieser Blog ist derzeit nicht Open-Source. Ich frage mich, ob ich das ändern sollte, da es möglicherweise mehr Leute gibt, die einfache Lösungen für Blogs mögen
- RSS und ATOM: Die Feeds werden gerade gebaut, sie werden in mehreren Varianten verfügbar sein, einer für alle Beiträge und einer für jede Kategorie, damit man den interessanten Teilen seiner Wahl folgen kann

Falls noch etwas fehlt, lasst es mich wissen, ich würde mich freuen, über weitere Ergänzungen nachzudenken!

# Warum nicht Wordpress oder andere Blog-Systeme

Es gibt mehrere Gründe, aber es läuft hauptsächlich auf den Overhead hinaus. Ich kann HTML oder Markdown sehr einfach schreiben und die meisten Funktionen, die diese Tools mitbringen, interessieren mich nicht. Auf der anderen Seite läuft dann fremder Code auf meinem Server und es gibt möglicherweise Risiken, die ich nicht vorhersagen kann.

Die einfache Lösung sind ein paar Zeilen PHP, ein paar Zeilen HTML und kein Problem mit Drittanbieter-Bibliotheken.
