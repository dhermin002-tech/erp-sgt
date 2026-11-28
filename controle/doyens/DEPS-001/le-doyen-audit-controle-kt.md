# Le Doyen Audit & Contrôle KT — DEPS-001 — 28/11/2026

Verdict : Validé sous réserve ⚠️
Score : 86/100

Périmètre : commit b5b8613 (parent 3e22519 = production), dépôt local c:/laragon/www/erp.sgt, et revue à froid de controle/doyens/DEPS-001/deps001.sh.
Mode : lecture seule. Tests lancés en local uniquement (SQLite en mémoire). Aucun SSH, aucune action en production, .env non lu.
PHP local : 8.3.30 (Win32). Composer 2.9.4.

## 🔴 Bloquants

Aucun.

## 🟡 Importants

**AUD-001 — Le retour arrière de deps001.sh s'arrête à la première erreur et peut laisser le site en maintenance.**
- Preuve : le script tourne sous `set -euo pipefail` et la fonction `retour()` ne désactive pas `errexit`. J'ai reproduit le cas en local :
  `set -euo pipefail; retour(){ echo "debut retour"; false; echo "suite retour"; }; trap retour ERR; false`
  → la sortie est seulement `debut retour`, code 1 : la suite de `retour` n'est jamais exécutée.
- Dans `retour()`, ces commandes ne sont pas protégées : `cd`, `cp -a` (dernier maillon du `&&`), les trois `artisan *:cache`, `chown`, `chmod`, `systemctl reload php8.3-fpm`. Si l'une échoue, le script sort sans `artisan up` ni `systemctl start sgt-queue`. Le site reste alors en 503 (`artisan down`) et la file d'attente reste arrêtée, jusqu'à une intervention manuelle en SSH.
- Impact : arrêt de SGT (web, PWA, API mobile, connecteur) pendant la durée de l'intervention. Probabilité faible, puisque `retour` remet le vendor d'origine.
- Correctif proposé (2 lignes, à faire avant l'exécution) : première ligne de `retour()` : `set +e; trap - ERR`. Ajouter aussi `php8.3 artisan package:discover >/dev/null || true` après la restauration du vendor, pour régénérer bootstrap/cache/packages.php et services.php, que `composer install` a réécrits à partir du nouveau vendor.
- Effort : 5 minutes. Coût du risque : non chiffré, faute de données réelles sur le coût d'une heure d'arrêt de SGT.

**AUD-002 — L'affirmation A2 est exacte pour les paquets directs, mais trois paquets indirects changent de version majeure sans que ce soit déclaré.**
- Preuve : comparaison programmatique des deux composer.lock (3e22519 puis b5b8613) :
  - `brick/math 0.14.8 → 1.0.0` (production : requis par laravel/framework, ramsey/uuid, web-token/jwt-library et spomky-labs/pki-framework, donc par la chaîne Web Push) ;
  - `guzzlehttp/uri-template 1.0.6 → 2.0.1` (production, via laravel/framework) ;
  - `hamcrest/hamcrest-php 2.1.1 → 3.0.0` (dev uniquement, via mockery).
- Toutes ces contraintes acceptent officiellement la nouvelle version majeure (`^1.0`, `^2.0`, `^3.0`) ; aucun code de app/, tests/, config/, routes/ ou database/ n'appelle directement ces bibliothèques (grep vide).
- Contrôle complémentaire que j'ai fait : signature VAPID (JWT ES256) et chiffrement aes128gcm de minishlink/web-push exécutés en local avec brick/math 1.0.0. Résultat : `vapid_headers=Authorization`, `chiffrement=OK`. Un vrai envoi push vers un appareil n'a pas été mesuré.
- Recommandation : déclarer ces 3 changements majeurs dans le commit ou dans REVIEW.md, puis faire un essai réel de notification push juste après la mise en production.

## 🟢 Améliorations

- AUD-003 : les contrôles de fumée de deps001.sh ne testent que des codes HTTP sans authentification (/up, /login, /app/, 401 sur l'API). Il manque un appel API authentifié et une vérification que la file sgt-queue traite bien un job après le redémarrage.
- AUD-004 : ajouter `composer audit --locked --no-dev` sur le VPS après `composer install`, pour prouver « 0 avis » sur la cible elle-même.
- AUD-005 : sous Windows, la génération de clés EC échoue sans `OPENSSL_CONF` (erreur « configuration file routines::no such file »). C'est un défaut de l'environnement local, pas une régression, mais il fausse tout essai local de Web Push. À documenter dans le README.
- AUD-006 : 12 des 42 avis du fichier audit-avant.json n'ont pas de gravité renseignée. Le décompte « 1 critique, 11 élevés » se limite donc aux avis dont la gravité est connue. Le fichier commence aussi par un BOM UTF-8, qui empêche `json_decode` de le lire directement.
- AUD-007 : après le script, la production reste en HEAD détaché sur b5b8613. C'est cohérent avec le contrôle d'entrée, mais il faut le savoir pour les prochains déploiements.

## Points validés

| Affirmation | Statut | Preuve |
|---|---|---|
| A1 — 42 avis avant (1 critique, 11 élevés), 0 après | **vérifié** | J'ai refait l'audit de manière indépendante : le composer.lock de 3e22519, mis dans un dossier temporaire, donne 42 avis (critical 1, high 11, medium 15, low 3, sans gravité 12), soit les mêmes nombres que audit-avant.json. Sur HEAD b5b8613, `composer audit --locked` et `composer audit` affichent « No security vulnerability advisories found. », code 0. L'avis critique concernait laravel/framework ; les avis élevés concernaient guzzle, league/commonmark (×9) et symfony/http-foundation. |
| A2 — framework 13.14.0 → 13.44.1, aucun changement majeur d'un paquet direct | **vérifié, mais incomplet** | 11 paquets directs changent, tous en version mineure ou corrective (framework 13.44.1, sanctum 4.5.0, tinker 3.2.0, phpunit 12.5.45…). composer.json n'est pas modifié (content-hash identique). 3 paquets indirects changent de version majeure : voir AUD-002. 1 paquet est ajouté (symfony/polyfill-time). |
| A3 — Suite complète 207/207, 977 assertions | **vérifié** | Relancée par moi : `{"result":"passed","tests":207,"passed":207,"assertions":977}`, exit=0. J'ai aussi lancé les tests ciblés Auth/API (12 classes : Abilities, Login, OAuthSgt, Commentaire, AppareilPush, Analyse, MobileSgt, ModifierTache, OrdreIa, Parite, Performance, ReferentielSites) : 66/66 réussis, 568 assertions, exit=0. |
| A4 — `composer install --dry-run` : rien à installer | **vérifié** | « Nothing to install, update or remove », exit=0. `composer validate` : « ./composer.json is valid », exit=0. |
| A5 — Compatibilité php8.3 du VPS (16/16) | **non mesuré par l'audit** | En local, `check-platform-reqs --lock` donne 19 lignes en « success » sur PHP 8.3.30, exit=0 (le lock exige `php ^8.3`). La version mineure et les extensions du PHP du VPS ne sont pas vérifiables sans SSH. |
| A6 — Retour automatique à 3e22519 en cas d'échec | **vérifié sous réserve** | Le `trap retour ERR` se déclenche bien sur l'échec d'une étape ou d'un contrôle de fumée : test local d'une boucle `[ "$c" = 200 ]` qui échoue, le piège est appelé. En revanche, le retour lui-même n'est pas robuste : voir AUD-001. Contrôles d'entrée présents et corrects : HEAD = 3e22519, arbre propre, b5b8613 présent dans origin/main (vérifié en local : main = origin/main = b5b8613), étiquette avant-deps001, sauvegarde du vendor et de la base avec contrôle « Dump completed ». |

Le diff ne touche que composer.lock (793 lignes ajoutées, 657 supprimées). Aucune migration, donc la base n'a pas besoin d'être restaurée en cas de retour.

Mon verdict d'Expert-AUDIT-TECH-35 : livraison saine sur le fond. J'ai prouvé moi-même « 0 avis » et la suite complète au vert. GO technique recommandé, à condition de corriger AUD-001 dans deps001.sh avant l'exécution (`set +e; trap - ERR` en tête de `retour`) et de faire un essai réel de notification push après la mise en production. Avis consultatif : la décision GO / NO-GO appartient au manager.
