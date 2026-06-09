# BUGS.md — SGT (Système de Gestion des Tâches)
**Mis à jour par** : SecurityAgent
**Dernière mise à jour** : 2026-06-09

| ID | Priorité | Tâche liée | Description | Statut |
|----|----------|------------|-------------|--------|
| SEC-001 | 🔴 P0 | Sécurité | `APP_DEBUG=true` et `APP_ENV=local` actifs sur le VPS — à corriger dans `.env` production | ✅ Corrigé — 2026-06-09 VPS |
| SEC-002 | 🔴 P0 | Auth | Pas de throttle sur `POST /login` — brute-force possible | ✅ Corrigé — commit d088382 |
| SEC-003 | 🟠 P1 | TacheController | IDOR sur `restaurer()` — manque `authorizeAccess()` | ✅ Corrigé — commit d088382 |
| SEC-004 | 🟠 P1 | Middleware | Headers de sécurité HTTP absents (X-Frame-Options, CSP) | ✅ Corrigé — SecureApiHeaders ajouté aux routes web — commit à pousser |
| SEC-005 | 🟠 P1 | Config | `SESSION_ENCRYPT=false` — sessions non chiffrées | ✅ Corrigé — 2026-06-09 VPS |
| SEC-006 | 🟡 P2 | MembresController | Mot de passe minimum 6 chars (trop court) | ✅ Corrigé — min:8 — commit d088382 |
| SEC-007 | 🟡 P2 | Middleware | Pas de Content-Security-Policy | 🔴 Ouvert |
| SEC-008 | 🟡 P2 | SousTache/ActionSuivi | Pas de contrôle d'accès sur la tâche parente | ✅ Corrigé — abort_unless() ajouté dans store() des 2 controllers — commit à pousser |
| SEC-009 | 🟢 P3 | .env.example | `.env.example` contient `APP_DEBUG=true` | 🔴 Ouvert |

---

## Légende priorités

| Niveau | Signification |
|---|---|
| P0 | Bloquant — production inutilisable |
| P1 | Critique — fonctionnalité majeure cassée |
| P2 | Modéré — dégradation visible mais contournable |
| P3 | Mineur — cosmétique ou edge case |

## Légende statuts

| Statut | Icône |
|---|---|
| Ouvert | 🔴 |
| En cours de correction | 🟡 |
| Corrigé — en attente de test | 🔵 |
| Fermé | ✅ |
