# Déploiement de SGT KayTech

À exécuter en fenêtre de maintenance après le GO du manager. Préparer les SHA approuvés des dépôts web et mobile ainsi que l’APK vérifiée. Consigner le SHA de chaque dépôt dans le journal de déploiement.

## Préparer et sauvegarder

1. Depuis le SHA mobile approuvé, exécuter dans `sgt_mobile/app` :

   ```bash
   npm ci
   SGT_MOBILE_ENV=production EXPO_NO_DOTENV=1 EXPO_PUBLIC_SGT_API_URL=https://sgt.kaytechnologie.online/api/v1 npm run export:pwa
   ```

   Fournir `SGT_VAPID_PUBLIC_KEY` si les notifications web sont configurées. Vérifier `dist/manifest.webmanifest` (nom **SGT KayTech**, chemin et portée `/app/`). Préparer le répertoire complet `dist` pour publication, avec ses fichiers de service worker et ses ressources.

2. Sur le VPS, dans `/var/www/erp-sgt`, relever le SHA servi et créer l’étiquette `git tag -a avant-parite -m "Version web avant Parité"` sur ce SHA. Si l’étiquette existe, contrôler qu’elle désigne bien la version actuellement servie. Sauvegarder la PWA publique active séparément pour pouvoir rétablir ses ressources et son service worker.
3. Mettre l’application en maintenance avec `php artisan down` et suspendre le traitement des écritures différées via le gestionnaire du worker. Créer une sauvegarde MySQL avec un fichier de connexion protégé, hors racine web. Utiliser un chemin unique, vérifier le code de sortie, la présence de données et la somme SHA-256 :

   ```bash
   umask 077
   dump="/var/backups/sgt/avant-parite-$(date +%Y%m%dT%H%M%S).sql"
   mysqldump --defaults-extra-file=/chemin/protege/mysql-sgt.cnf --single-transaction --no-tablespaces --routines --triggers --events erp_sgt > "$dump" &&
     test -s "$dump" && sha256sum "$dump"
   ```

## Installer la version approuvée

1. Activer le code web approuvé, puis exécuter `composer install --no-dev --optimize-autoloader --no-interaction`. Conserver les paramètres de production et vérifier `APP_DEBUG=false`, `APP_ENV=production`, `APP_URL=https://sgt.kaytechnologie.online` et `BCRYPT_LIMIT=72` dans la configuration d’environnement.
2. Exécuter `php artisan config:clear`, puis **`php artisan migrate --force` avant le rechargement PHP-FPM**. Vérifier `php artisan migrate:status`.
3. Reconstruire les caches avec `php artisan config:cache`, `php artisan route:cache` et `php artisan view:cache`. Recharger PHP-FPM avec `sudo systemctl reload php8.3-fpm` pour renouveler les processus et leur OPcache. Vérifier l’état du service.
4. Basculer la PWA préparée sur `/app/` sur la même origine que l’API. Vérifier la configuration Nginx et l’absence de mise en cache longue pour `index.html`, le manifest et le service worker. Relancer le worker via son gestionnaire de services et vérifier qu’il tourne avec le nouveau code. `php artisan queue:restart` permet de demander une sortie propre des workers Laravel gérés par un superviseur. Remettre l’application en ligne avec `php artisan up`.

## Vérifier puis diffuser

- Vérifier `/up`, la connexion web, le tableau de bord et l’accès aux membres et sites avec un compte Manager. Vérifier le 403 avec un compte Technicien.
- Sur la PWA, vérifier le manifest, la navigation et le renouvellement du service worker depuis une installation existante. Tester une action hors ligne suivie d’une reconnexion avec des données de recette dédiées.
- Avec un compte de contrôle, vérifier qu’un changement de mot de passe invalide la seconde session web, le jeton mobile et le renouvellement OAuth du connecteur Claude. Vérifier la reconnexion et les notifications si elles sont configurées.
- Consulter les journaux PHP-FPM, Laravel et du worker. Le manager signe et vérifie l’APK avant diffusion. Publier l’annonce ci-dessous après validation.

## Retour arrière

Remettre en maintenance, arrêter le worker, réactiver le code de l’étiquette `avant-parite` et restaurer les fichiers de la PWA précédente. Réinstaller ses dépendances, reconstruire les caches, recharger PHP-FPM, puis relancer le worker et les contrôles de fumée. **Conserver les migrations en place**. La sauvegarde MySQL est destinée à une restauration de secours contrôlée, qui doit tenir compte des écritures intervenues depuis la sauvegarde. Garder la diffusion de l’APK suspendue si la version web et la PWA doivent être rétablies.

## Annonce aux utilisateurs

**SGT KayTech se met à jour.** À chaque changement de mot de passe, vos sessions sont fermées sur tous vos appareils et votre connecteur Claude est déconnecté. Pour reprendre votre travail, reconnectez-vous au SGT avec votre identifiant et votre nouveau mot de passe. Si vous utilisez le connecteur Claude, reconnectez-le et autorisez de nouveau son accès au SGT.
