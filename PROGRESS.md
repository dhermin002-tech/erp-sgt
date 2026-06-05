# PROGRESS.md — SGT (Système de Gestion des Tâches)
**Dernière mise à jour** : 2026-06-05 — Sprint 3 terminé
**Agent actif** : DevAgent
**Avancement global** : 54% (15/28 tâches)

---

## Tâches terminées (15/28)

### Sprint 1 ✅ (5 tâches)
- ✅ INFRA-001/002/003/004 + AUTH-001

### Sprint 2 ✅ (5 tâches)
- ✅ FEAT-001/002/003/004 + FIX route param

### Sprint 3 ✅ (5 tâches)
- ✅ FEAT-005 — Détection retards (scope `enRetard()`, KPI dashboard, badge rouge)
- ✅ FEAT-006 — Fil commentaires + upload photo terrain (jpg/png/webp ≤5Mo, soft delete)
- ✅ FEAT-007 — Notifications in-app : assignation + changement statut, cloche header avec badge, tout marquer lu

---

## En cours
_Aucune tâche en cours. Sprint 3 terminé — Lancer Sprint 4 via `dev-agent run erp.sgt`._

---

## Prochaine tâche
- ⬜ **FEAT-008** — Rapports & actions à entreprendre (EF-6) — Sprint 4

---

## Avancement par Sprint

| Sprint | Titre | Tâches | Terminées | % |
|---|---|---|---|---|
| Sprint 1 | Infrastructure & Auth | 5 | 5 | 100% ✅ |
| Sprint 2 | Gestion des Tâches Core | 5 | 5 | 100% ✅ |
| **Sprint 3** | Dates, Commentaires, Notif. | 5 | **5** | **100%** ✅ |
| Sprint 4 | Rapports & Archivage | 2 | 0 | 0% |
| Sprint 5 | Tableau de Bord | 2 | 0 | 0% |
| Sprint 6 | Recherche, i18n, Thème | 4 | 0 | 0% |
| Sprint 7 | Tests, Sécu, Déploiement | 5 | 0 | 0% |
| **Total** | | **28** | **15** | **54%** |

---

## Budget tokens

| Poste | Consommé | Budget |
|---|---|---|
| Sprint 1 | ~73 000 | — |
| Sprint 2 | ~90 000 | — |
| Sprint 3 | ~55 000 | — |
| **Total projet** | **~218 000** | ~620 000 |

---

## Blockers
❌ Aucun

---

## Journal

### 2026-06-05 — DevAgent (Sprint 3)
- ✅ FEAT-005 : scope `enRetard()` opérationnel, KPI dashboard "En retard" avec badge alerte
- ✅ FEAT-006 : CommentaireController (store/destroy), fil chronologique dans taches/show, upload photo Storage::disk('public'), soft delete auteur ou Manager
- ✅ FEAT-007 : TacheAssigneeNotification + StatutTacheNotification (channel=database), dispatch dans TacheController (store/update/patchStatut), NotificationController (index/marquerLue/toutLire/count), cloche header avec badge compteur + dropdown 8 dernières notifs
- ✅ 38 tests PHPUnit verts (10 nouveaux : CommentaireTest + notifications)
- ✅ Commit : `9ef73b3`

### ✅ Agent KT — Sprint 3 vérifié
- ERR-PHP-004 : photos base64 PDF non applicable Sprint 3 (pas de PDF) ✅
- ERR-CSS-001 : fil commentaires `max-height:400px + overflow-y:auto + min-height:0` ✅
- Upload : validation mimes + taille max côté FormRequest ✅
