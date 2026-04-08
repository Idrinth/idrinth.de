# Écraser des fichiers de mod pour patcher

J'en avais récemment assez d'attendre que le Mod Configuration Tool intègre mes corrections, alors j'ai décidé de créer moi-même un patch pour ce que j'avais ajouté et corrigé.

La partie technique du patch était facile : quelques nouvelles fonctions que j'avais déjà dans des merge requests sur GitHub, simplement reportées pour remplacer le code existant. Ce qui n'était pas facile, c'était de faire fonctionner correctement l'écrasement des fichiers.

La première chose que j'ai essayée était de déclarer MCT comme dépendance de mon mod, ce qui aurait dû suffire selon ma compréhension initiale. Curieusement, tout se comportait toujours comme si rien n'avait changé.

Ensuite, j'ai essayé d'ajuster l'ordre de chargement. Comme on peut l'imaginer, l'effet était minime, voire inexistant, car pour une raison quelconque, les fichiers MCT étaient toujours préférés à leurs homologues patchés.

Ce qui a finalement fonctionné, c'est un simple flag dans la déclaration de dépendance, qui force un chargement. Je n'ai aucune idée de pourquoi ce flag en particulier a rendu l'ordre de chargement complètement inutile, mais j'accepte le résultat : mon mod écrase enfin le code du mod patché.

Donc, si vous avez un jour besoin d'écraser des fichiers, procédez comme suit :

- Créez un nouveau fichier package et enregistrez-le
- Faites un clic droit dessus dans les outils de modding et choisissez les dépendances
- Entrez le nom de la dépendance, par exemple abc.pack, et assurez-vous qu'il n'y a pas d'espaces en fin de ligne
- Cochez la case devant le nom pour que les fichiers soient effectivement écrasés

J'espère que cela aidera quelqu'un, amusez-vous bien avec le modding ! Le mod est d'ailleurs disponible [sur le Workshop](https://steamcommunity.com/sharedfiles/filedetails/?id=3700933274).
