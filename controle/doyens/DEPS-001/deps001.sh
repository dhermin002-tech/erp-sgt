#!/bin/bash
# DEPS-001 — mise à jour des bibliothèques serveur SGT (aucun secret affiché)
# Usage : bash deps001.sh <SHA_cible>
set -euo pipefail
umask 077
SHA="$1"
ANCIEN=3e22519
cd /var/www/erp-sgt
TS=$(date +%Y%m%dT%H%M%S)
B=/var/backups/sgt
echo "TS=$TS"
[ "$(git rev-parse --short=7 HEAD)" = "$ANCIEN" ] || { echo "ERREUR: HEAD inattendu $(git rev-parse --short HEAD)"; exit 1; }
[ -z "$(git status --short)" ] || { git status --short; echo "ERREUR: arbre modifié"; exit 1; }
git fetch -q origin
git merge-base --is-ancestor "$SHA" origin/main || { echo "ERREUR: $SHA absent de origin/main"; exit 1; }
echo "Fichiers modifiés :"; git diff --stat "$ANCIEN" "$SHA" | tail -n 20
git rev-parse -q --verify refs/tags/avant-deps001 >/dev/null || git tag -a avant-deps001 -m "Avant DEPS-001" "$(git rev-parse HEAD)"

mkdir -p $B && chmod 700 $B
cp -a vendor "$B/vendor-avant-deps001-$TS"
DB=$(grep -E '^DB_DATABASE=' .env | cut -d= -f2- | tr -d '"')
dump="$B/avant-deps001-$TS.sql"
mysqldump --single-transaction --no-tablespaces --routines --triggers --events "$DB" > "$dump"
tail -1 "$dump" | grep -q "Dump completed"
echo "Sauvegardes : vendor + base ($(du -h "$dump" | cut -f1), $(grep -c '^CREATE TABLE' "$dump") tables)"; sha256sum "$dump"

retour() {
  set +e
  trap - ERR
  echo "!!! ÉCHEC — retour à $ANCIEN"
  cd /var/www/erp-sgt
  git switch -q --detach "$ANCIEN"
  [ -d vendor ] && mv vendor "$B/vendor-echec-deps001-$TS"
  cp -a "$B/vendor-avant-deps001-$TS" vendor
  rm -f bootstrap/cache/packages.php bootstrap/cache/services.php
  php8.3 artisan package:discover >/dev/null
  php8.3 artisan config:cache >/dev/null; php8.3 artisan route:cache >/dev/null; php8.3 artisan view:cache >/dev/null
  chown -R www-data:www-data /var/www/erp-sgt; chmod 640 .env
  systemctl reload php8.3-fpm; systemctl start sgt-queue || true
  php8.3 artisan up || true
  echo "Retour effectué. HEAD=$(git rev-parse --short HEAD) · site: $(curl -s -o /dev/null -w '%{http_code}' https://sgt.kaytechnologie.online/up) · sgt-queue: $(systemctl is-active sgt-queue)"
  exit 1
}

# Aucune migration en attente en production (ce lot n'en livre aucune) : sinon on s'arrête avant toute coupure
php8.3 artisan migrate:status --pending --no-interaction 2>&1 | grep -q "No pending migrations" \
  || { echo "ERREUR: migration(s) en attente en production — arrêt sans coupure"; exit 1; }
echo "Aucune migration en attente"
trap retour ERR

php8.3 artisan down --retry=60
systemctl stop sgt-queue
git switch -q --detach "$SHA"
export COMPOSER_ALLOW_SUPERUSER=1
php8.3 "$(command -v composer)" install --no-dev --optimize-autoloader --no-interaction 2>&1 | tail -3
php8.3 artisan migrate:status --no-interaction >/dev/null
php8.3 artisan config:cache >/dev/null
php8.3 artisan route:cache >/dev/null
php8.3 artisan view:cache >/dev/null
chown -R www-data:www-data /var/www/erp-sgt
chmod 640 .env
chmod -R 775 storage bootstrap/cache
systemctl reload php8.3-fpm
systemctl start sgt-queue
php8.3 artisan up
sleep 3
for u in /up /login /app/; do
  c=$(curl -s -o /dev/null -w '%{http_code}' "https://sgt.kaytechnologie.online$u")
  echo "$u $c"; [ "$c" = 200 ]
done
c=$(curl -s -o /dev/null -w '%{http_code}' https://sgt.kaytechnologie.online/api/v1/me); echo "/api/v1/me sans jeton $c"; [ "$c" = 401 ]
c=$(curl -s -o /dev/null -w '%{http_code}' https://sgt.kaytechnologie.online/api/v1/sites); echo "/api/v1/sites sans jeton $c"; [ "$c" = 401 ]
sleep 5
[ "$(systemctl is-active sgt-queue)" = active ]
sudo -u www-data HOME=/tmp php8.3 /tmp/controle-push.php
trap - ERR
echo "Laravel : $(php8.3 artisan --version)"
echo "sgt-queue: $(systemctl is-active sgt-queue) · php8.3-fpm: $(systemctl is-active php8.3-fpm)"
echo "HEAD=$(git rev-parse --short HEAD)"
echo "DEPS001 OK"
