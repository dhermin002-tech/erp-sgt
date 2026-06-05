# PROGRESS.md — SGT (Système de Gestion des Tâches)
**Dernière mise à jour** : 2026-06-05 — Sprint 4 terminé
**Agent actif** : DevAgent
**Avancement global** : 68% (19/28 tâches)

---

## Tâches terminées (19/28)

### Sprints 1-3 ✅ (15 tâches — voir journal précédent)

### Sprint 4 ✅ (4 tâches)
- ✅ FEAT-008 — Rapports d'intervention (CRUD) + Actions de suivi AJAX (fait/à faire)
- ✅ FEAT-009 — Compteur archives ce mois dans dashboard + liens accès rapide
- ✅ FIX — `bootstrap/app.php` : JSON responses pour wantsJson() (422 correct)

---

## En cours
_Aucune tâche en cours. Sprint 4 terminé — Lancer Sprint 5 via `dev-agent run erp.sgt`._

---

## Prochaine tâche
- ⬜ **FEAT-010** — KPI Cards + filtres dashboard (EF-8.1/8.5) — Sprint 5

---

## Avancement par Sprint

| Sprint | Titre | Tâches | Terminées | % |
|---|---|---|---|---|
| Sprint 1 | Infrastructure & Auth | 5 | 5 | 100% ✅ |
| Sprint 2 | Gestion des Tâches Core | 5 | 5 | 100% ✅ |
| Sprint 3 | Dates, Commentaires, Notif. | 5 | 5 | 100% ✅ |
| **Sprint 4** | Rapports & Archivage | 4 | **4** | **100%** ✅ |
| Sprint 5 | Tableau de Bord | 2 | 0 | 0% |
| Sprint 6 | Recherche, i18n, Thème | 4 | 0 | 0% |
| Sprint 7 | Tests, Sécu, Déploiement | 5 | 0 | 0% |
| **Total** | | **28** | **19** | **68%** |

---

## Budget tokens — ~268 000 / ~620 000 consommés

## Blockers
❌ Aucun

---

## Journal — Sprint 4 (2026-06-05)
- ✅ RapportController : store + destroy (auteur ou Manager)
- ✅ ActionSuiviController : store AJAX + toggle fait/à faire + destroy
- ✅ Section Rapports & Actions dans taches/show (onglets visuels)
- ✅ Dashboard : compteur archives ce mois + liens accès rapide
- ✅ Fix bootstrap/app.php : `shouldRenderJsonWhen` inclut `wantsJson()`
- ✅ 47 tests PHPUnit verts (9 nouveaux)
- ✅ Commit : `7ef7034`

### ✅ Agent KT — Sprint 4 vérifié
- ERR-PHP-004 : PDF avec images base64 → non applicable (pas d'export PDF ce sprint)
- ERR-CSS-001 : overflow-y:auto sur liste rapports + min-height:0 ✅
