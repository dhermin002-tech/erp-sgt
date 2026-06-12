# Rapport Expert-KT — Corrections SGT du 2026-06-11

## Contexte

Demande utilisateur : appliquer les corrections prioritaires issues de l'analyse Expert-KT / Doyen-KT sur le projet SGT, puis laisser un rapport de reprise pour qu'un agent Claude Code `expert-kt` puisse comprendre le travail effectue et continuer si necessaire.

Projet audite : `C:/laragon/www/erp.sgt`  
Branche observee : `main`  
Dernier commit observe avant modifications : `3bfbb74 feat(membres): tri par grade hierarchique + separateurs de groupe + indicateur activite agents IA`

## Objectif du lot

Traiter les corrections immediates du plan "7 jours" sans engager les chantiers lourds :

- Corriger le controle d'acces sur `ActionSuiviController::toggle`.
- Ajouter les tests de non-regression pour les actions de suivi.
- Ajouter une CSP progressive en mode `Report-Only`.
- Durcir les valeurs de securite par defaut dans `.env.example`.
- Aligner la documentation avec la version Laravel declaree par Composer.

Les chantiers non traites volontairement dans ce lot :

- Pagination API des taches.
- Durcissement complet du serveur MCP HTTP.
- Journal d'activite metier.
- Scopes Sanctum fins par type d'action.
- Lancement Sprint 1 SGT Mobile.

## Fichiers modifies

### `app/Http/Controllers/ActionSuiviController.php`

Correction appliquee dans `toggle()` :

- Ajout d'un controle d'acces via `Auth::user()->canAccessTache($actionSuivi->tache)`.
- Un utilisateur non manager/non createur/non assigne a la tache parente recoit maintenant un `403`.

Raison :

- L'audit Expert-KT avait identifie un IDOR : un utilisateur authentifie pouvait changer l'etat d'une action de suivi sans verifier son acces a la tache.

### `tests/Feature/AccessControlTest.php`

Tests ajoutes :

- `test_outsider_ne_peut_pas_creer_action_suivi`
- `test_assignee_peut_creer_action_suivi`
- `test_outsider_ne_peut_pas_toggle_action_suivi`
- `test_assignee_peut_toggle_action_suivi`

Ajout aussi d'un helper `actionSuivi()` pour creer une action de test rattachee a la tache principale.

Raison :

- Les tests couvraient deja sous-taches, commentaires et rapports, mais pas les actions de suivi.

### `app/Http/Middleware/SecureApiHeaders.php`

Ajout du header :

```http
Content-Security-Policy-Report-Only
```

Politique actuelle :

```text
default-src 'self';
script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net;
style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net;
img-src 'self' data: blob:;
font-src 'self' data: https://fonts.gstatic.com https://cdn.jsdelivr.net;
connect-src 'self';
frame-ancestors 'none';
base-uri 'self';
form-action 'self'
```

Raison :

- `SEC-007` signalait l'absence de CSP.
- Le mode `Report-Only` est volontaire pour eviter de casser l'interface en production avant observation des violations.

Suite recommandee :

1. Observer les violations CSP en production/staging.
2. Retirer progressivement les `unsafe-inline` si possible.
3. Passer de `Content-Security-Policy-Report-Only` a `Content-Security-Policy`.

### `.env.example`

Changements :

- `APP_NAME="SGT KayTechnologie"`
- `APP_DEBUG=false`
- `APP_URL=http://erp.sgt.test`
- `SESSION_ENCRYPT=true`

Raison :

- `SEC-009` signalait que `.env.example` poussait vers une configuration dangereuse par defaut.
- Ces valeurs reduisent le risque de mauvaise copie vers staging/prod.

### `README.md`

Changement :

- Backend documente : `Laravel 13 · PHP 8.3`

Raison :

- `composer.json` declare `laravel/framework: ^13.8`, alors que le README indiquait Laravel 11.

### `FICHE_TECHNIQUE.md`

Changement :

- Backend documente : Laravel `13`

Raison :

- Alignement avec `composer.json`.

## Verification effectuee

Commande lancee :

```powershell
C:\Windows\System32\WindowsPowerShell\v1.0\powershell.exe -Command "& 'C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe' artisan test"
```

Resultat :

```text
111 tests passes
203 assertions
```

Avant ce lot, la suite etait a `107 tests`. Les 4 tests ajoutes passent.

## Decisions techniques

- Le controle `actions.toggle` autorise tout utilisateur ayant acces a la tache parente, pas seulement le createur de l'action.
- Cette decision est coherente avec la logique collaborative SGT : une action de suivi appartient au travail de la tache, tandis que la suppression reste plus restrictive.
- La CSP est en `Report-Only` pour eviter une rupture visuelle/fonctionnelle immediate.
- La documentation est alignee sur Composer sans modifier les dependances.

## Risques restants

### P1/P2 — API taches sans pagination

`TacheApiController::index()` retourne toutes les taches visibles. Correct pour petit volume, fragile pour mobile + MCP + historique.

Prochaine action :

- Ajouter `per_page`, pagination JSON, filtres date et meta de pagination.

### P2 — MCP HTTP a durcir avant exposition publique

`sgt-mcp-server/server-http.js` utilise tokens statiques et auto-approval OAuth en environnement HTTP.

Prochaine action :

- Restreindre par VPN/IP allowlist/reverse proxy.
- Ajouter rate limit.
- Journaliser les actions MCP sensibles.
- Prevoir rotation des tokens.

### P2 — Journal d'activite metier absent/partiel

SGT devrait tracer :

- Creation tache.
- Changement statut.
- Assignation/reassignation.
- Suppression/restauration.
- Changement priorite/progression.
- Actions agents IA.

Prochaine action :

- Creer table `activity_logs` ou utiliser un package type activitylog si accepte par le projet.

### P2 — CSP a rendre bloquante apres observation

Prochaine action :

- Collecter violations.
- Reduire `unsafe-inline`.
- Passer en CSP bloquante.

### P3 — BUGS.md non mis a jour dans ce lot

Note :

- Une tentative de lecture ciblee de `BUGS.md` a ete refusee par l'environnement d'approbation.
- Les corrections correspondant a `SEC-007` et `SEC-009` sont appliquees dans le code/config, mais `BUGS.md` peut encore afficher ces items comme ouverts.

Prochaine action :

- Mettre a jour `BUGS.md` :
  - `SEC-007` : corrige partiellement, CSP Report-Only ajoutee.
  - `SEC-009` : corrige, `.env.example` durci.
  - Ajouter eventuellement une entree `SEC-011` : IDOR actions de suivi corrige.

## Prochaine reprise recommandee pour Claude Code

1. Lire ce rapport.
2. Verifier le diff courant :

```bash
git diff
```

3. Relancer les tests :

```bash
php artisan test
```

ou sous Laragon Windows :

```powershell
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan test
```

4. Mettre a jour `BUGS.md`.
5. Commit recommande :

```bash
git add .env.example README.md FICHE_TECHNIQUE.md app/Http/Controllers/ActionSuiviController.php app/Http/Middleware/SecureApiHeaders.php tests/Feature/AccessControlTest.php .claude-agents/RAPPORT_EXPERT_KT_2026-06-11.md BUGS.md
git commit -m "security(sgt): verrouille actions suivi et durcit configuration"
```

6. Apres validation, deployer :

```bash
cd /var/www/erp-sgt
git pull origin main
php artisan test
php artisan config:cache
php artisan route:cache
php artisan view:clear
```

## Etat final du lot

Statut : termine avec tests OK.  
Niveau de risque restant : warning, car pagination API/MCP/journal activite restent a traiter dans un lot suivant.
