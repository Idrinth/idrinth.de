# Mod-Dateien überschreiben, um zu patchen

Ich hatte es kürzlich satt, darauf zu warten, dass das Mod Configuration Tool meine Korrekturen übernimmt, also dachte ich mir, ich baue einfach selbst einen Patch für das, was ich hinzugefügt und behoben habe.

Der technische Teil des Patches war einfach: ein paar neue Funktionen, die ich bereits in Merge Requests auf GitHub hatte, einfach übertragen und bestehenden Code ersetzt. Was nicht einfach war, war das korrekte Überschreiben der Dateien.

Das Erste, was ich versuchte, war MCT als Abhängigkeit meines Mods zu deklarieren, was nach meinem anfänglichen Verständnis ausgereicht haben sollte. Seltsamerweise verhielt es sich trotzdem so, als hätte sich nichts geändert.

Zweitens versuchte ich die Ladereihenfolge anzupassen. Wie man sich vorstellen kann, war der Effekt winzig, genauer gesagt nicht vorhanden, weil aus irgendeinem Grund die MCT-Dateien immer noch gegenüber ihren gepatchten Gegenstücken bevorzugt wurden.

Was es schließlich zum Laufen brachte, war ein einzelnes Flag in der Abhängigkeitsdeklaration, das einen Ladevorgang erzwingt. Ich habe keine Ahnung, warum genau dieses Flag die Ladereihenfolge komplett überflüssig machte, aber ich akzeptiere das Ergebnis: Mein Mod überschreibt endlich den Code des gepatchten Mods.

Falls du also jemals Dateien überschreiben musst, gehe wie folgt vor:

- Erstelle eine neue Package-Datei und speichere sie
- Klicke mit der rechten Maustaste darauf in den Modding-Tools und wähle Abhängigkeiten
- Gib den Namen der Abhängigkeit ein, zum Beispiel abc.pack, und stelle sicher, dass keine Leerzeichen am Ende stehen
- Aktiviere das Kontrollkästchen vor dem Namen, damit Dateien tatsächlich überschrieben werden

Ich hoffe, das hilft jemandem, viel Spaß beim Modden! Der Mod ist übrigens [im Workshop](https://steamcommunity.com/sharedfiles/filedetails/?id=3700933274) verfügbar.
