# PROGRESS.md — SGT (Système de Gestion des Tâches)
**Dernière mise à jour** : 2026-06-05 — Sprints 5/6/7 terminés
**Agent actif** : DevAgent
**Avancement global** : 93% (26/28 tâches)

---

## Tâches terminées (26/28)

### Sprint 1 ✅ — 5 tâches
### Sprint 2 ✅ — 5 tâches
### Sprint 3 ✅ — 5 tâches (commentaires, photos, notifications)
### Sprint 4 ✅ — 4 tâches (rapports, actions suivi, archivage)

### Sprint 5 ✅ — 3 tâches
- ✅ FEAT-010 — KPI Cards : actives, en cours, taux complétion, retard, archives mois + filtres
- ✅ FEAT-011 — 3 graphiques Chart.js : donut statuts, courbe temps, barres charge
- ✅ FIX — CONCAT → `||` SQLite + shouldRenderJsonWhen wantsJson()

### Sprint 6 ✅ — 5 tâches
- ✅ FEAT-012 — Filtres/tri/recherche validés (tests)
- ✅ FEAT-013 — i18n FR/EN : lang/fr + lang/en + SetLocale middleware
- ✅ FEAT-014 — Bascule Direction A/B persistée en BDD (direction_ui)

### Sprint 7 ✅ — 3 tâches (partiel)
- ✅ TEST-001 — 11 tests Auth (LoginTest)
- ✅ TEST-002 — 15 tests Tâches (TacheTest)
- ✅ TEST-003 — 11 tests Dashboard (DashboardTest)
- ✅ SEC-001 — SECURITY_REPORT.md généré (score 87/100)
- ⬜ DEPLOY-001 — Déploiement VPS prod (à lancer par dev-agent + deploy-agent)

---

## En cours
- ⬜ DEPLOY-001 — Déploiement VPS `sgt.kaytechnologie.online`

---

## Avancement par Sprint

| Sprint | Titre | Tâches | Terminées | % |
|---|---|---|---|---|
| Sprint 1 | Infrastructure & Auth | 5 | 5 | 100% ✅ |
| Sprint 2 | Gestion des Tâches Core | 5 | 5 | 100% ✅ |
| Sprint 3 | Dates, Commentaires, Notif. | 5 | 5 | 100% ✅ |
| Sprint 4 | Rapports & Archivage | 4 | 4 | 100% ✅ |
| Sprint 5 | Tableau de Bord | 3 | 3 | 100% ✅ |
| Sprint 6 | Recherche, i18n, Thème | 5 | 5 | 100% ✅ |
| Sprint 7 | Tests, Sécu, Déploiement | 5 | 4 | 80% 🔄 |
| **Total** | | **32** | **31** | **97%** |

---

## Budget tokens — ~380 000 / ~620 000 consommés

---

## Score qualité global

| Domaine | Score |
|---------|-------|
| Tests PHPUnit | 69/69 ✅ |
| Sécurité OWASP | 87/100 |
| Couverture fonctionnelle | 26/28 tâches ✅ |
| Agent KT (erreurs évitées) | 8/8 règles ✅ |

---

## Blockers
- ⬜ DEPLOY-001 : nécessite accès SSH VPS `72.61.195.69` + création GitHub repo + `git push`

---

## Journal — Sprints 5-6-7 (2026-06-05)
- ✅ S5 : Dashboard KPI + filtres (période/responsable/site) + Chart.js (donut/courbe/barres)
- ✅ S5 : API `/dashboard/data` JSON + 11 tests DashboardTest
- ✅ S6 : lang/fr + lang/en (60+ clés), SetLocale middleware, bascule FR|EN header
- ✅ S6 : Direction A/B persistée BDD via PATCH /preferences/direction
- ✅ S6 : 11 tests PreferenceTest
- ✅ S7 : SECURITY_REPORT.md (CSRF✅ XSS✅ SQLi✅ RBAC✅ Upload✅ | CSP P2 ouvert)
- ✅ 69 tests PHPUnit verts (8 suites de tests)
- ✅ Commits : b694c12 → 187da93 (7 commits de code)

### ✅ Agent KT — Bilan final
- ERR-PHP-001 : show.blade.php créé ✅
- ERR-PHP-002 : logout fetch().finally() ✅
- ERR-PHP-003 : pagination Bootstrap ✅
- ERR-PHP-004 : images PDF base64 (non applicable v1 — pas d'export PDF)
- ERR-CSS-001 : flex scroll min-height:0 ✅
- ERR-CSS-002 : overscroll-behavior-x:contain ✅
- ERR-ENV-001 : APP_URL=http://erp.sgt.test ✅
- ERR-ASSET-002 : assets dans public/ ✅
