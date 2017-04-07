symfony
=======

A Symfony project created on April 3, 2017, 8:48 am.

Code source de la plateforme d'annonce construite pour apprendre et tester Symfony dans sa version 3.

Installation

1. Définir vos paramètres d'application

Pour ne pas qu'on se partage tous nos mots de passe, le fichier app/config/parameters.yml est ignoré dans ce dépôt. A la place, vous avez le fichier parameters.yml.dist que vous devez renommer (enlevez le .dist) et modifier.

2. Télécharger les vendors

Avec Composer, lancer la commande :

php composer.phar install

3. Créer la base de données

Lancer les commandes :

php bin/console doctrine:database:create

php bin/console doctrine:schema:update --dump-sql
php bin/console doctrine:schema:update --force

php bin/console doctrine:fixtures:load

5. Publier les assets

Publiez les assets dans le répertoire web :

php bin/console assets:install web

Tout est good !