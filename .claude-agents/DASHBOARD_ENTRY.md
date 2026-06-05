# DASHBOARD_ENTRY.md — Inscription kaytech_dashbord
## Projet : SGT — Système de Gestion des Tâches
**Mis à jour** : 2026-06-05 — Sprints 1-7 terminés

---

## Fiche projet dashboard (état actuel)

```yaml
id: erp_sgt
label: SGT — Gestion des Tâches
dossier_www: erp.sgt
url_locale: http://erp.sgt.test
url_production: https://sgt.kaytechnologie.online
stack: Laravel 11 + Blade + Bootstrap 5 + Chart.js
statut: Prêt à déployer (97%)
phase_actuelle: Sprint 7 terminé — en attente DEPLOY-001
avancement: 97%
taches: 26/28 terminées
tests: 69/69 verts
commits: 8
score_securite: 87/100
date_creation: 2026-06-05
date_livraison_estimee: 2026-08-22
github_repo: https://github.com/kaytechnologie/erp-sgt (à créer)
```

---

## Inscription dans le système kaytech_dashbord

### ✅ Fait automatiquement
- `~/.cc-dashboard/config.json` → `erp_sgt` ajouté dans `projects[]`
- Label ajouté : `"erp_sgt": "SGT — Gestion des Tâches"`
- `tasks-watcher.ts` → mapping `'erp_sgt': 'C:/laragon/www/erp.sgt'` ajouté
- `~/.claude/projects/erp_sgt/reports/` → dossier créé
- Rapport initial généré : `2026-06-05T00-00-00.md`

### À faire manuellement (si daemon en cours)
Redémarrer le daemon pour prendre en compte le nouveau projet :
```bash
# Dans kaytech_dashbord/
start-daemon.bat
```

---

## Fichiers surveillés par tasks-watcher

```
c:/laragon/www/erp.sgt/.claude-agents/TASKS.md   ← suivi automatique tâches
c:/laragon/www/erp.sgt/PROGRESS.md               ← avancement global
```

---

## Mapping agents IA par sprint (réalisé)

| Sprint | Agent | Statut | Livrable |
|--------|-------|--------|----------|
| S1 — Infra & Auth | `dev-agent` | ✅ | Laravel 11, RBAC 5 rôles, layout A/B |
| S2 — Tâches Core | `dev-agent` | ✅ | CRUD tâches, sous-tâches, statuts |
| S3 — Dates & Notif. | `dev-agent` | ✅ | Retards, commentaires, photos, notifications |
| S4 — Rapports | `dev-agent` | ✅ | Rapports terrain, actions suivi, archivage |
| S5 — Dashboard | `dev-agent` | ✅ | KPI + 3 graphiques Chart.js |
| S6 — UX & i18n | `dev-agent` | ✅ | FR/EN, Direction A/B BDD |
| S7 — QA | `dev-agent` + `security-agent` | ✅ | 69 tests, SECURITY_REPORT.md |
| DEPLOY | `deploy-agent` | ⬜ | VPS sgt.kaytechnologie.online |
