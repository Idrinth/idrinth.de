# Skyrim Party Sheet

Skyrim Party Sheet est un mod d'interface qui permet de jeter un coup d'œil rapide aux compagnons, suivants et invocations afin de prendre de meilleures décisions tactiques au cœur d'un combat. Jusqu'à présent, il semble utiliser très peu de CPU et de RAM, ce qui en fait un ajout intéressant pour quiconque utilise des PNJ sur son chemin – à moins d'en avoir une armée entière, d'une manière ou d'une autre.

Je suis tombé sur ce mod hier dans la journée et j'étais curieux de voir comment il interagirait avec mon propre suivant, qui ne respecte pas tout à fait les mécaniques habituelles d'un suivant.

Plus précisément, Idrinth Thalui n'est pas un coéquipier du joueur, afin d'éviter que ses combats n'entraînent des primes sur le joueur et de prévenir d'autres effets que je trouve encore agaçants et pas faciles à gérer autrement. Cette petite différence semblait empêcher le mod de reconnaître qu'Idrinth suivait le joueur et se battait à ses côtés.

J'ai contacté l'auteur le soir même pour lui demander comment contourner ce drapeau, et j'ai reçu une excellente réponse en quelques heures :

- Idrinth serait considéré comme un suivant de quête s'il était dans une faction spécifique ou s'il utilisait des paquets spécifiques
- Ajouter cette faction ne le ferait pas apparaître en permanence, mais pendant le combat, là où son affichage est pertinent

J'ai fini par implémenter la faction plus tôt aujourd'hui et lors des tests, cela a parfaitement fonctionné. L'approche par paquets était trop instable, étant donné qu'il a un grand nombre de paquets et qu'ils ne suivent pas tous le joueur, j'ai donc décidé de ne pas la retenir.

Un grand merci à l'auteur pour son aide rapide, je pense avoir trouvé un nouveau mod à conserver pour les informations supplémentaires que je peux récolter de cette manière.
