# SGT — Système de Gestion des Tâches
### KayTechnologie Gabon

[![Tests CI](https://github.com/dhermin002-tech/erp-sgt/actions/workflows/tests.yml/badge.svg)](https://github.com/dhermin002-tech/erp-sgt/actions/workflows/tests.yml)

Outil interne de planification, suivi et reporting des tâches quotidiennes (terrain + bureau).

## Stack
- **Backend** : Laravel 11 · PHP 8.3
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

URL cible : `https://sgt.kaytechnologie.online`
VPS : `72.61.195.69` (Ubuntu 24.04 · Nginx) — voir `pdd.md` pour le plan complet.
