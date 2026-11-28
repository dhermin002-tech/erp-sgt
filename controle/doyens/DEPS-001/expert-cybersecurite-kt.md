# Expert Cybersécurité KT — DEPS-001 — 28/11/2026

**Gate** : G2 (consultatif) — commit `b5b8613` (parent `3e22519` = production)
**Verdict** : Validé sous réserve ⚠️ (avis : **favorable avec réserves**)
**Score** : 92/100
**Décision GO / NO-GO : manager du projet.**

Périmètre vérifié par moi-même : `git diff --stat 3e22519 b5b8613` (seul `composer.lock`, 793+/657-), `composer audit --locked`, `composer validate`, comparaison avis/versions, config d'authentification, `bootstrap/app.php`, `deps001.sh`, suite de tests locale. Non vérifié : configuration Nginx de production (server_name / default_server), état réel du VPS, `~/.my.cnf` root.

## 🔴 Bloquants
Aucun.

## 🟡 Importants
1. **Retour arrière incomplet sur le manifeste des paquets** — `deps001.sh`, fonction `retour()` : le `vendor` est restauré, mais pas `bootstrap/cache/packages.php` ni `services.php`, régénérés par `composer install` (script `post-autoload-dump` → `artisan package:discover`, `composer.json:55-57`). Risque faible ici (aucun fournisseur de service ajouté ou retiré : seul paquet nouveau `symfony/polyfill-time`), mais à corriger pour réutiliser le script. Correction : dans `retour()`, après la copie du vendor, ajouter `rm -f bootstrap/cache/packages.php bootstrap/cache/services.php; php8.3 artisan package:discover >/dev/null || true`.
2. **Robustesse du gestionnaire `retour()`** — il s'exécute sous `set -e` : si `config:cache` échoue pendant le retour, la fonction s'interrompt et le site reste en maintenance. Correction : `set +e` en première ligne de `retour()`.
3. **Retour arrière sans restauration de base** — `migrate --force` est exécuté mais `retour()` ne restaure pas le dump. Acceptable pour ce lot (aucune migration ajoutée : le diff ne touche que `composer.lock`), le dump `avant-deps001-$TS.sql` permet une restauration manuelle. À documenter dans la procédure.

## 🟢 Améliorations
- Rotation des sauvegardes `/var/backups/sgt` (dump avec données personnelles + copie de vendor ~ dizaines de Mo à chaque passage) : purge au-delà de N jours.
- `composer install` tourne en root (`COMPOSER_ALLOW_SUPERUSER=1`) avec scripts : pratique déjà en place, risque de chaîne d'approvisionnement limité aux paquets du lock ; à terme, exécuter sous l'utilisateur de déploiement.
- Programmer un `composer audit --locked` hebdomadaire (cron ou CI) : les avis Symfony du 25/11 montrent que le délai d'apparition est court.
- Vérifier une fois la config Nginx de production (`default_server` qui refuse les Host inconnus) : les avis Symfony « URL vers un hôte choisi par le client » dépendaient de ce point (aucun `URL::forceRootUrl` dans `app/`).

## Points validés (preuves)
**(1) Avis critique TokenGuard — SGT n'était pas exposé.**
- `config/auth.php:40-45` : un seul guard, `web`, driver `session`. Aucun driver `token`, aucun `auth:api` (grep `app config routes bootstrap` : 0 occurrence de `TokenGuard`, `auth:api`, `viaRequest`, `Auth::extend`, `api_token`).
- API : `routes/api.php:33` → `auth:sanctum`. Sanctum utilise son propre `Laravel\Sanctum\Guard` (enregistré dans `vendor/laravel/sanctum/src/SanctumServiceProvider.php:24-27`), qui lit `$request->bearerToken()` (`Guard.php:89`, toujours une chaîne) — pas `Illuminate\Auth\TokenGuard`.
- Le mécanisme voisin (tableau passé au fournisseur Eloquent → `whereIn`, `EloquentUserProvider.php:129-130`) est neutralisé : `LoginController.php:19-21` et `ApiTokenController.php:17-20` valident `username`/`password` en `string`.
- Correctif présent dans la version installée : `vendor/laravel/framework/src/Illuminate/Auth/TokenGuard.php:83,113` (contrôles `is_string`), `Application::VERSION = '13.44.1'`.
- **Message au manager** : exposition passée à cet avis = nulle (code vulnérable non utilisé). Aucune action de réponse à incident requise.

**(2) Avis élevés — tous corrigés par les versions du nouveau lock** (affectedVersions de `audit-avant.json` vs `b5b8613:composer.lock`) :
| Paquet | Avant → Après | Borne haute vulnérable | Avis élevés |
|---|---|---|---|
| laravel/framework (critique) | v13.14.0 → v13.44.1 | <13.27.0 | 1 |
| guzzlehttp/guzzle | 7.11.0 → 7.17.0 | <7.15.2 | 1 |
| league/commonmark | 2.8.2 → 2.10.10 | <=2.10.1 | 9 |
| symfony/http-foundation | v7.4.13 → v7.4.25 | <7.4.19 (ReDoS) / <7.4.23 | 1 |
Les avis moyens/faibles (guzzle, psr7 2.13.6, mime 7.4.24, mailer 7.4.24, routing 7.4.24, http-kernel 7.4.25, flysystem 3.36.0) sont aussi au-delà de leurs bornes.
Exposition passée complémentaire : `league/commonmark` non utilisé par l'application (aucun `markdown`/`CommonMark` dans `app/` et `resources/views/`) ; Guzzle utilisé seulement vers un hôte fixe avec `withoutRedirecting()` (`app/Services/TransportPushHttp.php:59`).
- Contre-audit indépendant : `composer audit --locked` sur `b5b8613` → « No security vulnerability advisories found. » (code de sortie 0) ; `composer validate` → valide (lock cohérent avec `composer.json`, inchangé).
- Liste des paquets : 120 → 121, seul ajout `symfony/polyfill-time` v1.42.0 (source `github.com/symfony/polyfill-time`, dépendance de `nesbot/carbon`) ; aucun paquet retiré, aucune source hors GitHub/Packagist.

**(3) Script `deps001.sh`**
- `umask 077` (l.5) → dump SQL créé en 600 ; `mkdir -p $B && chmod 700 $B` (l.17) → répertoire 700.
- Aucun secret affiché : nom de base lu dans `.env` sans être imprimé (l.19), `mysqldump` sans `-p` en ligne de commande (pas de mot de passe visible dans `ps`) ; sortie limitée à taille, nombre de tables, empreinte sha256.
- Garde-fous avant action : HEAD attendu, arbre propre, SHA présent sur `origin/main`, tag `avant-deps001`, dump vérifié (« Dump completed »).
- Retour automatique `trap retour ERR` couvrant installation, migrations, caches et tests de fumée (`/up`, `/login`, `/app/` = 200 ; `/api/v1/me` et `/api/v1/sites` sans jeton = 401) ; `.env` remis en 640.

**(4) TRUSTED_PROXIES / avis « private_ranges »** — non concerné : aucun `trustProxies()` dans `bootstrap/app.php`, aucune variable `TRUSTED_PROXIES` dans `app/`, `config/`, `bootstrap/`, `.env.example` ; `TrustProxies` n'a pas de lecture d'environnement (`vendor/.../Http/Middleware/TrustProxies.php:15,166`) → aucun proxy approuvé, le raccourci `private_ranges` n'est jamais utilisé. PHP-FPM derrière Nginx local reçoit l'IP réelle dans `REMOTE_ADDR`.

**Tests** : ma propre exécution de `php artisan test` (SQLite en mémoire, `phpunit.xml:26-27`) n'a rien affiché : elle s'est terminée sans résultat exploitable, probablement coupée par la limite de 580 s, et le code 0 est celui de `tail`, pas celui de PHPUnit. Ce contrôle n'est donc pas concluant. Le score suppose que le résultat annoncé par l'exécutant (207/207) est vrai : à confirmer avant mise en production.

## Risque résiduel
Mise à jour mineure de 30 versions de Laravel (13.14 → 13.44) : risque fonctionnel couvert par les tests, pas par une recette manuelle. Les avis publiés après le 28/11 ne sont évidemment pas couverts.

*— Expert Cybersécurité KT*
