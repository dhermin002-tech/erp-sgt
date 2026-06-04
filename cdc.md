# Cahier des Charges (CDC)
## Système de Gestion des Tâches — KayTechnologie Gabon

| | |
|---|---|
| **Projet** | Système de Gestion des Tâches (SGT) — KayTechnologie |
| **Client** | KayTechnologie Gabon (LAN · Software · Hardware) |
| **Version du document** | 1.0 |
| **Date** | Juin 2026 |
| **Statut** | À valider |
| **Auteur** | Équipe Design / Développement |

---

## 1. Contexte et objectifs

### 1.1 Contexte
KayTechnologie Gabon réalise des interventions de terrain dans les domaines **LAN, Software et Hardware**. Les équipes (Manager, Techniciens, Agents, Développeurs, Stagiaires) ont besoin d'un outil centralisé pour planifier, suivre et rendre compte de leurs travaux au quotidien, y compris sur site.

### 1.2 Objectifs
- Centraliser la **saisie et le suivi des tâches** quotidiennes.
- Attribuer des **responsables** et décomposer le travail en **sous-tâches**.
- Suivre l'état d'avancement via un **code couleur de statut** clair.
- Offrir un **tableau de bord de pilotage** mesurant l'efficacité de l'équipe.
- Rester **utilisable sur le terrain** (mobile) et **bilingue FR / EN**.

### 1.3 Bénéfices attendus
- Visibilité temps réel sur la charge et les retards.
- Réduction des oublis et des tâches non suivies.
- Traçabilité (commentaires, rapports, historique).
- Aide à la décision via les indicateurs.

---

## 2. Périmètre

### 2.1 Inclus
- Gestion des tâches et sous-tâches.
- Tableau de bord et reporting.
- Gestion des rôles et des accès.
- Version web responsive (desktop + mobile).
- Interface bilingue FR / EN.

### 2.2 Exclus (hors périmètre v1)
- Facturation / comptabilité.
- Gestion de stock matériel.
- Pointage / RH.
- Application mobile native (le web responsive couvre le besoin terrain en v1).

---

## 3. Acteurs et rôles

| Rôle | Description | Droits principaux |
|---|---|---|
| **Manager** | Pilote l'activité | Vue **globale** sur toutes les tâches, création/affectation, accès tableau de bord complet, gestion des membres |
| **Technicien** | Intervention terrain | Voit **ses tâches**, met à jour statut/progression, commente, ajoute rapports/photos |
| **Agent** | Support / exécution | Voit **ses tâches**, met à jour statut, commente |
| **Développeur** | Réalisation logicielle | Voit **ses tâches**, sous-tâches, statut, commentaires |
| **Stagiaire** | Accès encadré | Voit **ses tâches**, met à jour statut, commente (droits restreints) |

> **Règle d'accès :** le Manager voit tout ; chaque autre membre voit uniquement les tâches dont il est responsable ou contributeur. Un **sélecteur de rôle** est prévu pour la démonstration.

---

## 4. Exigences fonctionnelles

### EF-1 — Gestion des tâches
- **EF-1.1** Créer, modifier, supprimer une tâche.
- **EF-1.2** Champs d'une tâche : *titre, description, responsable, site/lieu d'intervention, date de début, date d'échéance, statut, progression (%), priorité*.
- **EF-1.3** Affecter un ou plusieurs **responsables** parmi l'équipe.
- **EF-1.4** Renseigner le **lieu / site d'intervention** (ex. Libreville, Akanda, Owendo…).

### EF-2 — Sous-tâches
- **EF-2.1** Décomposer une tâche en **sous-tâches** dépliables.
- **EF-2.2** Chaque sous-tâche a son propre statut et peut être cochée.
- **EF-2.3** La progression de la tâche parente se calcule à partir des sous-tâches terminées.

### EF-3 — Statuts (code couleur)
- **EF-3.1** Cinq statuts normalisés :

| Statut | Couleur | Signification |
|---|---|---|
| À faire / Nouveau | Gris ardoise `#64748B` | Créée, pas démarrée |
| En cours | Bleu `#2563EB` | Travail actif |
| En attente | Ambre `#C97A0A` | Bloquée / attente d'un tiers |
| En arrêt | Bordeaux `#B0202E` | Suspendue / annulée |
| Terminé | Vert `#15885A` | Achevée |

- **EF-3.2** Changement de statut en un clic, avec mise à jour visuelle immédiate.

### EF-4 — Dates et planning
- **EF-4.1** Date de **début** et date de **fin/échéance** obligatoires.
- **EF-4.2** Détection automatique des tâches **en retard** (échéance dépassée et non terminée).
- **EF-4.3** (Option) Vues complémentaires **Kanban** et **Calendrier** en plus du tableau.

### EF-5 — Commentaires et notifications
- **EF-5.1** Fil de **commentaires** par tâche (texte, mention de membre).
- **EF-5.2** (Terrain) Possibilité de joindre une **photo de chantier**.
- **EF-5.3** **Notifications** : nouvelle affectation, changement de statut, échéance proche, mention.

### EF-6 — Rapports et actions
- **EF-6.1** Espace **« Rapports & actions à entreprendre »** par tâche et global.
- **EF-6.2** Saisie de comptes-rendus d'intervention.
- **EF-6.3** Liste d'actions de suivi (à faire / fait).

### EF-7 — Archivage des tâches terminées
- **EF-7.1** Une tâche cochée **« Terminé »** **disparaît du tableau actif**.
- **EF-7.2** Elle reste **consultable dans l'archive « Terminées »**.
- **EF-7.3** Possibilité de **restaurer** une tâche archivée.

### EF-8 — Tableau de bord (pilotage)
- **EF-8.1** Cartes **KPI** : tâches actives, en cours, **taux de complétion (%)**, **tâches en retard** (alerte).
- **EF-8.2** **Répartition par statut** (graphique camembert / donut).
- **EF-8.3** **Avancement dans le temps** (courbe — tâches terminées vs créées).
- **EF-8.4** **Charge par responsable** (barres).
- **EF-8.5** Filtrage du tableau de bord par période, responsable, site.

### EF-9 — Recherche, tri et filtres
- **EF-9.1** Tableau **triable** par colonne (échéance, statut, responsable…).
- **EF-9.2** Filtres : statut, responsable, site, échéance.
- **EF-9.3** Recherche plein texte sur titre/description.

### EF-10 — Multilingue et thème
- **EF-10.1** Interface **bilingue FR / EN** (bascule utilisateur).
- **EF-10.2** **Choix de la charte (Direction A / Direction B)** intégré au système — voir `charte-graphique.css` (attribut `data-direction`).

---

## 5. Exigences non fonctionnelles

| Réf | Exigence | Cible |
|---|---|---|
| ENF-1 | **Performance** | Chargement < 2 s, interactions fluides < 200 ms |
| ENF-2 | **Responsive** | Desktop, tablette, mobile (terrain) |
| ENF-3 | **Accessibilité** | Contrastes WCAG AA, cibles tactiles ≥ 44 px |
| ENF-4 | **Sécurité** | Authentification, gestion des rôles, mots de passe chiffrés |
| ENF-5 | **Disponibilité** | ≥ 99 % en heures ouvrées |
| ENF-6 | **Compatibilité** | Chrome, Edge, Firefox, Safari (versions récentes) |
| ENF-7 | **Sauvegarde** | Données sauvegardées quotidiennement |
| ENF-8 | **Internationalisation** | Architecture i18n FR/EN dès la conception |

---

## 6. Charte graphique

L'identité visuelle est dérivée du logo KayTechnologie : **bleu marine (LAN)** en couleur principale, **orange (Software)** en accent d'action, **bordeaux (Hardware)** pour les alertes, gris ardoise pour la structure. Typographie : **Archivo** (titres) + **IBM Plex Sans** (interface) + **IBM Plex Mono** (données).

Deux directions sont proposées et **toutes deux livrées dans `charte-graphique.css`**, sélectionnables à l'exécution :

- **Direction A — Corporate Sobre** : navigation latérale marine, tableaux denses.
- **Direction B — Opérations Modernes** : navigation horizontale, cartes aérées, rails de statut.

> Détail complet des tokens, composants et bascule : fichier **`charte-graphique.css`**.

---

## 7. Proposition technique (indicative)

| Couche | Proposition |
|---|---|
| **Front-end** | HTML / CSS / JavaScript (ou React) — charte `charte-graphique.css` |
| **Back-end** | API REST (Node.js/Express ou PHP/Laravel) |
| **Base de données** | PostgreSQL ou MySQL |
| **Authentification** | JWT / sessions, rôles |
| **Hébergement** | Serveur KayTechnologie ou cloud (à valider — voir `pdd.md`) |

---

## 8. Livrables

1. Cahier des charges (ce document).
2. Plan de déploiement (`pdd.md`).
3. Charte graphique exécutable (`charte-graphique.css`) — directions A & B.
4. Maquettes haute-fidélité (HTML).
5. Prototype interactif.
6. Application livrée + documentation utilisateur.

---

## 9. Critères d'acceptation

- ✅ Toutes les exigences **EF-1 à EF-10** sont opérationnelles.
- ✅ Les 5 statuts et leur code couleur sont respectés.
- ✅ Les tâches terminées disparaissent du tableau et sont archivées.
- ✅ Le tableau de bord affiche les 4 familles d'indicateurs.
- ✅ L'interface fonctionne sur mobile et bascule FR/EN.
- ✅ La bascule de charte A/B fonctionne.

---

## 10. Planning macro (indicatif)

| Phase | Durée estimée |
|---|---|
| Cadrage & validation CDC | 1 semaine |
| Maquettes & prototype | 1–2 semaines |
| Développement v1 | 4–6 semaines |
| Recette & corrections | 1–2 semaines |
| Déploiement & formation | 1 semaine |

---

*Document de travail — à amender après validation par le Manager KayTechnologie.*
