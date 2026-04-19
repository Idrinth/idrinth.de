# Idrinth's Teaks: Blame Yourself

J'ai été paresseux ces derniers jours, principalement occupé à rafistoler ma liste de mods Skyrim. Une petite chose que j'ai construite était une nouvelle version de mod qui permet au joueur de se dénoncer lui-même pour quelques actes considérés comme plutôt criminels.

Ajouter la possibilité de se dénoncer pour l'équipement daedrique n'était pas une grande affaire, pensais-je, je l'ai juste ajouté parce que cela semblait juste. Après une si longue pause du modding actif de Skyrim, j'ai cependant oublié quelques choses :

- Les fichiers marqués ESL ont besoin que leurs FormIDs soient dans une plage spécifique que le Creation Kit ne respecte pas automatiquement
- La compression des FormIDs n'affecte pas les noms des fichiers audio qui leur sont attachés
- Les fichiers sonores attachés aux dialogues sont récupérés par voix et par nom en suivant le FormID de cette réponse

J'ai passé à peu près une demi-heure à chasser des fichiers après avoir compressé les FormIDs, simplement parce que les noms ne sont pas lisibles, la compression ne te donne pas une liste des changements et je ne voulais pas empaqueter accidentellement une autre version partiellement cassée.

La prochaine fois, je vérifierai deux fois la compression avant d'ajouter les fichiers sonores, cela devrait réduire considérablement le taux d'erreurs.
