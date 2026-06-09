# SECURITY_REPORT.md — SGT KayTech (Laravel 11)
**Audité par** : SecurityAgent  
**Date** : 2026-06-09  
**Niveau de risque global** : 🟠 ÉLEVÉ

---

## Résumé exécutif

| Sévérité | Nombre | Bloquant déploiement |
|----------|--------|----------------------|
| 🔴 P0 Critique | 2 | OUI |
| 🟠 P1 Élevé | 3 | OUI |
| 🟡 P2 Modéré | 3 | NON |
| 🟢 P3 Faible | 2 | NON |
| **TOTAL** | **10** | |

---

## Vulnérabilités détectées

---

### 🔴 SEC-001 — APP_DEBUG=true en environnement actif

**Fichier** : `.env:18`  
**Code problématique** :
```
APP_ENV=local
APP_DEBUG=true
```
**Impact** : En cas d'erreur, Laravel expose la stack trace complète, les variables d'environnement, les chemins du serveur, et potentiellement les valeurs des variables (dont APP_KEY, mots de passe BDD). Exploitable pour cartographier l'infrastructure.  
**Correction** :
```
APP_ENV=production
APP_DEBUG=false
```
> Note : Le fichier `config/app.php` a déjà `'debug' => (bool) env('APP_DEBUG', false)` — le défaut est correct, mais `.env` l'écrase explicitement à `true`. C'est ce `.env` qu'il faut corriger avant toute mise en production.

---

### 🔴 SEC-002 — Absence de throttle sur la route POST /login (web)

**Fichier** : `routes/web.php:19`  
**Code problématique** :
```php
Route::post('/login', [LoginController::class, 'login'])->middleware('guest');
```
**Impact** : La route de connexion web n'a aucun rate limiting. Un attaquant peut lancer une attaque par force brute (brute-force / credential stuffing) sans limitation de tentatives.  
**Note positive** : La route API `/v1/auth/token` est correctement protégée (`throttle:5,1` dans `routes/api.php`).  
**Correction** :
```php
Route::post('/login', [LoginController::class, 'login'])
    ->middleware(['guest', 'throttle:5,1'])
    ->name('login.submit');
```

---

### 🟠 SEC-003 — TacheController::restaurer() sans contrôle d'accès

**Fichier** : `app/Http/Controllers/TacheController.php:159`  
**Code problématique** :
```php
public function restaurer(Tache $tache)
{
    $tache->update(['statut' => 'nouveau', 'archived_at' => null]);
    return redirect()->route('taches.archives')
                     ->with('success', 'Tâche restaurée.');
}
```
**Impact** : Toutes les autres actions sensibles (`show`, `edit`, `update`, `destroy`, `patchStatut`) appellent `$this->authorizeAccess($tache)`, mais `restaurer()` ne le fait pas. N'importe quel utilisateur authentifié peut restaurer une tâche archivée qui ne lui appartient pas.  
**Correction** :
```php
public function restaurer(Tache $tache)
{
    $this->authorizeAccess($tache); // ← ajouter cette ligne
    $tache->update(['statut' => 'nouveau', 'archived_at' => null]);
    return redirect()->route('taches.archives')
                     ->with('success', 'Tâche restaurée.');
}
```

---

### 🟠 SEC-004 — Headers de sécurité absents sur les routes WEB

**Fichier** : `bootstrap/app.php:17-19`  
**Code problématique** :
```php
$middleware->api(append: [
    \App\Http\Middleware\SecureApiHeaders::class,
]);
// Le middleware SecureApiHeaders n'est PAS appliqué aux routes web
```
**Impact** : Les routes web (`/login`, `/dashboard`, `/taches`, etc.) ne reçoivent pas les headers `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`, `X-XSS-Protection`, `Referrer-Policy`. Risque de clickjacking sur toutes les pages de l'application.  
**Correction** — Appliquer aussi aux routes web dans `bootstrap/app.php` :
```php
$middleware->web(append: [
    \App\Http\Middleware\SetLocale::class,
    \App\Http\Middleware\SecureApiHeaders::class, // ← ajouter
]);
```

---

### 🟠 SEC-005 — SESSION_ENCRYPT=false (sessions non chiffrées en BDD)

**Fichier** : `.env:32`  
**Code problématique** :
```
SESSION_DRIVER=database
SESSION_ENCRYPT=false
```
**Impact** : Les sessions sont stockées en clair dans la base de données. Si un attaquant obtient un accès en lecture à la BDD (SQL injection sur un autre composant, dump de base, accès physique), il peut lire ou voler des sessions actives sans aucun obstacle.  
**Correction** :
```
SESSION_ENCRYPT=true
```
> Laravel chiffre les données de session avec `APP_KEY`. Aucun changement de code requis.

---

### 🟡 SEC-006 — Mot de passe minimum trop court (6 caractères)

**Fichier** : `app/Http/Controllers/MembresController.php:29` et `:58`  
**Code problématique** :
```php
'password' => 'required|string|min:6|confirmed',
// et
'password' => 'nullable|string|min:6|confirmed',
```
**Impact** : Les mots de passe de 6 caractères sont facilement brute-forcés. OWASP recommande un minimum de 8 caractères (idéalement 12+).  
**Correction** :
```php
'password' => 'required|string|min:8|confirmed',
// et
'password' => 'nullable|string|min:8|confirmed',
```

---

### 🟡 SEC-007 — Absence de Content-Security-Policy (CSP)

**Fichier** : `app/Http/Middleware/SecureApiHeaders.php`  
**Impact** : Sans CSP, si une XSS était introduite dans le futur (via une dépendance compromise ou un oubli), le navigateur exécuterait les scripts injectés sans restriction. Le middleware actuel ne définit pas ce header.  
**Correction** — Ajouter dans `SecureApiHeaders.php` :
```php
$response->headers->set(
    'Content-Security-Policy',
    "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data: blob:; font-src 'self'; connect-src 'self'"
);
```

---

### 🟡 SEC-008 — SousTacheController et ActionSuiviController : pas de vérification d'appartenance à la tâche parente

**Fichier** : `app/Http/Controllers/SousTacheController.php:11`  
**Fichier** : `app/Http/Controllers/ActionSuiviController.php:11`  
**Code problématique** :
```php
public function store(Request $request, Tache $tache)
{
    $request->validate(['titre' => 'required|string|max:255']);
    // Aucune vérification que l'utilisateur a accès à $tache
    $st = $tache->sousTaches()->create([...]);
```
**Impact** : Un technicien peut créer des sous-tâches ou actions de suivi sur n'importe quelle tâche en forgeant une requête POST avec l'ID d'une tâche quelconque. IDOR (Insecure Direct Object Reference) partiel.  
**Correction** — Ajouter dans les méthodes `store()` des deux controllers :
```php
public function store(Request $request, Tache $tache)
{
    // Vérifier l'accès à la tâche parente
    $user = Auth::user();
    if (! $user->isManager()) {
        $peutAcceder = $tache->createur_id === $user->id
            || $tache->responsables()->where('users.id', $user->id)->exists();
        abort_unless($peutAcceder, 403, 'Accès refusé.');
    }
    $request->validate(['titre' => 'required|string|max:255']);
    // ...
}
```

---

### 🟢 SEC-009 — .env.example expose APP_DEBUG=true comme valeur par défaut

**Fichier** : `.env.example:4`  
**Code problématique** :
```
APP_DEBUG=true
```
**Impact** : Un développeur qui copie `.env.example` sans modifier les valeurs se retrouve avec `APP_DEBUG=true`. Risque de mauvaise configuration involontaire sur un serveur de staging ou production.  
**Correction** :
```
APP_DEBUG=false
# Mettre à true UNIQUEMENT en développement local — JAMAIS en production
```

---

### 🟢 SEC-010 — Logout via POST (conforme) — Audit informatif

**Fichier** : `routes/web.php:20`  
**Analyse** : `Route::post('/logout', ...)` — CONFORME. Le logout est bien en POST, protégé par `@csrf` (confirmé dans `resources/views/layouts/app.blade.php:369`). Aucune correction nécessaire.  
**Recommandation** : Ajouter un commentaire dans le code pour documenter ce choix de sécurité délibéré.

---

## Points CONFORMES (positifs)

| Check | Statut | Détail |
|-------|--------|--------|
| .gitignore protège .env | ✅ CONFORME | `.env` listé dans `.gitignore` |
| Pas de secrets hardcodés dans les controllers | ✅ CONFORME | Aucun credential en dur trouvé |
| Pas d'injection SQL | ✅ CONFORME | Pas de `DB::statement`/`DB::select` avec interpolation, ni `whereRaw` non bindé |
| Pas de XSS `{!! !!}` dans les vues Blade | ✅ CONFORME | Aucune occurrence trouvée |
| CSRF sur tous les formulaires POST/PUT/DELETE | ✅ CONFORME | `@csrf` présent sur tous les formulaires |
| Mass assignment sécurisé | ✅ CONFORME | Tous les modèles ont `$fillable` explicite |
| TacheController : authorizeAccess() | ⚠️ PARTIEL | show/edit/update/destroy/patchStatut OK — restaurer() manquant (SEC-003) |
| MembresController protégé `role:manager` | ✅ CONFORME | `->middleware('role:manager')` appliqué |
| SiteController protégé `role:manager` | ✅ CONFORME | `->middleware('role:manager')` appliqué |
| Logout POST (pas GET) | ✅ CONFORME | `Route::post('/logout', ...)` |
| config/app.php : `APP_DEBUG` défaut = false | ✅ CONFORME | `'debug' => (bool) env('APP_DEBUG', false)` |
| Routes API : auth:sanctum | ✅ CONFORME | Toutes les routes `/v1/*` sous `auth:sanctum` |
| Throttle API login | ✅ CONFORME | `throttle:5,1` sur `POST /v1/auth/token` |
| Bcrypt rounds | ✅ CONFORME | `BCRYPT_ROUNDS=12` (recommandé: ≥10) |
| Mots de passe hachés (bcrypt) | ✅ CONFORME | `Hash::make()` utilisé partout, cast `hashed` sur User |
| Tokens Sanctum révoqués au logout API | ✅ CONFORME | `currentAccessToken()->delete()` dans `ApiTokenController::revoke()` |
| SecureApiHeaders middleware (API) | ✅ CONFORME | X-Frame-Options, X-Content-Type-Options, X-XSS-Protection présents sur API |
| Validation sur tous les controllers POST/PUT | ✅ CONFORME | `validate()` ou `FormRequest` présent partout |
| Upload image : vérification type MIME | ✅ CONFORME | `mimes:jpg,jpeg,png,webp\|max:5120` dans CommentaireController |
| RoleMiddleware côté serveur | ✅ CONFORME | Vérification `$request->user()->role` côté serveur |

---

## Plan de correction priorisé

### Corrections OBLIGATOIRES avant déploiement (P0 + P1)

| # | Action | Fichier | Effort |
|---|--------|---------|--------|
| 1 | `APP_ENV=production`, `APP_DEBUG=false` | `.env` | 2 min |
| 2 | Ajouter `throttle:5,1` sur `POST /login` | `routes/web.php:19` | 2 min |
| 3 | Ajouter `$this->authorizeAccess($tache)` dans `restaurer()` | `TacheController.php:159` | 1 min |
| 4 | Appliquer `SecureApiHeaders` aux routes web | `bootstrap/app.php` | 2 min |
| 5 | `SESSION_ENCRYPT=true` | `.env` | 1 min |

### Corrections recommandées (P2)

| # | Action | Fichier | Effort |
|---|--------|---------|--------|
| 6 | Augmenter `min:6` à `min:8` | `MembresController.php:29,58` | 2 min |
| 7 | Ajouter `Content-Security-Policy` | `SecureApiHeaders.php` | 5 min |
| 8 | Contrôle accès tâche parente dans SousTacheController et ActionSuiviController | 2 fichiers | 10 min |

---

## Certification sécurité

```
⛔ NON CONFORME
```

**Motif** : 2 vulnérabilités P0 et 3 vulnérabilités P1 détectées — déploiement en production BLOQUÉ.

**Conditions de re-certification CONFORME** :
- [ ] SEC-001 : `APP_DEBUG=false`, `APP_ENV=production`
- [ ] SEC-002 : `throttle:5,1` sur `POST /login` web
- [ ] SEC-003 : `authorizeAccess()` dans `restaurer()`
- [ ] SEC-004 : `SecureApiHeaders` sur routes web
- [ ] SEC-005 : `SESSION_ENCRYPT=true`

Une fois ces 5 corrections appliquées, relancer `security-agent quick erp.sgt` pour re-certification.

---

*Rapport généré par SecurityAgent — SGT KayTech Laravel 11*  
*Périmètre audité : controllers (17), routes (web.php + api.php), modèles (7), vues Blade (21), .env, config/app.php, middlewares (3), bootstrap/app.php*
