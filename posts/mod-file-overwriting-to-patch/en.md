# Mod File Overwriting To Patch

I recently was fed up with waiting for the Mod Configuration Tool to include my fixes, so I thought I'd just go ahead and build a patch for what I added and fixed.

The technical part of the patch was easy, a few new functions I already had in merge requests on github just carried over, replacing existing code. What was not easy was getting it to overwrite properly.

The first thing I tried was declaring MCT a dependency of my mod, something that should have been sufficient in my initial understanding. Weirdly enough it still behaved as if nothing was changed.

Second I tried adjusting the load order. As you can imagine the effect was tiny, as in non existant, because out of some reason the MCT files were still preferred over their patched counterpart.

What finally did it was a single flag in the dependency declaration, that forces a load. I have no idea why that flag specifically made the load order completely unnecessary, but I'm accepting the result: My mod finally overwrites the patched mod's code.

So if you ever need to overwrite files, do the following:

- create a new package file and safe it
- right-click it in the modding tools and choose dependencies
- enter the name of the dependency, for example abc.pack and make sure there are no trailing spaces
- check the box before the name so files are actually overridden

I hope this helps someone, have fun modding! Mod is [on the Workshop](https://steamcommunity.com/sharedfiles/filedetails/?id=3700933274) by the way.
