# Followme

Application Nextcloud de veille collaborative : articles, catégories, classement des contributeurs et export de newsletter Hugo.

## Compatibilité et installation

Métadonnées : Nextcloud 30–36, PHP 8.2+. Validation de cette version sur Nextcloud 33 / MariaDB 11.
L'interface utilise exclusivement JavaScript natif, CSS et les API Nextcloud : aucune compilation ni dépendance JavaScript d'exécution.

Installer le dossier `followme` dans le répertoire d'applications persistant de Nextcloud, puis exécuter comme utilisateur du serveur web :

```sh
php occ app:enable followme
```

Pour une mise à jour : sauvegarder la base et le dossier de l'application, remplacer les fichiers par l'archive signée, puis exécuter `php occ upgrade`. La migration 1.3.0 conserve les actualités, conserve la dernière valeur des paramètres dupliqués, chiffre les mots de passe existants avec Nextcloud ICrypto et ajoute les index. Les requêtes respectent le préfixe configuré et évitent les fonctions SQL propres à MySQL. Conserver le secret d'instance Nextcloud lors des sauvegardes/restaurations pour pouvoir déchiffrer les paramètres.

Avec l'image Docker Nextcloud, ne pas monter le checkout dans `/var/www/html/apps` avant l'initialisation : son rsync peut l'effacer. Utiliser le répertoire `custom_apps` et la procédure de montage adaptée à l'installation.

## Paramètres et utilisation

Le menu **Paramètres**, en bas de la navigation, donne aux administrateurs un lien vers **Administration → Followme**. L'accès et l'enregistrement sont contrôlés côté serveur ; les écritures exigent un jeton CSRF.

Les intégrations WordPress et Rocket.Chat sont facultatives. Elles utilisent des adresses HTTPS. Les champs de mot de passe restent toujours vides : les laisser vides conserve les secrets existants ; vider l'adresse désactive l'intégration. Les secrets sont chiffrés en base et ne sont pas exposés dans le formulaire. Un échec de notification Rocket.Chat ne supprime pas l'actualité enregistrée.

Tous les utilisateurs connectés peuvent contribuer, modifier et supprimer les actualités de cet espace partagé, comme dans les versions précédentes. L'auteur initial est conservé lors des modifications.

La version affichée est fournie par les métadonnées de l'application via Nextcloud. Le classement couvre l'année civile UTC ; les filtres du navigateur comprennent le dernier jour sélectionné. La modale native prend en charge le clavier, Échap et les petits écrans.

## Développement et tests

Chaque tâche cloud est isolée : utiliser les checkouts existants, sans worktree supplémentaire sauf demande explicite.

```sh
composer install --prefer-dist --no-interaction
NEXTCLOUD_ROOT=/chemin/vers/nextcloud vendor/bin/phpunit -c phpunit.xml
node --check js/script.js
node --check js/admin.js
make appstore
```

Les tests PHPUnit nécessitent une instance Nextcloud installée avec Followme activé ; les tests de base de données annulent leurs données par transaction. Ne pas les exécuter sur une instance de production. Le bootstrap prend `NEXTCLOUD_ROOT` pour les checkouts situés hors du dossier Nextcloud.

Tests navigateur sur une instance isolée avec les comptes `admin` et `followme-reader` :

```sh
npm ci
npx playwright install chromium
FOLLOWME_TEST_BASE_URL=http://127.0.0.1:8080 \
FOLLOWME_TEST_ADMIN_PASSWORD='mot-de-passe-local-admin' \
FOLLOWME_TEST_READER_PASSWORD='mot-de-passe-local-lecteur' npm run test:browser
```

`FOLLOWME_BROWSER_EXECUTABLE` permet d'utiliser un Chromium déjà installé. Les captures sont enregistrées sous `build/browser/`. Playwright est une dépendance de test uniquement ; aucun framework n'est chargé par Followme.

La CI installe Nextcloud 33/MariaDB, exécute PHPUnit et les parcours navigateur (CRUD, export, modale bureau/mobile et permissions) et prépare l'archive. Une release GitHub déclenche les mêmes tests, vérifie que le tag correspond à `appinfo/info.xml`, signe l'archive et la publie dans le catalogue Nextcloud. Configurer les secrets GitHub `APP_PRIVATE_KEY`, `APP_CERTIFICATE`, `APPSTORE_TOKEN` avec le certificat de l'application `followme`. Aucune publication ni mise à jour d'un serveur de production n'est déclenchée par un simple push.
