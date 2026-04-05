# Expérience de blogging - Ou pourquoi j'ai construit un blog à partir de zéro

Comme vous l'avez peut-être remarqué, il s'agit en grande partie d'un site statique, contenant du HTML, du CSS et une petite quantité de JavaScript.

Le backend est composé de fichiers JSON et MD, avec pratiquement tout généré à partir de ceux-ci lors de la compilation, afin que la consultation du contenu soit rapide.

## Qu'est-ce qui manque pour l'instant et quels sont mes plans ?

- Commentaires : J'ai intentionnellement laissé les commentaires de côté, pour ne pas avoir à faire de modération régulière et à vérifier leur contenu potentiellement nuisible
- Recherche par tags : Cela viendra, je ne le considère simplement pas encore comme pertinent
- Open-Source : Ce blog n'est actuellement pas open-source. Je me demande si je devrais changer cela, étant donné qu'il y a potentiellement plus de personnes qui aiment les solutions simples pour les blogs
- RSS et ATOM : Les flux sont en cours de construction, ils seront disponibles en plusieurs variantes, un pour tous les articles et un pour chaque catégorie, afin que vous puissiez suivre les parties intéressantes de votre choix

Si quelque chose d'autre manque, faites-le moi savoir, je serais ravi de réfléchir à d'autres ajouts !

# Pourquoi pas Wordpress ou d'autres systèmes de blog

Il y a plusieurs raisons, mais cela se résume principalement à la surcharge. Je peux écrire du HTML ou du Markdown très facilement et je ne me soucie pas de la plupart des fonctionnalités que ces outils apportent. D'un autre côté, je me retrouve avec le code de quelqu'un d'autre qui tourne sur mon serveur et j'ai potentiellement des risques que je ne peux pas prévoir.

La solution simple, c'est quelques lignes de PHP, quelques lignes de HTML et aucun problème avec des bibliothèques tierces.
