# PROGRESS.md — SGT (Système de Gestion des Tâches)
**Dernière mise à jour** : 2026-06-05 — Sprint 1 terminé
**Agent actif** : DevAgent
**Avancement global** : 18% (5/28 tâches)

---

## Tâches terminées (5/28)

- ✅ INFRA-001 — Initialisation Laravel 11 + vhost + .env (~15 000 tokens) — 2026-06-05
- ✅ INFRA-002 — Migrations BDD + seeders (10 tables, 5 sites, 5 users) (~18 000 tokens) — 2026-06-05
- ✅ AUTH-001 — Auth username + RBAC 5 rôles + logoutSafe() (~16 000 tokens) — 2026-06-05
- ✅ INFRA-003 — Layout principal Blade + charte A/B + vue login (~14 000 tokens) — 2026-06-05
- ✅ INFRA-004 — CI/CD GitHub Actions + phpunit.xml SQLite + 11 tests verts (~10 000 tokens) — 2026-06-05

---

## En cours
_Aucune tâche en cours. Sprint 1 terminé — Lancer Sprint 2 via `dev-agent run erp.sgt`._

---

## Prochaine tâche
- ⬜ **FEAT-001** — CRUD Tâches complet (EF-1) — Sprint 2
  - Lancer : `/dev-agent run erp.sgt`

---

## Avancement par Sprint

| Sprint | Titre | Tâches | Terminées | % |
|---|---|---|---|---|
| **Sprint 1** | Infrastructure & Auth | 5 | **5** | **100%** ✅ |
| Sprint 2 | Gestion des Tâches Core | 4 | 0 | 0% |
| Sprint 3 | Dates, Commentaires, Notif. | 3 | 0 | 0% |
| Sprint 4 | Rapports & Archivage | 2 | 0 | 0% |
| Sprint 5 | Tableau de Bord | 2 | 0 | 0% |
| Sprint 6 | Recherche, i18n, Thème | 4 | 0 | 0% |
| Sprint 7 | Tests, Sécu, Déploiement | 5 | 0 | 0% |
| Backlog | Nice-to-have | 3 | 0 | — |
| **Total** | | **28** | **5** | **18%** |

---

## Budget tokens

| Poste | Consommé | Budget |
|---|---|---|
| Sprint 1 (session 2026-06-05) | ~73 000 | 100 000 |
| Total projet | ~73 000 | ~620 000 |

---

## Blockers
❌ Aucun

---

## Journal

### 2026-06-05 — DevAgent (Sprint 1)
- ✅ Laravel 11 installé, BDD `erp_sgt` MySQL, vhost `http://erp.sgt.test`
- ✅ 10 migrations : users (username/role), sites, taches, sous_taches, tache_user, commentaires, notifications, rapports, actions_suivi
- ✅ 7 modèles (scopes `enRetard`, `actives`, `visiblePar` sur Tache)
- ✅ UserFactory avec états `manager()`, `technicien()`, `developpeur()`, `stagiaire()`
- ✅ Seeders : 5 sites Gabon + 5 users (1 par rôle)
- ✅ Auth username + RoleMiddleware enregistré dans bootstrap/app.php
- ✅ Vue login avec charte-graphique.css (sans Bootstrap externe)
- ✅ Layout Blade Direction A/B + hamburger mobile + logoutSafe() (ERR-PHP-002)
- ✅ DashboardController avec KPI (scoped par rôle)
- ✅ 11 tests PHPUnit verts sur LoginTest
- ✅ CI/CD `.github/workflows/tests.yml` configuré
- ✅ Commit : `b694c12`

### ✅ Agent KT — Sprint 1 vérifié
- ERR-PHP-002 : logout `fetch().finally()` ✅ appliqué
- ERR-ENV-001 : `APP_URL=http://erp.sgt.test` ✅
- ERR-PHP-001 : routes resource → à appliquer Sprint 2 (avec show.blade.php obligatoire)
- ERR-PHP-003 : pagination Bootstrap → à appliquer Sprint 2
- ERR-PHP-004 : images PDF base64 → Sprint 4
- ERR-CSS-001 : flex scroll min-height:0 → à vérifier Sprint 2
