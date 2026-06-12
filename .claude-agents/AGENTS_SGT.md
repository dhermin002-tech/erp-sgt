# AGENTS_SGT.md — Référentiel des agents IA & automatisation MCP

> Objectif : **ne plus jamais récupérer les IDs des agents manuellement**.
> Le MCP résout désormais tout automatiquement.

## Correspondance ID ↔ agent (snapshot 12/06/2026)

| ID | agent_code | Rôle |
|----|-----------|------|
| 6  | dev-agent | Développement Blade/PHP/contrôleurs |
| 7  | qa-agent | Tests & validation |
| 8  | project-agent | Chef de projet (identité du MCP) |
| 9  | design-ui-agent | Design system, composants, écrans |
| 10 | audit-agent | Audit final / certification |
| 11 | expert-kt | Ingénierie senior |
| 12 | le-doyen-kt | Architecture / contrôle |

> ⚠️ **Les IDs peuvent changer** (recréation d'agents, reseed). **Ne jamais coder un ID en dur.**
> Toujours passer par le code de l'agent (résolu automatiquement) ou `lister_agents`.

## Automatisation en place (depuis 12/06/2026)

### 1. Endpoint API (existait déjà)
`GET /api/v1/agents` → renvoie `{ id, nom_complet, agent_code, agent_couleur }` pour chaque agent IA.

### 2. Outil MCP `lister_agents` (ajouté)
Appelle `/agents` et liste les agents avec leurs IDs. Remplace la commande tinker manuelle.

### 3. `creer_tache` accepte les **codes d'agents** (ajouté)
Le paramètre `responsables_codes` accepte des `agent_code` (ex: `['dev-agent','qa-agent']`).
Le MCP les résout en IDs via `/agents` **automatiquement** avant de créer la tâche.

```jsonc
// Avant (manuel) : il fallait connaître les IDs
creer_tache({ titre: "...", responsables: [9] })

// Maintenant (auto) : on donne le code, le MCP résout l'ID
creer_tache({ titre: "...", responsables_codes: ["design-ui-agent"] })
```

`responsables` (IDs humains) et `responsables_codes` (codes agents) sont **cumulables** ;
au moins l'un des deux doit être fourni.

## Procédure pour assigner une tâche à un agent (à l'avenir)

1. **Ne plus lancer de commande tinker.**
2. Utiliser directement `creer_tache` avec `responsables_codes: ["<agent-code>"]`.
3. Si besoin de voir la liste : appeler l'outil MCP `lister_agents`.

## Prérequis

- Le MCP `index.js` (`C:/laragon/www/sgt-mcp-server/index.js`) doit être à jour (15 outils).
- **Après toute modification du MCP, redémarrer Claude Code** pour recharger les outils.
- L'API SGT doit exposer `/api/v1/agents` (déjà le cas).

## Tâches de refonte design SGT (inscrites le 12/06/2026)

Voir le plan complet dans la session : Sprint 1 (DS + Login), Sprint 2 (Dashboard + Tâches),
Sprint 3 (Membres/Sites/Rapports/Agents IA + audit). Agents assignés : design-ui-agent (UI),
dev-agent (DashboardService, /design-system), qa-agent (tests), audit-agent (certification).
