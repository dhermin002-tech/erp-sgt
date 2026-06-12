# EXECUTION_PLAN.md — Refonte design SGT (plan de reprise)

> **POINT DE REPRISE** : exécution non démarrée. À la reprise après renouvellement des tokens,
> exécuter les tâches ci-dessous **sprint par sprint**, en jouant chaque agent assigné.
> Validation = `php artisan test` 100% vert. Marquer chaque tâche `terminé` dans SGT.
> **Pour chaque tâche : créer d'abord ses sous-tâches** (règle [[feedback-sous-taches-auto]]).

## Règles d'exécution (toutes sessions)
1. Avant de coder une tâche : `creer_sous_tache` pour chaque action individuelle.
2. Coder en jouant l'agent assigné (design-ui-agent, dev-agent, qa-agent, audit-agent).
3. `php artisan test` → si vert : `toggle_sous_tache` sur les actions faites + `changer_statut` → `termine`.
4. Commit + `deploy-sgt` à la fin de chaque sprint.
5. PHP local : `C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe`

## SPRINT 1 — Fondations & Login (P0) — tâches SGT #32→#36
| Tâche SGT | Code | Agent | Sous-tâches prévues |
|---|---|---|---|
| #32 | DS-001 | design-ui-agent | créer sgt-tokens.css · définir couleurs/typo/spacing/radius/ombres · importer dans app.blade.php · vérifier rendu |
| #33 | DS-002 | design-ui-agent | créer x-kpi-card.blade.php · props (valeur/tendance/barre/couleur) · responsive · test rendu |
| #34 | DS-003 | design-ui-agent | x-stat-panel · x-avatar · x-empty-state · tests |
| #35 | DS-004 | dev-agent | route /design-system · contrôleur · vue showcase tous états · middleware manager |
| #36 | LOG-001 | design-ui-agent | structure split-screen · panneau branding · formulaire + erreurs inline · état loading · responsive <768px · LoginTest vert |

## SPRINT 2 — Dashboard & Tâches (P1) — tâches SGT #37→#42
| Tâche SGT | Code | Agent | Sous-tâches prévues |
|---|---|---|---|
| #37 | DASH-001 | dev-agent | DashboardService · méthode kpis() 1 requête · données panneaux · DashboardTest |
| #38 | DASH-002 | design-ui-agent | 6 x-kpi-card · grid auto-fit · responsive 1 col mobile |
| #39 | DASH-003 | design-ui-agent | donut Chart.js différé · barres · fallback texte · data-attributes |
| #40 | DASH-004 | design-ui-agent | panneau tâches critiques · activités récentes · actions IA · échéances |
| #41 | TSK-001 | design-ui-agent | appliquer DS liste tâches · conserver chips · TacheTest |
| #42 | TSK-002 | design-ui-agent | refonte show · refonte form create/edit · tests |

## SPRINT 3 — Reste & Audit (P2/P3) — tâches SGT #43→#45
| Tâche SGT | Code | Agent | Sous-tâches prévues |
|---|---|---|---|
| #43 | SEC-001 | design-ui-agent | refonte Membres · Sites · Rapports · tests verts |
| #44 | AGT-001 | design-ui-agent | harmoniser Tâches IA · Rapports IA · Sessions IA |
| #45 | AUD-001 | audit-agent | suite complète verte · cohérence DS · responsive · score /100 |

## Correspondance agents (snapshot — préférer responsables_codes)
6=dev-agent · 7=qa-agent · 8=project-agent · 9=design-ui-agent · 10=audit-agent · 11=expert-kt · 12=le-doyen-kt
