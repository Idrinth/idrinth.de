# Skyrim Party Sheet

Skyrim Party Sheet is a UI mod, that allows a quick glance at companions, followers and summons to make better tactical decisions in the het of a fight. It seems so far to use very little CPU and RAM, so it is a nice addition to anyone using NPCs along their way - unless they have a complete army of them somehow.

I stumbled upon this mod some time yesterday and was curious to see how it would interact with my own follower, who doesn't quite follow the usual mechanics of a follower.

Specifically Idrinth Thalui is no a teammate of the player to prevent his fights from causing bounties on the player and to prevent some other effects that I still consider annoying and not easy to handle otherwise. This small difference seemed to break the mod's recognition of Idrinth following and fighting alongside the player.

I reached out to the author the same evening, asking about how to bypass that flag and got a great answer within a few hours:

- Idrinth would be considered a Quest Follower if in a specific faction or if using specific packages
- Adding that faction would not show him always but during combat where showing was relevant

I ended up implementing the faction earlier today and during tests it worked beautifully. The following was too unstable, considering he has a large amount of packages and not all are following the player, so I decided against it.

A big thank you to the author for the quick help, I think I found a new mod to keep for the additional information I can gather that way.
