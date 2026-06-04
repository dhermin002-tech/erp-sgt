# TASKS.md — SGT (Système de Gestion des Tâches)
**Généré par** : ProjectAgent
**Date** : 2026-06-05
**Total tâches** : 28 (P0: 5 | P1: 13 | P2: 7 | P3: 3)
**Sprints** : 7
**Durée totale estimée** : 9–11 semaines
**Budget tokens estimé** : ~620 000 tokens

---

## SPRINT 1 — Infrastructure & Auth (Semaine 1–2)
> **Agent principal** : `dev-agent` + `db-agent` + `infra-agent`
> Budget estimé : ~85 000 tokens

### [P0] INFRA-001 — Initialisation projet Laravel
**Epic** : Infrastructure
**Estimation tokens** : ~15 000
**Dépendances** : aucune
**Skills requis** : `laravel-php`, `laragon-windows-specifics`
**Agent** : dev-agent
**Critères d'acceptation** :
- [ ] Projet Laravel 11 créé dans `c:/laragon/www/erp.sgt/`
- [ ] Vhost Laragon configuré → `http://erp.sgt.test`
- [ ] `.env` configuré : DB_DATABASE=erp_sgt, APP_URL=http://erp.sgt.test
- [ ] `php artisan key:generate` exécuté
- [ ] `php artisan serve` ou Laragon répond en 200
**Statut** : ⬜ À faire

---

### [P0] INFRA-002 — Schéma BDD complet (migrations)
**Epic** : Infrastructure
**Estimation tokens** : ~20 000
**Dépendances** : INFRA-001
**Skills requis** : `laravel-php`, `db-agent`, `database-mysql`
**Agent** : dev-agent + db-agent
**Tables à créer** :
- `users` (id, username, password, role, remember_token, timestamps, soft_delete)
- `sites` (id, nom, ville, timestamps)
- `taches` (id, titre, description, responsable_id, site_id, date_debut, date_echeance, statut, progression, priorite, archived_at, timestamps, soft_delete)
- `sous_taches` (id, tache_id, titre, statut, ordre, timestamps)
- `tache_user` (pivot tâche ↔ responsables multiples)
- `commentaires` (id, tache_id, user_id, contenu, photo_path, timestamps, soft_delete)
- `notifications` (id, user_id, type, data JSON, read_at, timestamps)
- `rapports` (id, tache_id, user_id, contenu, timestamps)
- `actions_suivi` (id, tache_id, user_id, description, fait BOOL, timestamps)
**Critères d'acceptation** :
- [ ] Toutes les migrations exécutées sans erreur
- [ ] Contraintes FK explicites (ON DELETE CASCADE/SET NULL selon cas)
- [ ] `php artisan migrate:fresh` propre
- [ ] Seeders : 5 rôles, 3 sites Gabon (Libreville, Akanda, Owendo), 1 admin Manager
**Statut** : ⬜ À faire

---

### [P0] AUTH-001 — Authentification par username + RBAC 5 rôles
**Epic** : Auth & Sécurité
**Estimation tokens** : ~20 000
**Dépendances** : INFRA-002
**Skills requis** : `laravel-php`, `security-web`
**Agent** : dev-agent
**Critères d'acceptation** :
- [ ] Login par `username` (jamais email) avec bcrypt
- [ ] 5 rôles : manager, technicien, agent, developpeur, stagiaire
- [ ] Middleware `role:manager` etc. fonctionnel sur toutes les routes
- [ ] Redirection post-login selon rôle
- [ ] Logout CSRF-safe (`fetch()` + `.finally()`) — ERR-PHP-002
- [ ] Tests PHPUnit : login valide, mauvais mdp, accès par rôle
**Statut** : ⬜ À faire

---

### [P0] INFRA-003 — Layout principal + intégration charte-graphique.css
**Epic** : Infrastructure UI
**Estimation tokens** : ~18 000
**Dépendances** : AUTH-001
**Skills requis** : `laravel-php`, `design-ui-agent`, `frontend-design`
**Agent** : dev-agent + design-ui-agent
**Critères d'acceptation** :
- [ ] Layout `layouts/app.blade.php` avec sidebar (Dir A) et nav haute (Dir B)
- [ ] `charte-graphique.css` et `theme-switcher.js` liés depuis `public/`
- [ ] Bascule Direction A/B fonctionnelle (localStorage persistant)
- [ ] Header : nom utilisateur + rôle + logout
- [ ] Menu latéral : liens Tâches, Dashboard, Archive, Membres (Manager only)
- [ ] Responsive mobile (hamburger sur ≤ 768 px)
- [ ] ERR-CSS-001 vérifié (flex scroll + min-height:0)
**Statut** : ⬜ À faire

---

### [P0] INFRA-004 — CI/CD GitHub Actions + phpunit.xml SQLite
**Epic** : Infrastructure CI
**Estimation tokens** : ~12 000
**Dépendances** : INFRA-001
**Skills requis** : `ci-cd-github`, `qa-testing`
**Agent** : dev-agent
**Critères d'acceptation** :
- [ ] `.github/workflows/tests.yml` créé (PHP 8.3, cache Composer, SQLite in-memory)
- [ ] `phpunit.xml` configuré DB_CONNECTION=sqlite, DB_DATABASE=:memory:
- [ ] Badge `[![Tests CI]...]` dans README.md
- [ ] `php artisan test` passe en local avant tout push
**Statut** : ⬜ À faire

---

## SPRINT 2 — Gestion des Tâches Core (Semaines 3–4)
> **Agent principal** : `dev-agent` + `design-ui-agent`
> Budget estimé : ~120 000 tokens
> **Refs CDC** : EF-1, EF-2, EF-3

### [P1] FEAT-001 — CRUD Tâches complet (EF-1)
**Epic** : Tâches
**Estimation tokens** : ~35 000
**Dépendances** : INFRA-002, AUTH-001, INFRA-003
**Skills requis** : `laravel-php`, `design-ui-agent`
**Agent** : dev-agent + design-ui-agent
**Critères d'acceptation** :
- [ ] `Route::resource('taches', TacheController::class)` avec toutes les vues (index, create, show, edit) — ERR-PHP-001
- [ ] Champs formulaire : titre, description, responsable(s), site, date_debut, date_echeance, statut, progression%, priorité
- [ ] Affectation **multi-responsables** (select multiple ou tags)
- [ ] Liste paginée avec triable par colonne (Bootstrap, pas Tailwind) — ERR-PHP-003
- [ ] FormRequest validation (titre requis, dates cohérentes, responsable requis)
- [ ] Messages flash FR (succès vert, erreur rouge)
- [ ] Le Manager voit toutes les tâches ; les autres voient uniquement les leurs
**Statut** : ⬜ À faire

---

### [P1] FEAT-002 — Sous-tâches + progression automatique (EF-2)
**Epic** : Tâches
**Estimation tokens** : ~20 000
**Dépendances** : FEAT-001
**Skills requis** : `laravel-php`, `design-ui-agent`
**Agent** : dev-agent + design-ui-agent
**Critères d'acceptation** :
- [ ] Section sous-tâches dépliable dans la vue `show` de la tâche
- [ ] CRUD inline sous-tâches (AJAX Alpine.js)
- [ ] Cocher une sous-tâche met à jour son statut
- [ ] `progression` de la tâche parente = (sous-tâches terminées / total) × 100, recalculé auto
- [ ] Barre de progression visuelle (Bootstrap progress bar)
**Statut** : ⬜ À faire

---

### [P1] FEAT-003 — Statuts + code couleur (5 statuts, EF-3)
**Epic** : Tâches
**Estimation tokens** : ~15 000
**Dépendances** : FEAT-001
**Skills requis** : `laravel-php`, `design-ui-agent`
**Agent** : dev-agent + design-ui-agent
**Critères d'acceptation** :
- [ ] Enum PHP `StatutTache` : `nouveau`, `en_cours`, `en_attente`, `en_arret`, `termine`
- [ ] Badges couleur respectant exactement les tokens CDC : `#64748B` / `#2563EB` / `#C97A0A` / `#B0202E` / `#15885A`
- [ ] Changement de statut en 1 clic (dropdown Alpine.js ou AJAX patch) avec mise à jour visuelle immédiate
- [ ] Tests PHPUnit : transitions de statut valides
**Statut** : ⬜ À faire

---

### [P1] FEAT-004 — Affectation responsables + sites d'intervention (EF-1.3, EF-1.4)
**Epic** : Tâches
**Estimation tokens** : ~12 000
**Dépendances** : FEAT-001
**Skills requis** : `laravel-php`
**Agent** : dev-agent
**Critères d'acceptation** :
- [ ] CRUD Sites (Manager only) : nom, ville
- [ ] Select "site d'intervention" dans formulaire tâche
- [ ] Filtrage tâches par site fonctionnel
- [ ] Filtre liste responsables (membres actifs uniquement)
**Statut** : ⬜ À faire

---

## SPRINT 3 — Dates, Commentaires & Notifications (Semaines 5–6)
> **Agent principal** : `dev-agent`
> Budget estimé : ~90 000 tokens
> **Refs CDC** : EF-4, EF-5

### [P1] FEAT-005 — Détection automatique des retards (EF-4)
**Epic** : Planning
**Estimation tokens** : ~15 000
**Dépendances** : FEAT-001
**Skills requis** : `laravel-php`
**Agent** : dev-agent
**Critères d'acceptation** :
- [ ] Scope Eloquent `enRetard()` : `date_echeance < today AND statut != termine`
- [ ] Badge rouge "En retard" visible dans la liste et la vue `show`
- [ ] KPI "Tâches en retard" alimenté (préparation Sprint 5)
- [ ] Scheduler Laravel (optionnel) : notification quotidienne aux responsables si retard
**Statut** : ⬜ À faire

---

### [P1] FEAT-006 — Fil de commentaires + photos terrain (EF-5.1, EF-5.2)
**Epic** : Collaboration
**Estimation tokens** : ~25 000
**Dépendances** : FEAT-001
**Skills requis** : `laravel-php`, `design-ui-agent`
**Agent** : dev-agent + design-ui-agent
**Critères d'acceptation** :
- [ ] Fil de commentaires chronologique dans la vue `show`
- [ ] Formulaire d'ajout de commentaire (texte + @mention membre)
- [ ] Upload photo chantier (stockage `storage/app/public/photos/`, `php artisan storage:link`)
- [ ] Miniature cliquable + lightbox Bootstrap
- [ ] Soft delete commentaire (auteur ou Manager)
**Statut** : ⬜ À faire

---

### [P1] FEAT-007 — Notifications in-app (EF-5.3)
**Epic** : Collaboration
**Estimation tokens** : ~22 000
**Dépendances** : FEAT-006
**Skills requis** : `laravel-php`, `laravel-queues-jobs`
**Agent** : dev-agent
**Critères d'acceptation** :
- [ ] Notification créée pour : nouvelle affectation, changement statut, échéance J-1, mention dans commentaire
- [ ] Cloche dans le header avec badge compteur non-lus
- [ ] Liste déroulante dernières 10 notifications avec lien vers la tâche
- [ ] Marquage "lu" au clic ou "Tout marquer lu"
- [ ] Queue Laravel (driver database) pour dispatch asynchrone
**Statut** : ⬜ À faire

---

## SPRINT 4 — Rapports & Archivage (Semaines 7)
> **Agent principal** : `dev-agent` + `design-ui-agent`
> Budget estimé : ~70 000 tokens
> **Refs CDC** : EF-6, EF-7

### [P1] FEAT-008 — Rapports & actions à entreprendre (EF-6)
**Epic** : Rapports
**Estimation tokens** : ~25 000
**Dépendances** : FEAT-001
**Skills requis** : `laravel-php`, `design-ui-agent`
**Agent** : dev-agent + design-ui-agent
**Critères d'acceptation** :
- [ ] Onglet "Rapports & Actions" dans la vue `show` de la tâche
- [ ] Saisie de compte-rendu d'intervention (textarea + date + auteur)
- [ ] Liste d'actions de suivi : description + checkbox "Fait / À faire"
- [ ] Rapport global exportable (liste des tâches avec leurs rapports) — PDF DomPDF si demandé
- [ ] Images PDF en base64 (ERR-PHP-004)
**Statut** : ⬜ À faire

---

### [P1] FEAT-009 — Archivage + restauration des tâches terminées (EF-7)
**Epic** : Archivage
**Estimation tokens** : ~18 000
**Dépendances** : FEAT-003
**Skills requis** : `laravel-php`
**Agent** : dev-agent
**Critères d'acceptation** :
- [ ] Quand statut passe à `termine` : tâche masquée du tableau actif (scope `actives()`)
- [ ] Page `/taches/archives` listant les tâches terminées (Manager + auteur)
- [ ] Bouton "Restaurer" remet `statut = nouveau` et sors de l'archive
- [ ] Compteur "X tâches archivées ce mois" dans le dashboard
**Statut** : ⬜ À faire

---

## SPRINT 5 — Tableau de Bord (Semaine 8)
> **Agent principal** : `dev-agent` + `design-ui-agent`
> Budget estimé : ~80 000 tokens
> **Refs CDC** : EF-8

### [P1] FEAT-010 — KPI Cards + filtres dashboard (EF-8.1, EF-8.5)
**Epic** : Dashboard
**Estimation tokens** : ~20 000
**Dépendances** : FEAT-001, FEAT-005, FEAT-009
**Skills requis** : `laravel-php`, `design-ui-agent`
**Agent** : dev-agent + design-ui-agent
**Critères d'acceptation** :
- [ ] 4 KPI cards : Tâches actives · En cours · Taux de complétion % · En retard (rouge alerte)
- [ ] Filtres : période (7j/30j/trim/tout), responsable, site
- [ ] Accès `/dashboard` : Manager = tous les membres ; autres = leurs données uniquement
- [ ] Cartes responsive mobile (2 colonnes → 1 colonne)
**Statut** : ⬜ À faire

---

### [P1] FEAT-011 — Graphiques Chart.js (EF-8.2, EF-8.3, EF-8.4)
**Epic** : Dashboard
**Estimation tokens** : ~30 000
**Dépendances** : FEAT-010
**Skills requis** : `laravel-php`, `design-ui-agent`
**Agent** : dev-agent + design-ui-agent
**Critères d'acceptation** :
- [ ] Donut **Répartition par statut** (couleurs exactes CDC)
- [ ] Courbe **Avancement dans le temps** (tâches terminées vs créées, 30 derniers jours)
- [ ] Barres **Charge par responsable** (nb tâches actives par membre)
- [ ] Données alimentées par API JSON interne (`/dashboard/data?...`)
- [ ] Charts responsive (canvas resize)
- [ ] Couleurs chart = tokens `charte-graphique.css`
**Statut** : ⬜ À faire

---

## SPRINT 6 — Recherche, i18n & Thème A/B (Semaine 9)
> **Agent principal** : `dev-agent` + `design-ui-agent`
> Budget estimé : ~75 000 tokens
> **Refs CDC** : EF-9, EF-10

### [P2] FEAT-012 — Tri, filtres, recherche plein texte (EF-9)
**Epic** : UX Tableau
**Estimation tokens** : ~22 000
**Dépendances** : FEAT-001
**Skills requis** : `laravel-php`, `design-ui-agent`
**Agent** : dev-agent + design-ui-agent
**Critères d'acceptation** :
- [ ] En-têtes de colonne cliquables → tri ASC/DESC (Alpine.js ou Livewire)
- [ ] Filtres sidebar/dropdown : statut, responsable, site, date échéance (plage)
- [ ] Barre de recherche plein texte sur titre + description (LIKE ou Full-Text MySQL)
- [ ] URL persistante des filtres (query params)
**Statut** : ⬜ À faire

---

### [P2] FEAT-013 — Internationalisation FR / EN complète (EF-10.1)
**Epic** : i18n
**Estimation tokens** : ~25 000
**Dépendances** : INFRA-003
**Skills requis** : `laravel-php`
**Agent** : dev-agent
**Critères d'acceptation** :
- [ ] Fichiers `lang/fr/*.php` et `lang/en/*.php` pour tous les labels, messages, emails
- [ ] Bascule FR/EN dans le header (flag + store en session/localStorage)
- [ ] `App::setLocale()` appliqué à chaque requête selon préférence utilisateur
- [ ] Tous les messages flash, validations et labels traduits
**Statut** : ⬜ À faire

---

### [P2] FEAT-014 — Bascule charte Direction A / B (EF-10.2)
**Epic** : Thème
**Estimation tokens** : ~12 000
**Dépendances** : INFRA-003
**Skills requis** : `design-ui-agent`, `laravel-php`
**Agent** : design-ui-agent + dev-agent
**Critères d'acceptation** :
- [ ] Sélecteur Direction A/B accessible dans les préférences utilisateur (profil ou header)
- [ ] Préférence sauvegardée en BDD (colonne `direction_ui` sur `users`)
- [ ] `data-direction="A|B"` appliqué sur `<html>` via `@auth` + `theme-switcher.js`
- [ ] Les deux directions sont visuellement distinctes et correctes sur mobile
**Statut** : ⬜ À faire

---

### [P2] FEAT-015 — Vues Kanban et Calendrier (EF-4.3 — option)
**Epic** : Vues alternatives
**Estimation tokens** : ~28 000
**Dépendances** : FEAT-001, FEAT-003
**Skills requis** : `laravel-php`, `design-ui-agent`
**Agent** : dev-agent + design-ui-agent
**Critères d'acceptation** :
- [ ] Vue Kanban : colonnes = 5 statuts, drag & drop (sortable.js ou Alpine.js)
- [ ] Vue Calendrier : FullCalendar.js ou mini-calendrier Bootstrap affichant les échéances
- [ ] Bascule tableau / kanban / calendrier en haut de la liste
**Statut** : ⬜ À faire

---

## SPRINT 7 — Tests, Sécurité & Déploiement (Semaines 10–11)
> **Agents** : `qa-agent` + `security-agent` + `deploy-agent`
> Budget estimé : ~80 000 tokens

### [P1] TEST-001 — Tests PHPUnit Auth + RBAC
**Epic** : Tests
**Estimation tokens** : ~15 000
**Dépendances** : AUTH-001
**Skills requis** : `qa-testing`, `qa-agent`
**Agent** : qa-agent
**Critères d'acceptation** :
- [ ] `tests/Feature/Auth/LoginTest.php` : login username, mauvais mot de passe, chaque rôle
- [ ] Test accès interdit : stagiaire ne peut pas créer/modifier tâche d'un autre
- [ ] Test Manager voit toutes les tâches, technicien uniquement les siennes
- [ ] 100 % tests verts (`php artisan test`)
**Statut** : ⬜ À faire

---

### [P1] TEST-002 — Tests PHPUnit Tâches + Sous-tâches + Statuts
**Epic** : Tests
**Estimation tokens** : ~18 000
**Dépendances** : FEAT-001, FEAT-002, FEAT-003
**Skills requis** : `qa-testing`, `qa-agent`
**Agent** : qa-agent
**Critères d'acceptation** :
- [ ] CRUD tâches : création, modification, suppression (soft delete)
- [ ] Calcul progression automatique (sous-tâches terminées / total)
- [ ] Transitions de statut valides et invalides
- [ ] Détection retard (scope `enRetard()`)
- [ ] Archivage + restauration
**Statut** : ⬜ À faire

---

### [P2] TEST-003 — Tests Dashboard + API JSON
**Epic** : Tests
**Estimation tokens** : ~12 000
**Dépendances** : FEAT-010, FEAT-011
**Skills requis** : `qa-testing`, `api-testing`
**Agent** : qa-agent
**Critères d'acceptation** :
- [ ] Endpoint `/dashboard/data` retourne JSON correct avec filtres
- [ ] KPI compteurs cohérents avec les données seeder
- [ ] Accès non-Manager filtre les données sur ses propres tâches
**Statut** : ⬜ À faire

---

### [P2] SEC-001 — Audit sécurité (OWASP)
**Epic** : Sécurité
**Estimation tokens** : ~10 000
**Dépendances** : TEST-001, TEST-002
**Skills requis** : `security-agent`, `security-web`
**Agent** : security-agent
**Critères d'acceptation** :
- [ ] CSRF sur tous les formulaires
- [ ] XSS : toutes les sorties Blade passent par `{{ }}` ou `e()`
- [ ] Policies Laravel sur TacheController (authorize)
- [ ] Upload photos : validation extension (jpg/png/webp), taille max 5 Mo
- [ ] Rapport `SECURITY_REPORT.md` généré
**Statut** : ⬜ À faire

---

### [P1] DEPLOY-001 — Déploiement VPS production
**Epic** : Déploiement
**Estimation tokens** : ~18 000
**Dépendances** : TEST-001, TEST-002, SEC-001
**Skills requis** : `deploy-agent`, `infra-agent`, `deployment-hosting`
**Agent** : deploy-agent + infra-agent
**Critères d'acceptation** :
- [ ] Code pushé sur GitHub → CI passe en vert
- [ ] `git pull` sur VPS (72.61.195.69), `composer install --no-dev`, migrations
- [ ] Nginx configuré → `sgt.kaytechnologie.online` avec SSL Certbot
- [ ] `APP_DEBUG=false`, `APP_ENV=production` dans `.env` prod
- [ ] Accès production fonctionnel pour 5 rôles
- [ ] Sauvegarde BDD automatique quotidienne configurée
**Statut** : ⬜ À faire

---

## BACKLOG P3 — Nice-to-have (post-v1)

### [P3] BONUS-001 — Application mobile native (React Native Expo)
**Note** : Hors périmètre v1 per CDC section 2.2. Dépend du succès de la v1.
**Skills** : `react-native-mobile`, `flutter-kaytech`
**Statut** : ⬜ Backlog

### [P3] BONUS-002 — Export PDF rapports globaux (DomPDF)
**Note** : Optionnel v1, prioritaire si demande Manager.
**Skills** : `laravel-php`
**Statut** : ⬜ Backlog

### [P3] BONUS-003 — Intégration SMS notification retard (Africa's Talking)
**Skills** : `sms-notifications`
**Statut** : ⬜ Backlog
