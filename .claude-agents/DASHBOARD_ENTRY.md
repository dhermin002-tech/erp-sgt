# DASHBOARD_ENTRY.md — Inscription kaytech_dashbord
## Projet : SGT — Système de Gestion des Tâches

> Ce fichier contient les données nécessaires pour inscrire ce projet
> dans le tableau de bord KayTechnologie (`kaytech_dashbord`).

---

## Fiche projet dashboard

```yaml
id: erp_sgt
label: SGT — Gestion des Tâches
dossier_www: erp.sgt
url_locale: http://erp.sgt.test
url_production: https://sgt.kaytechnologie.online
stack: Laravel 11 + Blade + Bootstrap 5
statut: En développement
phase_actuelle: Initialisation — Sprint 1 non démarré
avancement: 0%
priorite: Haute
date_creation: 2026-06-05
date_livraison_estimee: 2026-08-22
github_repo: https://github.com/kaytechnologie/erp-sgt (à créer)
```

---

## Commande d'ajout dans le dashboard

Pour ajouter manuellement ce projet dans `kaytech_dashbord`, ajouter l'entrée suivante
dans le fichier de configuration du dashboard (selon le format existant) :

```json
{
  "id": "erp_sgt",
  "label": "SGT — Gestion des Tâches",
  "folder": "erp.sgt",
  "url_local": "http://erp.sgt.test",
  "url_prod": "https://sgt.kaytechnologie.online",
  "stack": "Laravel 11",
  "status": "dev",
  "progress": 0,
  "tasks_file": "c:/laragon/www/erp.sgt/.claude-agents/TASKS.md",
  "progress_file": "c:/laragon/www/erp.sgt/PROGRESS.md",
  "bugs_file": "c:/laragon/www/erp.sgt/BUGS.md"
}
```

---

## Mapping agents IA par sprint

| Sprint | Agent principal | Agents support | Livrable |
|--------|----------------|----------------|----------|
| S1 — Infra & Auth | `dev-agent` | `db-agent`, `infra-agent` | Laravel opérationnel, 5 rôles RBAC |
| S2 — Tâches Core | `dev-agent` | `design-ui-agent` | CRUD tâches + statuts + sous-tâches |
| S3 — Dates & Notif. | `dev-agent` | — | Retards détectés, commentaires, notifications |
| S4 — Rapports | `dev-agent` | `design-ui-agent` | Rapports intervention, archivage |
| S5 — Dashboard | `dev-agent` | `design-ui-agent` | KPI + 3 graphiques Chart.js |
| S6 — UX & i18n | `dev-agent` | `design-ui-agent` | Recherche, FR/EN, thème A/B |
| S7 — QA & Déploiement | `qa-agent` | `security-agent`, `deploy-agent` | Prod live, tests verts, SSL |

---

## Comment lancer chaque sprint

```bash
# Sprint 1 — Infrastructure
/dev-agent run erp.sgt

# Sprint 7 — Tests & QA
/qa-agent run erp.sgt

# Sprint 7 — Déploiement
/deploy-agent run erp.sgt

# Rapport d'avancement à tout moment
/report-agent run erp.sgt
```
