# FICHE_TECHNIQUE.md — SGT (Système de Gestion des Tâches)
**Détecté par** : ProjectAgent — 2026-06-05
**Basé sur** : cdc.md + pdd.md

---

## Identité du projet

| Champ | Valeur |
|---|---|
| **Nom court** | erp.sgt |
| **Nom complet** | Système de Gestion des Tâches — KayTechnologie |
| **Client** | KayTechnologie Gabon |
| **URL cible prod** | https://sgt.kaytechnologie.online |
| **URL locale** | http://erp.sgt.test |
| **Dossier www/** | c:/laragon/www/erp.sgt/ |
| **Dépôt GitHub** | https://github.com/kaytechnologie/erp-sgt (à créer) |
| **Monnaie** | N/A (pas de facturation en v1) |
| **Langue UI** | Bilingue FR / EN (bascule utilisateur) |

---

## Stack technique

| Couche | Choix | Version |
|---|---|---|
| **Type** | LARAVEL_WEB | — |
| **Backend** | Laravel | 11 |
| **Langage backend** | PHP | 8.3 |
| **Frontend** | Blade + Bootstrap | 5 |
| **JS léger** | Alpine.js | 3.x |
| **Graphiques** | Chart.js | 4.x |
| **Thème** | charte-graphique.css (Direction A/B) | v1.0 |
| **Internationalisation** | Laravel Lang (FR/EN) | — |
| **Base de données** | MySQL | 8+ |
| **Auth** | Laravel Breeze modifié (username) | — |
| **RBAC** | Middleware Laravel (5 rôles) | — |
| **Tests** | PHPUnit + SQLite in-memory | — |
| **Export PDF** | — (hors périmètre v1) | — |
| **Hébergement** | VPS Hostinger (72.61.195.69) | Ubuntu 24.04 |
| **CI/CD** | GitHub Actions | — |

---

## Rôles RBAC (5 niveaux)

| Rôle | Slug | Droits |
|---|---|---|
| Manager | `manager` | Vue globale, CRUD toutes tâches, gestion membres, dashboard complet |
| Technicien | `technicien` | Ses tâches uniquement, statut/progression, commentaires, photos |
| Agent | `agent` | Ses tâches uniquement, statut, commentaires |
| Développeur | `developpeur` | Ses tâches, sous-tâches, statut, commentaires |
| Stagiaire | `stagiaire` | Ses tâches, statut (droits restreints) |

---

## Modules fonctionnels (EF-1 à EF-10)

| Ref CDC | Module | Sprint |
|---|---|---|
| EF-1 | Gestion des tâches (CRUD + champs complets) | Sprint 2 |
| EF-2 | Sous-tâches + progression auto | Sprint 2 |
| EF-3 | Statuts + code couleur (5 statuts) | Sprint 2 |
| EF-4 | Dates + détection retard | Sprint 3 |
| EF-5 | Commentaires + photos + notifications | Sprint 3 |
| EF-6 | Rapports & actions à entreprendre | Sprint 4 |
| EF-7 | Archivage + restauration | Sprint 4 |
| EF-8 | Tableau de bord (KPI + graphiques) | Sprint 5 |
| EF-9 | Recherche, tri, filtres | Sprint 6 |
| EF-10 | Bilingue FR/EN + thème A/B | Sprint 6 |

---

## Skills agents activés automatiquement

```
dev-agent          → développement Laravel (tous sprints)
design-ui-agent    → UI/UX, charte A/B, composants visuels (S2, S4, S5, S6)
db-agent           → schéma BDD, index, contraintes FK (S1)
infra-agent        → vhost Laragon, VPS, Nginx, démons (S1, S7)
qa-agent           → PHPUnit, rapport bugs, go/no-go (S7)
deploy-agent       → git push, VPS déploiement, rollback (S7)
security-agent     → OWASP, RBAC, XSS/SQLi audit (S7)
```

---

## Contraintes techniques

- Auth par **username** uniquement — jamais email
- Thème CSS livré : `charte-graphique.css` (Direction A = sidebar marine / Direction B = nav haute)
- Bascule thème : `theme-switcher.js` (localStorage)
- Code statut couleurs fixes CDC : gris `#64748B`, bleu `#2563EB`, ambre `#C97A0A`, bordeaux `#B0202E`, vert `#15885A`
- Soft deletes sur `taches`, `commentaires`, `users`
- Pagination avec `simple-bootstrap` (pas Tailwind)
- `APP_DEBUG=false` en production obligatoire

---

## Environnements

| Env | URL | BDD | Notes |
|---|---|---|---|
| DEV | http://erp.sgt.test | MySQL local (Laragon) | `.env` local |
| REC | — | Séparée | Tests recette manager |
| PROD | https://sgt.kaytechnologie.online | MySQL VPS | `APP_DEBUG=false` |
