# Wolfgang AI - A Gamemaster in Trouble

I have spent a lot of time on Wolfgang over the last year. He is built by me and a few contributors, offering writing sessions for role play for anyone with a bit of time to spare.

While the AI part is fully functional it seems we broke something about the MariaDB that hosts metadata, in turn preventing the whole application from starting. If you ever wanted to play around with debugging a dockerized MariaDB, here is your chance, because right now I can't make the necessary time for it.

## What is in the future?

Besides repairs, there is a need to feed the AI with more training data, so that the specialists produce better output than they do now. This doesn't need more than a keyboard and a github account, so I'm hopeful that the total number of training texts will grow slowly.

Additionally I plan a UI remake, one that had been started a few times and never got anywhere. The current UI is functional and relatively performant, but it looks very dated. Design input is very welcome here as well, but please plan incremental changes, not a huge rework. Huge reworks are just not feasible for a project this size.

## Financing

Hosting the infrastructure is expensive. Not massively, but the requirement for large context windows in the models requires large VRAM capacities. We have been using beam cloud for that and are usually covered by their monthly base coverage, using up 20-30$ in terms of costs for that part alone.

I have thought about adding ads, but for now I'm envisioning a different direction: Merch and Upgrades.

Merch is clear, we have a nice logo, so we can allow people to wear it. Little effort, little benefit. Upgrades instead are cost prducing for Wolfgang, because they increase in some way the amount of messages used. Next to free upgrades for contributions to the repo, there are some paid tiers that would help offset that.

I am not sure if that will work out, but we'll see.
