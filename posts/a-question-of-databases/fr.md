# Une question de bases de données

J'ai lu pas mal de publications sur X récemment, qui faisaient la promotion d'applications à base de données unique. Cela m'inquiète, car la génération qui vient après moi tombe dans le même piège que la génération avant moi : un système unique pour tout résoudre.

## En quoi les bases de données diffèrent-elles ?

Les bases de données stockent des données, c'est leur caractéristique fondamentale. Cependant, différentes bases de données stockent différentes données de différentes manières et sont plus performantes lorsqu'on les utilise pour ce pour quoi elles ont été conçues.

Les données relationnelles, par exemple, sont généralement gérées par une base de données SQL, car ses mécanismes d'indexation sont optimisés exactement pour le type de jeux de données où des tuples à peu de champs sont liés à d'autres tuples à peu de champs.

MongoDB et de nombreuses autres bases de données NoSQL stockent plutôt des sortes de documents. Un document définit par sa structure une seule façon dont les données sont destinées à être consommées. On peut y représenter des données relationnelles, mais c'est plus lent et demande plus d'efforts. Il vaut mieux y stocker des données hiérarchiques, par exemple un document avec ses pages.

Les bases de données vectorielles sont encore un tout autre type. Elles stockent les données comme matière brute, mais leur avantage n'est pas de pouvoir récupérer des données par identifiant, mais de pouvoir trouver des choses qui sont logiquement liées, et non structurellement. C'est le type de base de données par défaut pour tous les produits d'IA qui indexent les données au lieu de tout déverser dans chaque requête.

Les caches et les magasins clé-valeur sont le quatrième grand type. Ils ont un ensemble de fonctionnalités restreint, mais offrent une vitesse que les autres bases de données ne peuvent généralement pas égaler pour ce qu'ils font : lire une valeur spécifique à un identifiant spécifique donné. Redis en est un exemple classique, largement utilisé pour les données qui n'ont pas besoin d'être persistantes ou dont la génération à la volée est coûteuse.

## Ai-je besoin de tout cela pour mon chatbot de 100 utilisateurs ?

Probablement pas, car 100 utilisateurs ne représentent pas une grande quantité de données. Leurs données de chat et métadonnées seront suffisamment petites pour être traitées par presque n'importe quel système, même ceux basés sur des fichiers. Le problème est la mise à l'échelle correcte. On ne veut pas arriver à un point où plus rien ne fonctionne parce que la base de données est surchargée à faire quelque chose pour laquelle elle n'est pas faite, simplement parce qu'on a négligé de trouver le bon outil pour le travail quelques mois ou années plus tôt.

## Existe-t-il des exemples de systèmes multi-bases de données ?

Presque tous les systèmes à code source fermé que j'ai touchés utilisaient au minimum 2 à 3 bases de données, généralement un MySQL et un cache de type Redis au cœur. Côté open source, j'ai [Wolfgang AI](https://github.com/bjoern-buettner/roleplay-ai) qui utilise beaucoup de bases de données pour minimiser les temps de chargement et de traitement des données. Ce n'est peut-être pas parfait non plus, mais si vous trouvez quelque chose à améliorer, faites-le moi savoir !
