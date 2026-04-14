# Wolfgang AI - Un maître de jeu en difficulté

J'ai passé beaucoup de temps sur Wolfgang au cours de l'année écoulée. Il est construit par moi et quelques contributeurs, et propose des sessions d'écriture pour le jeu de rôle à toute personne disposant d'un peu de temps.

Alors que la partie IA est pleinement fonctionnelle, il semble que nous ayons cassé quelque chose au niveau de la MariaDB qui héberge les métadonnées, ce qui empêche toute l'application de démarrer. Si vous avez toujours voulu vous amuser à déboguer une MariaDB dockerisée, voici votre chance, car en ce moment je ne peux pas y consacrer le temps nécessaire.

## Que nous réserve l'avenir ?

En plus des réparations, il est nécessaire d'alimenter l'IA avec davantage de données d'entraînement, afin que les spécialistes produisent de meilleurs résultats qu'actuellement. Cela ne nécessite rien de plus qu'un clavier et un compte GitHub, alors j'ai bon espoir que le nombre total de textes d'entraînement augmentera lentement.

Par ailleurs, je prévois une refonte de l'interface, qui a été lancée plusieurs fois sans jamais aboutir. L'interface actuelle est fonctionnelle et relativement performante, mais elle paraît très datée. Toute contribution sur le design est également la bienvenue ici, mais merci de prévoir des changements progressifs, pas une énorme refonte. Les grandes refontes ne sont tout simplement pas réalisables pour un projet de cette taille.

## Financement

Héberger l'infrastructure coûte cher. Pas massivement, mais le besoin de grandes fenêtres de contexte dans les modèles exige de grandes capacités de VRAM. Nous utilisons Beam Cloud pour cela et sommes généralement couverts par leur couverture mensuelle de base, avec 20-30 $ de coûts pour cette partie seulement.

J'ai pensé à ajouter de la publicité, mais pour l'instant j'envisage une direction différente : du merchandising et des upgrades.

Le merchandising est clair, nous avons un beau logo, donc nous pouvons permettre aux gens de le porter. Peu d'effort, peu de bénéfice. Les upgrades, en revanche, génèrent des coûts pour Wolfgang, car ils augmentent d'une manière ou d'une autre le nombre de messages utilisés. En plus des upgrades gratuits pour les contributions au dépôt, il existe des paliers payants qui aideraient à compenser cela.

Je ne suis pas sûr que cela fonctionnera, mais on verra.
