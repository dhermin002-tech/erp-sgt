# Plan de Déploiement (PDD)
## Système de Gestion des Tâches — KayTechnologie Gabon

| | |
|---|---|
| **Projet** | Système de Gestion des Tâches (SGT) |
| **Client** | KayTechnologie Gabon |
| **Version** | 1.0 |
| **Date** | Juin 2026 |
| **Document lié** | `cdc.md` (Cahier des charges) |

---

## 1. Objectif du document

Décrire la stratégie, les étapes, les prérequis et les responsabilités pour **mettre en production** le Système de Gestion des Tâches et **accompagner l'équipe Kaytech** dans son adoption.

---

## 2. Stratégie de déploiement

Déploiement **progressif en 3 vagues** afin de limiter les risques :

1. **Vague pilote** — Manager + 2 techniciens, sur un périmètre réduit (1 site).
2. **Vague étendue** — toute l'équipe technique (Techniciens, Agents, Développeurs).
3. **Généralisation** — Stagiaires + tous les sites d'intervention.

> Critère de passage d'une vague à l'autre : retours positifs + correction des anomalies bloquantes.

---

## 3. Environnements

| Environnement | Usage | Accès |
|---|---|---|
| **DEV** (Développement) | Codage, tests unitaires | Équipe dev |
| **REC** (Recette / Test) | Validation fonctionnelle, tests utilisateurs | Manager + pilotes |
| **PROD** (Production) | Utilisation réelle par l'équipe | Toute l'équipe |

Chaque environnement dispose de sa **base de données isolée**. Aucune donnée réelle en DEV.

---

## 4. Architecture cible

```
        ┌─────────────────────────────┐
        │   Navigateur / Mobile        │
        │   (FR / EN, charte A ou B)   │
        └──────────────┬──────────────┘
                       │ HTTPS
        ┌──────────────▼──────────────┐
        │   Serveur Web (Front)        │
        │   HTML / CSS / JS (React)    │
        └──────────────┬──────────────┘
                       │ API REST
        ┌──────────────▼──────────────┐
        │   Serveur Applicatif (API)   │
        │   Auth + rôles + métier      │
        └──────────────┬──────────────┘
                       │
        ┌──────────────▼──────────────┐
        │   Base de données            │
        │   (PostgreSQL / MySQL)       │
        └──────────────────────────────┘
```

**Option d'hébergement** (à valider) :
- **A.** Serveur interne KayTechnologie (maîtrise totale, dépend de la connexion).
- **B.** Cloud (VPS / hébergeur) — meilleure disponibilité, accès terrain facilité.

---

## 5. Prérequis

### 5.1 Techniques
- Nom de domaine + **certificat HTTPS (SSL)**.
- Serveur (interne ou cloud) avec runtime (Node.js / PHP) et base de données.
- Outil de **sauvegarde automatique**.
- Comptes utilisateurs initiaux (liste des membres + rôles).

### 5.2 Organisationnels
- Validation du **CDC** par le Manager.
- Choix de la **charte (Direction A ou B)**.
- Liste des **sites d'intervention** et des **membres** à pré-charger.
- Désignation d'un **référent interne** (point de contact).

---

## 6. Étapes de déploiement

| # | Étape | Responsable | Livrable / Vérif. |
|---|---|---|---|
| 1 | Préparation des environnements (DEV/REC/PROD) | Dev / IT | Serveurs prêts |
| 2 | Configuration base de données + sauvegardes | Dev / IT | BDD opérationnelle |
| 3 | Déploiement de l'application en **REC** | Dev | Build en recette |
| 4 | Chargement données initiales (membres, sites) | Dev + Manager | Comptes créés |
| 5 | **Tests de recette** (scénarios CDC) | Manager + pilotes | PV de recette |
| 6 | Correction des anomalies | Dev | Anomalies bloquantes = 0 |
| 7 | Mise en **PROD** (vague pilote) | Dev / IT | Application en ligne |
| 8 | **Formation** des utilisateurs | Référent / Dev | Sessions réalisées |
| 9 | Suivi & extension (vagues 2 et 3) | Manager | Adoption mesurée |
| 10 | Clôture & transfert (maintenance) | Dev → IT interne | Doc remise |

---

## 7. Plan de tests (recette)

Scénarios minimaux à valider avant PROD :

- [ ] Créer / modifier / supprimer une tâche.
- [ ] Ajouter des sous-tâches et les cocher.
- [ ] Changer le statut (les 5 statuts) → couleur correcte.
- [ ] Affecter un responsable + renseigner un site.
- [ ] Cocher « Terminé » → la tâche quitte le tableau et apparaît dans l'archive.
- [ ] Ajouter un commentaire + recevoir une notification.
- [ ] Saisir un rapport / une action à entreprendre.
- [ ] Tableau de bord : KPI, donut, courbe, barres corrects.
- [ ] Détection des tâches en retard.
- [ ] Connexion par rôle : un membre ne voit que ses tâches, le Manager voit tout.
- [ ] Bascule FR / EN.
- [ ] Bascule charte A / B.
- [ ] Affichage mobile (terrain).

---

## 8. Sauvegarde et reprise (rollback)

- **Sauvegarde** : automatique **quotidienne** de la base + sauvegarde avant chaque mise en production.
- **Rétention** : 30 jours glissants.
- **Rollback** : en cas d'anomalie majeure, restauration de la version précédente (build + base) sous **1 heure**.
- **Test de restauration** : vérifié au moins une fois avant la PROD.

---

## 9. Formation et accompagnement

| Public | Format | Contenu |
|---|---|---|
| Manager | Session dédiée (2 h) | Pilotage, tableau de bord, gestion des membres |
| Techniciens / Agents | Atelier pratique (1 h) | Saisie tâches, statut, terrain, mobile |
| Développeurs | Atelier (1 h) | Tâches, sous-tâches, commentaires |
| Stagiaires | Prise en main guidée (30 min) | Usage de base |

**Supports fournis :** guide utilisateur (FR/EN), aide-mémoire des statuts, vidéo courte de prise en main.

---

## 10. Maintenance et support

- **Support de niveau 1** : référent interne KayTechnologie.
- **Support de niveau 2** : équipe de développement (correctifs).
- **Maintenance corrective** : correction des anomalies.
- **Maintenance évolutive** : nouvelles fonctionnalités (Kanban, calendrier, app native…) selon priorités.
- **Suivi** : journal des incidents + tableau de suivi des évolutions.

---

## 11. Sécurité

- Connexions **HTTPS** uniquement.
- **Mots de passe chiffrés**, politique de complexité.
- **Gestion des rôles** (principe du moindre privilège).
- Journalisation des actions sensibles.
- Sauvegardes chiffrées et stockées séparément.

---

## 12. Indicateurs de réussite du déploiement

| Indicateur | Cible |
|---|---|
| Taux d'adoption (membres actifs / total) | ≥ 90 % à 1 mois |
| Tâches saisies par semaine | En croissance |
| Anomalies bloquantes en PROD | 0 |
| Satisfaction utilisateurs | ≥ 4/5 |
| Délai de restauration (rollback testé) | ≤ 1 h |

---

## 13. Planning de déploiement (indicatif)

| Semaine | Activité |
|---|---|
| S1 | Préparation environnements + données |
| S2 | Déploiement REC + recette |
| S3 | Corrections + mise en PROD pilote + formation |
| S4 | Extension équipe technique (vague 2) |
| S5 | Généralisation (vague 3) + clôture |

---

*Document de travail — à ajuster selon le choix d'hébergement et la disponibilité de l'équipe Kaytech.*
