# REVIEW.md — SGT KayTech

## Revue du 18/06/2026 — Expert-KT + qa-agent (mode allégé)
**Demandé par** : Project-Agent (bug prod : impossible de valider sous-tâches/tâches)
**Contexte** : Le manager ne pouvait valider ni sous-tâches ni tâches ; clic sans effet, aucune alerte.

**Diagnostic Expert-KT** : backend vert (tests) + clic sans effet = problème runtime navigateur ou
chaîne de livraison, pas le code métier. Protocole imposé : isoler par couche (environnement →
réseau → console JS → version déployée), aucune supposition.

**Cause racine identifiée** : `const csrfToken` redéclaré dans `taches/show.blade.php`
(`@push('scripts')`) alors que `layouts/app.blade.php` le déclare déjà au niveau global →
`SyntaxError: Identifier 'csrfToken' has already been declared` → tout le bloc `<script>` de la
page rejeté → fonctions de validation jamais définies. Bug présent depuis toujours.

**Verdict** : Résolu ✅ — confirmé en production par l'utilisateur après déploiement + view:clear + Ctrl+F5.

**Points validés** :
- Suppression de la redéclaration `csrfToken` (commit 04c3cd0) — LE fix bloquant
- Fin de l'archivage automatique sur "terminé" + section "Terminées" dépliable + bouton Archiver (e103074)
- Durcissement AJAX sgtFetch : surface 419/403/500 au lieu de les avaler (ff123f2)
- Test garde-fou : `const csrfToken` doit apparaître 1 seule fois dans le HTML rendu

**Risques identifiés / dette** :
- Pas de CI/CD de déploiement (deploy 100% manuel, SSH par mot de passe) → risque de "code poussé mais non déployé"
- Pas de test JS exécuté (Dusk présent mais non lancé en CI) → les SyntaxError JS passent sous le radar des tests PHPUnit

**Corrections appliquées** : 3 commits déployés en prod ; 5 tests ajoutés (144 verts) ; leçon
mémorisée (err-js-const-redeclare).

**Recommandations qa-agent (backlog)** :
1. Ajouter un workflow GitHub Actions de déploiement (git pull + view:clear + smoke test) déclenché sur push main.
2. Ajouter au moins 1 test Dusk sur la page tâche (cocher sous-tâche, changer statut) pour couvrir le JS.
