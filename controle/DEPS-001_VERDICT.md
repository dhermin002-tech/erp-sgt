# DEPS-001 — Verdict consolidé — 28/11/2026

Lot : `b5b8613` sur `main`, `composer.lock` seul. Parent `3e22519`, la version en production.
Objectif du manager : aucun avis critique ou élevé de `composer audit`.

| Doyen | Verdict | Score | Fichier |
|---|---|---|---|
| le-doyen-kt | Corrections demandées ❌ (B1) | 74 | `doyens/DEPS-001/le-doyen-kt.md` |
| expert-cybersecurite-kt | Validé sous réserve ⚠️ (avis favorable avec réserves) | 92 | `doyens/DEPS-001/expert-cybersecurite-kt.md` |
| le-doyen-audit-controle-kt | Validé sous réserve ⚠️ | 86 | `doyens/DEPS-001/le-doyen-audit-controle-kt.md` |

**Verdict initial : Corrections demandées. Score : 74/100**, le plus bas des trois.

Les trois doyens jugent le lot bon :
- `composer audit` passe de 42 avis à 0 ;
- la suite Laravel passe à 207/207, relancée par l'audit ;
- l'avis critique sur le TokenGuard ne concernait pas SGT : celui-ci utilise une session web et Sanctum, et les champs de connexion sont validés en `string`.

Les réserves portent sur le script de mise en production `deps001.sh`.

## Réserves et corrections
| N° | Source | Réserve | Correction apportée |
|---|---|---|---|
| R1 (bloquant) | KT B1, audit AUD-001, cyber (b) | Sous `set -e`, le retour arrière s'arrêtait à sa première erreur | `set +e; trap - ERR` en tête de `retour()`, `exit 1` à la fin. Vérifié par une simulation : le retour va jusqu'au bout malgré une commande en échec |
| R2 | cyber (a), audit | Les manifestes `packages.php` et `services.php` n'étaient pas régénérés après la restauration de vendor | Suppression des manifestes, puis `package:discover` dans `retour()` |
| R3 | KT B1 | `rm -rf vendor` détruisait l'état en échec | Le dossier vendor en échec est déplacé dans `/var/backups/sgt/vendor-echec-*` |
| R4 | KT I1 | `migrate --force` n'était pas protégé | `migrate` est retiré. Une garde « aucune migration en attente » s'exécute **avant** la coupure. Testée en production : « No pending migrations » |
| R5 | KT I2, audit | Le worker et les notifications n'étaient pas contrôlés avant la levée du `trap` | `sgt-queue` doit être actif, puis `controle-push.php` vérifie FCM et Web Push : un jeton FCM est obtenu et la configuration VAPID est chargée, sans rien afficher de secret. Testé sur la production actuelle : OK. Un échec déclenche le retour arrière |
| R6 | KT I3, audit A2 | Le message de commit dit « aucune version majeure changée » : c'est inexact pour trois dépendances indirectes | Noté ici et dans REVIEW.md, sans réécrire `main` : `brick/math` 0.14.8 → 1.0.0, `guzzlehttp/uri-template` 1 → 2, `hamcrest` 2 → 3 (développement seulement). Les contraintes des paquets parents acceptent ces versions |
| R7 | cyber (c) | Le retour arrière ne restaure pas la base | Accepté pour ce lot, qui n'a aucune migration. Le dump de la base est fait avant la coupure |
| R8 | audit A5 | La compatibilité avec php8.3 du VPS n'a pas été mesurée par l'audit | Mesurée par la session principale avec `php8.3 composer check-platform-reqs --lock --no-dev` sur le VPS : 16/16 |

**Après corrections : Validé sous réserve ⚠️.**
Il reste une seule réserve, une vraie notification reçue sur l'APK et sur la PWA. Elle sera traitée avec la preuve de notification NOTIF-PREUVE (SGT 1986).
Ces avis sont consultatifs. Le manager a donné son GO.
