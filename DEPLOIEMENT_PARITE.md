# Déploiement de SGT KayTech

À exécuter en fenêtre de maintenance après le GO du manager. Préparer les SHA approuvés des dépôts web et mobile ainsi que l’APK vérifiée. Consigner le SHA de chaque dépôt dans le journal de déploiement.

## Préparer la fusion après le GO

Dans le dépôt ERP, récupérer les références distantes et vérifier que le SHA de `origin/feature/parite` correspond au SHA approuvé. Mettre `main` à jour en avance rapide, puis y intégrer la parité de la même manière.

```bash
sha_approuve='<SHA ERP approuvé par le manager>'
git fetch origin &&
  test "$(git rev-parse origin/feature/parite)" = "$sha_approuve" &&
  git switch main &&
  git merge --ff-only origin/main &&
  git merge --ff-only origin/feature/parite &&
  test "$(git rev-parse HEAD)" = "$sha_approuve" &&
  git push origin main
```

Si une avance rapide échoue ou que le SHA final diffère de celui approuvé, arrêter pour réconcilier les branches. Sur le VPS, l’installation utilise ce SHA exact.

## Préparer et sauvegarder

1. Depuis le SHA mobile approuvé, exécuter dans `sgt_mobile/app` :

   ```bash
   npm ci
   sortie_pwa=$(mktemp -d)
   SGT_PWA_OUTPUT_DIR="$sortie_pwa" SGT_MOBILE_ENV=production EXPO_NO_DOTENV=1 EXPO_PUBLIC_SGT_API_URL=https://sgt.kaytechnologie.online/api/v1 npm run export:pwa
   ```

   Fournir `SGT_VAPID_PUBLIC_KEY` si les notifications web sont configurées. Expo nettoie le dossier de sortie avant l’export. Le dossier temporaire dédié évite d’effacer une recette dans `app/dist`. Vérifier `$sortie_pwa/manifest.webmanifest` (nom **SGT KayTech**, chemin et portée `/app/`) et préparer le répertoire complet pour publication, fichiers de service worker et ressources compris.

2. Sur le VPS, dans `/var/www/erp-sgt`, relever le SHA servi et créer l’étiquette `git tag -a avant-parite -m "Version web avant Parité"` sur ce SHA. Si l’étiquette existe, contrôler qu’elle désigne bien la version actuellement servie. Sauvegarder la PWA publique active séparément pour pouvoir rétablir ses ressources et son service worker.
3. Mettre l’application en maintenance avec `php8.3 artisan down` et arrêter le worker avec `sudo systemctl stop sgt-queue`. Créer une sauvegarde MySQL avec un fichier de connexion protégé, hors racine web. Utiliser un chemin unique, vérifier le code de sortie, la présence de données et la somme SHA-256 :

   ```bash
   umask 077
   dump="/var/backups/sgt/avant-parite-$(date +%Y%m%dT%H%M%S).sql"
   mysqldump --defaults-extra-file=/chemin/protege/mysql-sgt.cnf --single-transaction --no-tablespaces --routines --triggers --events erp_sgt > "$dump" &&
     test -s "$dump" && sha256sum "$dump"
   ```

## Installer la version approuvée

1. Dans `/var/www/erp-sgt`, renseigner `sha_approuve` avec le SHA approuvé, exécuter `git fetch origin`, vérifier que le SHA approuvé appartient à `origin/main` avec `git merge-base --is-ancestor "$sha_approuve" origin/main`, puis comparer les commits avec `git diff --stat avant-parite "$sha_approuve"`. Avant la bascule, exécuter `git status --short` et `git config --get core.fileMode` (clé absente, Git utilise sa valeur par défaut). Si des `.gitignore` apparaissent modifiés, consulter `git diff --summary` et `git diff -- .gitignore ':(glob)**/.gitignore'`. Si les différences sont uniquement des changements de mode, rétablir le bit exécutable enregistré par Git. Pour un `.gitignore` suivi en mode `100644`, retirer le bit exécutable avec `chmod a-x` sur le fichier concerné. Lorsque le système de fichiers rend les modes non fiables, utiliser `git config --local core.fileMode false` après avoir vérifié l’absence de modifications de contenu. Relancer `git status --short` et arrêter si des modifications restent à traiter. Activer ensuite le SHA avec `git switch --detach "$sha_approuve"` et vérifier `git rev-parse HEAD`. Le périmètre web approuvé au CP-FINAL (`6cd596b`) compte **31 fichiers**, à contrôler avec `git diff --stat avant-parite 6cd596b`. Le FINAL-bis ajoute `deploy-vps.sh` à cette liste et modifie la procédure déjà comptée. Le diff final doit donc compter **32 fichiers**. Tout autre écart doit être expliqué avant installation.
2. Exécuter `php8.3 "$(command -v composer)" install --no-dev --optimize-autoloader --no-interaction`. Conserver les paramètres du VPS et vérifier `APP_DEBUG=false`, `APP_ENV=production` et `APP_URL=https://sgt.kaytechnologie.online`. **Ajouter `BCRYPT_LIMIT=72` dans `.env`** si la clé est absente ou remplacer sa valeur si elle est déjà présente. Garder une seule définition.
3. Exécuter `php8.3 artisan config:clear`, puis **`php8.3 artisan migrate --force` avant le rechargement PHP-FPM**. Vérifier `php8.3 artisan migrate:status`.
4. Reconstruire les caches avec `php8.3 artisan config:cache`, `php8.3 artisan route:cache` et `php8.3 artisan view:cache`. Recharger PHP-FPM avec `sudo systemctl reload php8.3-fpm` pour renouveler les processus et leur OPcache. Vérifier l’état du service.
5. Basculer le dossier exporté sur `/var/www/sgt-pwa/app`, servi sur `/app/` à la même origine que l’API. Vérifier la configuration Nginx et l’absence de mise en cache longue pour `index.html`, le manifest et le service worker. Relancer le worker avec `sudo systemctl start sgt-queue` et vérifier `sudo systemctl is-active sgt-queue`. Remettre l’application en ligne avec `php8.3 artisan up`.

## Vérifier puis diffuser

- Vérifier `/up`, la connexion web, le tableau de bord et l’accès aux membres et sites avec un compte Manager. Vérifier le 403 avec un compte Technicien.
- Sur la PWA, vérifier le manifest, la navigation et le renouvellement du service worker depuis une installation existante. Tester une action hors ligne suivie d’une reconnexion avec des données de recette dédiées.
- Avec un compte de contrôle, vérifier qu’un changement de mot de passe invalide la seconde session web, le jeton mobile et le renouvellement OAuth du connecteur Claude. Vérifier la reconnexion et les notifications si elles sont configurées.
- Consulter les journaux PHP-FPM, Laravel et du worker. Le manager signe et vérifie l’APK avant diffusion. Publier l’annonce ci-dessous après validation.

## Preuve des notifications réelles avant diffusion

La preuve FCM sur téléphone est obligatoire pour diffuser l’APK. Le manager signe le candidat construit avec le fichier Firebase client externe du paquet `online.kaytechnologie.sgt`. Réaliser la recette restreinte sur un téléphone et un compte Manager de contrôle après autorisation d’agir sur le serveur ciblé. Le transport FCM du serveur doit correspondre au projet de l’APK. Vérifier que `sgt-queue` est actif.

| Action | Résultat attendu |
|---|---|
| Dans **Mon profil → Appareils connectés**, choisir **Activer les notifications sur cet appareil**, puis accepter la permission système. | L’appareil est visible dans le profil. |
| Mettre l’app en arrière-plan. Depuis le même compte Manager, émettre un ordre de test sur une tâche dédiée. Faire prendre l’ordre et rendre un compte rendu de test par un agent de recette. | L’ordre passe par `a_executer` et `pris`, puis atteint `fait`. Le compte rendu déclenche l’envoi. |
| Observer le volet des notifications du téléphone. | La notification système affiche **Ordre IA traité** et **Un compte rendu est disponible.** |
| Choisir **Retirer l’appareil** depuis le profil et refaire l’essai avec un nouvel ordre. | L’appareil disparaît du profil et le nouvel ordre ne déclenche aucune notification sur ce téléphone pendant la période d’observation. |

Pour Web Push, si VAPID est configuré, exporter la PWA avec la clé VAPID publique et ouvrir la PWA via HTTPS avec son service worker actif. Sur iPhone, l’ouvrir depuis l’écran d’accueil. Refaire les quatre étapes. La même notification doit apparaître dans le système, puis cesser après le retrait de l’abonnement.

Variables serveur déclarées dans `config/push.php` :

- File et worker · `SGT_PUSH_QUEUE_CONNECTION`
- FCM · `SGT_FCM_PROJECT`, `GOOGLE_APPLICATION_CREDENTIALS`
- Web Push · `SGT_VAPID_SUBJECT`, `SGT_VAPID_PUBLIC_KEY`, `SGT_VAPID_PRIVATE_KEY`

Consigner l’empreinte de l’APK testée, l’heure, le transport et le résultat sans exposer de jeton ni de clé. Tant que la réception FCM réelle n’est pas prouvée, la diffusion de l’APK reste suspendue.

## Retour arrière

Remettre en maintenance avec `php8.3 artisan down`, arrêter `sgt-queue`, réactiver le code de l’étiquette `avant-parite` et restaurer la PWA précédente dans `/var/www/sgt-pwa/app`. Réinstaller ses dépendances, reconstruire les caches, recharger PHP-FPM, puis démarrer `sgt-queue`, remettre l’application en ligne avec `php8.3 artisan up` et relancer les contrôles de fumée. **Conserver les migrations en place**. La sauvegarde MySQL est destinée à une restauration de secours contrôlée, qui doit tenir compte des écritures intervenues depuis la sauvegarde. Garder la diffusion de l’APK suspendue si la version web et la PWA doivent être rétablies.

## Annonce aux utilisateurs

**SGT KayTech se met à jour.** Retrouvez sur votre téléphone les mêmes rubriques que sur le web et continuez à mettre à jour vos tâches pendant une coupure de connexion.

Accédez au SGT depuis votre navigateur : https://sgt.kaytechnologie.online/app/ — ou depuis l'application SGT KayTech installée sur votre téléphone.

À chaque changement de mot de passe, vos sessions sont fermées sur tous vos appareils et votre connecteur Claude est déconnecté. Pour reprendre votre travail, reconnectez-vous au SGT avec votre identifiant et votre nouveau mot de passe. Si vous utilisez le connecteur Claude, reconnectez-le et autorisez de nouveau son accès au SGT.
