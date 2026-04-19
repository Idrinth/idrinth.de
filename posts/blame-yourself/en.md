# Idrinth's Teaks: Blame Yourself

I was lazy in the last couple days, mostly patching my Skyrim modlist. One small thing I built was a new mod version that allows the player to self-report for a few things considered rather criminal.

Adding reporting yourself for daedric gear was not a huge deal I though, just added it because it felt right. Having had such a long break from active Skyrim modding I forgot a few things though:

- ESL flagged files need their FormIDs in a specific range that the Creation Kit does not automatically respect
- Compressing FormIDs does not affect the file names of audio files attached to them
- Sound files attached to dialogue are retrieved by voice and name following the FormID of that response

I pretty much spent half an hour hunting files after I compressed the FormIDs, just because the names are not readable, the compressing doesn't give you a list of changes and I didn't want to package another partially broken version by accident.

For next time I will double check the compression before adding sound files, that should significantly reduce the rate of errors.
