# Le Doyen KT — DEPS-001 — 28/11/2026

Verdict : Corrections demandées ❌ (le `composer.lock` est validé ; c'est le script `deps001.sh` qui doit être corrigé avant tout passage en production)
Score : 74/100

Périmètre contrôlé : commit b5b8613 (parent 3e22519), `controle/doyens/DEPS-001/{audit-avant.json, audit-apres.json, deps001.sh}`, `vendor/` local. Lecture seule. Aucune action en production, aucun secret lu.

---

## 🔴 Bloquants

### B1 — Le retour arrière s'arrête à la première commande en échec (`set -e` actif dans `retour()`)
- **Constat** : le script est en `set -euo pipefail` (deps001.sh:4) et la fonction `retour()` (deps001.sh:27-37) n'annule pas `errexit`. Dans bash, `errexit` reste actif dans un gestionnaire `trap ERR` : la première commande qui échoue dans `retour()` sans `|| true` (`rm -rf vendor && cp -a …` l.31, les trois `artisan …:cache` l.32, `chown` l.33, `systemctl reload` l.34) **termine le script au milieu du retour**. Le site reste alors en maintenance (`artisan down` l.40), le worker `sgt-queue` arrêté (l.41), éventuellement avec un `vendor/` supprimé.
- **Preuve** (reproduction locale, scratchpad `t.sh`) :
  ```
  set -euo pipefail
  retour(){ echo "retour debut"; false; echo "retour suite (cache, fpm, queue, up)"; }
  trap retour ERR
  echo avant; false; echo jamais
  → sortie : « avant », « retour debut », code=1 — la suite du retour n'est jamais exécutée.
  ```
  Or le cas le plus probable de déclenchement du retour est justement un échec d'`artisan` (démarrage du framework) : c'est exactement là que `config:cache` dans `retour()` risque d'échouer aussi.
- **Correction exacte** : en tête de `retour()` :
  ```bash
  retour() {
    set +e; trap - ERR
    echo "!!! ÉCHEC — retour à $ANCIEN"
    cd /var/www/erp-sgt || exit 1
    git switch -q --detach "$ANCIEN" || echo "!!! git switch KO — intervention manuelle"
    [ -d vendor ] && mv vendor "$B/vendor-echec-deps001-$TS"
    cp -a "$B/vendor-avant-deps001-$TS" vendor || echo "!!! restauration vendor KO"
    php8.3 artisan package:discover --ansi >/dev/null
    php8.3 artisan config:cache >/dev/null; php8.3 artisan route:cache >/dev/null; php8.3 artisan view:cache >/dev/null
    chown -R www-data:www-data /var/www/erp-sgt; chmod 640 .env
    systemctl reload php8.3-fpm; systemctl start sgt-queue
    php8.3 artisan up
    echo "Retour effectué. HEAD=$(git rev-parse --short HEAD) · queue=$(systemctl is-active sgt-queue)"
    exit 1
  }
  ```
  (`mv` au lieu de `rm -rf` : on ne détruit jamais l'arbre en échec avant d'avoir la copie saine en place ; `exit 1` explicite pour signaler l'échec à l'opérateur.)

---

## 🟡 Importants

### I1 — `migrate --force` appelé sans garde, alors qu'aucune migration n'est livrée et que le retour ne touche pas la base
- **Constat** : `git diff --stat 3e22519 b5b8613` → `composer.lock | 1450 +++---` (seul fichier). Aucun paquet n'auto-charge de migrations (`grep -rn loadMigrationsFrom vendor/laravel/sanctum/src` → vide). Donc `migrate --force` (deps001.sh:46) n'a rien à faire… **sauf** si la production porte des migrations en attente d'un autre lot : elles seraient alors exécutées hors périmètre DEPS-001 et **non annulées** par `retour()`. La ligne 45 (`migrate:status | grep -c Ran >/dev/null`) ne vérifie rien d'utile (elle échoue seulement si zéro migration n'a jamais tourné).
- **Correction exacte** : remplacer les lignes 45-46 par une garde stricte, sans migration :
  ```bash
  php8.3 artisan migrate:status --pending --no-interaction | grep -q "Pending" && { echo "ERREUR: migrations en attente hors DEPS-001"; false; }
  ```
  (à placer **avant** `artisan down`, avec les autres pré-contrôles, pour ne pas couper le service pour rien) et supprimer `migrate --force`.

### I2 — Le worker `sgt-queue` n'est pas contrôlé avant la levée du filet de sécurité
- **Constat** : `systemctl start sgt-queue` (l.51) rend la main dès le lancement ; `systemctl is-active` n'est lu qu'**après** `trap - ERR` (l.61-63), en simple affichage. Or c'est le worker qui exécute le chemin réel des notifications (FCM via google/auth + Guzzle, Web Push via web-token + brick/math), chemin **non couvert par la suite** : `tests/Feature/Notifications/AppareilPushApiTest.php:106` remplace `TransportPush` par un mock.
- **Correction exacte** : avant `trap - ERR`, ajouter
  `sleep 5; [ "$(systemctl is-active sgt-queue)" = active ]; php8.3 artisan queue:failed 2>/dev/null | tail -3`
  puis, après succès, **une notification réelle de contrôle** (une tâche de test assignée au compte du manager, réception vérifiée sur l'APK et la PWA) — c'est la seule preuve du chemin FCM.

### I3 — Le message de commit est inexact : « Aucune version majeure changée »
- **Constat** (comparaison des deux `composer.lock`) : trois changements majeurs, dont un non annoncé au contrôle :
  - `brick/math 0.14.8 -> 1.0.0` (**non signalé**, production) ;
  - `guzzlehttp/uri-template v1.0.6 -> v2.0.1` (production) ;
  - `hamcrest/hamcrest-php v2.1.1 -> v3.0.0` (dev uniquement).
- **Évaluation du risque** : `composer why` montre que ces paquets ne sont pas utilisés directement par l'application (grep `Brick\|UriTemplate` dans app/config/routes/database → vide) ; les contraintes des dépendants acceptent les deux branches (`laravel/framework v13.44.1 requires guzzlehttp/uri-template (^1.0 || ^2.0)`, `… requires brick/math (^0.14.2 || … || ^1.0)` ; `web-token/jwt-library 4.2.8` et `ramsey/uuid 4.10.0` idem). Le risque réel est porté par `web-token/jwt-library` (signature VAPID du Web Push) → **vérifié hors ligne, voir Points validés**. Risque résiduel faible, mais la traçabilité doit être exacte.
- **Correction exacte** : ne pas réécrire l'historique de `main` ; consigner la correction dans REVIEW.md / SGT (« majeures transitives : brick/math 1.0, uri-template 2, hamcrest 3 (dev) — vérifiées ») et dans le commit de correction du script.

---

## 🟢 Améliorations

- **A1** — Vérifier l'espace disque avant `cp -a vendor` et `mysqldump` (`df -h /var/backups`) ; ajouter la purge des copies `vendor-avant-deps001-*` après 7 jours de stabilité.
- **A2** — `set -u` + `SHA="$1"` : message brut « unbound variable » si l'argument manque → `SHA="${1:?Usage: bash deps001.sh <SHA>}"`.
- **A3** — Sauvegarder aussi `bootstrap/cache/{packages,services}.php` avec le `vendor/` (la correction B1 les régénère par `package:discover`, mais la copie rend le retour indépendant de l'état de l'arbre).
- **A4** — Contrôle de fumée authentifié : les tests `/api/v1/me` et `/api/v1/sites` sans jeton (401) prouvent la garde Sanctum mais pas une réponse 200 réelle ; prévoir un jeton de test à durée courte (hors script, sans l'afficher).
- **A5** — Planifier `composer audit` hebdomadaire (cron + alerte) : 42 avis se sont accumulés en quelques mois sur un produit en production ; c'est la vraie dette révélée par DEPS-001.

---

## Points validés

1. **Objectif du manager atteint** — `audit-avant.json` (BOM UTF-8, lu après nettoyage) : 42 avis = 1 critique, 11 élevés, 15 moyens, 3 faibles, 12 sans sévérité. Le critique : `laravel/framework <13.27.0` « TokenGuard authentication bypass through non-string API credentials ». `audit-apres.json` : `"advisories": []`. **Contre-vérification indépendante** : `composer audit --locked` → « No security vulnerability advisories found. »
2. **Exposition réelle de l'avis critique faible** — `config/auth.php:42` : garde `session` (+ Sanctum), aucun garde `token`. La correction est néanmoins présente : `vendor/laravel/framework/src/Illuminate/Auth/TokenGuard.php` → `if (is_string($token) && ! empty($token))`.
3. **Le dépôt ne change que `composer.lock`** — `git diff 3e22519 b5b8613 --stat` → 1 fichier. `composer.json` inchangé (`laravel/framework ^13.8`, `sanctum ^4.3`) : montée dans les contraintes existantes. `vendor/composer/installed.json` local = versions du lock (framework v13.44.1, guzzle 7.17.0, uri-template v2.0.1, brick/math 1.0.0).
4. **Compatibilité php 8.3** — `platform: {"php":"^8.3"}` identique avant/après ; exigences PHP des paquets sensibles ≤ 8.3 (framework `^8.3`, brick/math `^8.2`, uri-template `^7.4 || ^8.0`, polyfill-time `>=8.1`, web-token `>=8.2`) ; `composer check-platform-reqs --lock --no-dev` sous PHP 8.3.30 → succès. Réserve : vérifié sur PHP Windows local, pas sur les extensions du VPS (à relancer sur le VPS en pré-contrôle, lecture seule).
5. **Chemin Web Push vérifié hors ligne avec les nouvelles versions** (scratchpad `vapid.php`, clés générées à la volée, aucun réseau) : création de clés P-256, en-tête VAPID signé (`vapid t=eyJ0…`, 342 car.), chiffrement `aes128gcm` d'une charge (3068 octets) → OK avec `brick/math 1.0.0` et `web-token/jwt-library 4.2.8`. (Échec initial « Unable to create the key » = absence d'`OPENSSL_CONF` sous Windows, sans lien avec la mise à jour.)
6. **Montée 13.14 → 13.44 (30 versions mineures)** — même branche majeure, contraintes inchangées ; la politique Laravel n'autorise pas de rupture volontaire en mineure, mais des changements de comportement ponctuels existent. Je ne cite aucun changement précis que je n'ai pas pu vérifier dans le code. Le filet est la suite (207 tests annoncés, non relancée ici sur consigne) + le contrôle ciblé ci-dessous + la notification réelle (I2).
7. **Structure du script saine** — pré-contrôles avant toute coupure (HEAD attendu, arbre propre, SHA présent dans origin/main, tag `avant-deps001`), sauvegarde vendor + base avec vérification « Dump completed » et empreinte sha256, `pipefail` qui capte un échec de `composer install` malgré le `| tail`, contrôles HTTP **avant** la levée du `trap` (un code ≠ 200/401 déclenche bien le retour : reproduit dans `t2.sh`), rechargement php-fpm (OPcache). `chown -R www-data` cohérent avec `deploy-vps.sh:68`.
8. **Règle des 3 temps**
   - *Court terme* : gain sécurité net (1 critique + 11 élevés supprimés), risque fonctionnel faible une fois B1/I1/I2 corrigés.
   - *Moyen terme* : rester dans la branche 13.x et `symfony 7.4` (LTS) est le bon choix ; aucune nouvelle dépendance directe ; `symfony/polyfill-time` est une transitive de `nesbot/carbon 3.15.1`.
   - *Long terme* : aucune prison technologique introduite ; la dette réelle est l'absence de veille `composer audit` (A5).

9. **Contrôle ciblé relancé localement** (pas la suite complète, sur consigne) : `phpunit tests/Feature/Notifications tests/Feature/Auth` → `{"result":"passed","tests":28,"passed":28,"assertions":193}` (login, OAuth SGT, abilities Sanctum, API push).
