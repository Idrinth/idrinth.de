# Idrinth's Teaks: Blame Yourself

In den letzten Tagen war ich ziemlich faul und habe hauptsächlich an meiner Skyrim-Modliste herumgeflickt. Eine kleine Sache, die ich gebaut habe, war eine neue Mod-Version, die es dem Spieler erlaubt, sich selbst für ein paar Dinge anzuzeigen, die als eher kriminell gelten.

Sich selbst wegen daedrischer Ausrüstung anzuzeigen hinzuzufügen, war keine große Sache, dachte ich, ich habe es einfach hinzugefügt, weil es sich richtig anfühlte. Nach einer so langen Pause vom aktiven Skyrim-Modding habe ich aber ein paar Dinge vergessen:

- Als ESL markierte Dateien benötigen ihre FormIDs in einem bestimmten Bereich, den das Creation Kit nicht automatisch beachtet
- Das Komprimieren von FormIDs wirkt sich nicht auf die Dateinamen der dazugehörigen Audiodateien aus
- An Dialoge angehängte Sounddateien werden anhand der Stimme und des Namens abgerufen, die der FormID dieser Antwort folgen

Ich habe eine gute halbe Stunde damit verbracht, Dateien zu jagen, nachdem ich die FormIDs komprimiert hatte, einfach weil die Namen nicht lesbar sind, das Komprimieren keine Liste der Änderungen liefert und ich nicht versehentlich eine weitere teilweise kaputte Version veröffentlichen wollte.

Beim nächsten Mal werde ich die Komprimierung doppelt prüfen, bevor ich Sounddateien hinzufüge, das sollte die Fehlerquote deutlich reduzieren.
