# PROGRESS.md — SGT (Système de Gestion des Tâches)
**Dernière mise à jour** : 2026-06-05 — Sprint 2 terminé
**Agent actif** : DevAgent
**Avancement global** : 36% (10/28 tâches)

---

## Tâches terminées (10/28)

### Sprint 1 ✅
- ✅ INFRA-001 — Initialisation Laravel 11 + vhost + .env (~15 000 tokens)
- ✅ INFRA-002 — Migrations BDD + seeders (10 tables) (~18 000 tokens)
- ✅ AUTH-001 — Auth username + RBAC 5 rôles (~16 000 tokens)
- ✅ INFRA-003 — Layout Blade + charte A/B + vue login (~14 000 tokens)
- ✅ INFRA-004 — CI/CD GitHub Actions + 11 tests verts (~10 000 tokens)

### Sprint 2 ✅
- ✅ FEAT-001 — CRUD Tâches complet + filtres/tri/recherche (~35 000 tokens)
- ✅ FEAT-002 — Sous-tâches AJAX + progression auto recalculée (~20 000 tokens)
- ✅ FEAT-003 — Statuts + code couleur CDC (5 statuts, changement 1 clic) (inclus FEAT-001)
- ✅ FEAT-004 — Sites intervention + multi-responsables + SiteController (~12 000 tokens)
- ✅ FIX — Route parameter `{tach}` → `{tache}` (singularisation Laravel fr)

---

## En cours
_Aucune tâche en cours. Sprint 2 terminé — Lancer Sprint 3 via `dev-agent run erp.sgt`._

---

## Prochaine tâche
- ⬜ **FEAT-005** — Détection automatique des retards (scope `enRetard()`) — Sprint 3

---

## Avancement par Sprint

| Sprint | Titre | Tâches | Terminées | % |
|---|---|---|---|---|
| **Sprint 1** | Infrastructure & Auth | 5 | **5** | **100%** ✅ |
| **Sprint 2** | Gestion des Tâches Core | 4+1 | **5** | **100%** ✅ |
| Sprint 3 | Dates, Commentaires, Notif. | 3 | 0 | 0% |
| Sprint 4 | Rapports & Archivage | 2 | 0 | 0% |
| Sprint 5 | Tableau de Bord | 2 | 0 | 0% |
| Sprint 6 | Recherche, i18n, Thème | 4 | 0 | 0% |
| Sprint 7 | Tests, Sécu, Déploiement | 5 | 0 | 0% |
| **Total** | | **28** | **10** | **36%** |

---

## Budget tokens

| Poste | Consommé | Budget |
|---|---|---|
| Sprint 1 | ~73 000 | — |
| Sprint 2 | ~90 000 | — |
| Total projet | ~163 000 | ~620 000 |

---

## Blockers
❌ Aucun

---

## Journal

### 2026-06-05 — DevAgent (Sprint 2)
- ✅ TacheController (resource) : index filtres/tri/recherche, create/store, show, edit/update, destroy, archives, restaurer, patchStatut (AJAX)
- ✅ TacheRequest : validation complète (FormRequest)
- ✅ SousTacheController : store/toggle/destroy (AJAX JSON) + progression auto via `recalculerProgression()`
- ✅ SiteController + vues CRUD (Manager uniquement via `role:manager`)
- ✅ 5 vues tâches : index, create, edit, show, archives
- ✅ Badge statut réutilisable (`partials/badge_statut.blade.php`)
- ✅ Dropdown statut 1 clic (AJAX PATCH `/taches/{tache}/statut`)
- ✅ Fix route parameter : `.parameters(['taches' => 'tache'])`
- ✅ TacheFactory + SiteFactory créées
- ✅ 28 tests PHPUnit verts (TacheTest 15 tests + LoginTest 11 + ExampleTest 1 + Unit 1)
- ✅ Commits : `b694c12` (S1) + `80339b9` (PROGRESS) + `e6edb84` (S2)

### ✅ Agent KT — Sprint 2 vérifié
- ERR-PHP-001 : show.blade.php créé ✅
- ERR-PHP-002 : logout `fetch().finally()` ✅
- ERR-PHP-003 : pagination Bootstrap `pagination::bootstrap-4` ✅
- ERR-CSS-002 : `overflow-x:auto + overscroll-behavior-x:contain` sur tableau ✅
- ERR-CSS-001 : flex scroll → à surveiller Sprint 3 (vues plus complexes)
