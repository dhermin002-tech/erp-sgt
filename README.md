# SGT — Système de Gestion des Tâches
### KayTechnologie Gabon

[![Tests CI](https://github.com/dhermin002-tech/erp-sgt/actions/workflows/tests.yml/badge.svg)](https://github.com/dhermin002-tech/erp-sgt/actions/workflows/tests.yml)

Outil interne de planification, suivi et reporting des tâches quotidiennes (terrain + bureau).

## Stack
- **Backend** : Laravel 13 · PHP 8.3
- **Frontend** : Blade · Bootstrap 5 · Alpine.js · Chart.js
- **Thème** : `charte-graphique.css` — Directions A (sidebar marine) / B (nav haute)
- **BDD** : MySQL 8 (dev) · SQLite in-memory (tests)
- **Auth** : Username + RBAC 5 rôles (manager / technicien / agent / developpeur / stagiaire)
- **i18n** : Bilingue FR / EN

## Installation locale (Laragon)

```bash
git clone https://github.com/dhermin002-tech/erp-sgt c:/laragon/www/erp.sgt
cd c:/laragon/www/erp.sgt
composer install
cp .env.example .env
# Modifier .env : DB_DATABASE=erp_sgt, APP_URL=http://erp.sgt.test
php artisan key:generate
php artisan migrate:fresh --seed
```

Configurer le vhost Laragon → `http://erp.sgt.test`

## Tests

```bash
php artisan test
```

## Rôles

| Rôle | Droits |
|------|--------|
| Manager | Vue globale, gestion membres/sites, dashboard complet |
| Technicien | Ses tâches, statut, commentaires, photos |
| Agent | Ses tâches, statut, commentaires |
| Développeur | Ses tâches, sous-tâches, statut |
| Stagiaire | Ses tâches, statut (droits restreints) |

## Comptes de démo (seeder)

| Username | Mot de passe | Rôle |
|----------|-------------|------|
| admin | admin123 | Manager |
| technicien1 | tech123 | Technicien |
| agent1 | agent123 | Agent |
| dev1 | dev123 | Développeur |
| stagiaire1 | stage123 | Stagiaire |

## Déploiement production

- **URL** : `https://sgt.kaytechnologie.online`
- **VPS** : `72.61.195.69` (Ubuntu 24.04 · Nginx) — voir `pdd.md` pour le plan complet.
- **Chemin sur le serveur** : `/var/www/erp-sgt`

### Procédure de mise à jour (après un `git push` sur `main`)

```bash
ssh root@72.61.195.69
cd /var/www/erp-sgt
git pull origin main
php artisan migrate --force      # applique les nouvelles migrations sans confirmation
php artisan optimize             # reconstruit config + routes + vues + events en cache
```

> ⚠️ **Règle critique (ERR-PHP — route cache)** : toujours utiliser `php artisan optimize`
> et **jamais** `config:cache` seul. `optimize` régénère aussi le **route cache** ;
> l'oublier provoque un **500 « Route [...] not defined »** dès qu'une nouvelle route
> a été ajoutée (incident survenu 2 fois sur `/membres` et `/profil`).
>
> `php artisan optimize` exécute en une commande :
> `config:cache` + `route:cache` + `view:cache` + `event:cache`.

### Alias de déploiement (à installer une fois sur le VPS)

Pour ne plus jamais oublier une étape, créer un alias permanent sur le serveur :

```bash
# Sur le VPS, ajouter à la fin de ~/.bashrc
echo "alias deploy-sgt='cd /var/www/erp-sgt && git pull origin main && php artisan migrate --force && php artisan optimize && echo \"✅ SGT déployé\"'" >> ~/.bashrc
source ~/.bashrc
```

Ensuite, chaque déploiement se résume à une seule commande :

```bash
deploy-sgt
```

### En cas de problème après déploiement

```bash
php artisan optimize:clear       # vide TOUS les caches (config, route, view, event)
php artisan optimize             # puis les reconstruit proprement
tail -50 storage/logs/laravel.log   # diagnostiquer une erreur 500
```
