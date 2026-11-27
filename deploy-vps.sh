#!/bin/bash
# ============================================================
# deploy-vps.sh — Déploiement SGT sur VPS Hostinger
# Serveur : root@72.61.195.69
# URL cible : https://sgt.kaytechnologie.online
# ============================================================

set -e  # Arrêter si une commande échoue
umask 027

echo "=== [1/8] Mise à jour des paquets ==="
apt-get update -qq

echo "=== [2/8] Vérifier PHP 8.3 + extensions ==="
php8.3 -v || { apt-get install -y php8.3 php8.3-cli php8.3-fpm php8.3-mysql php8.3-mbstring php8.3-xml php8.3-zip php8.3-curl php8.3-bcmath php8.3-intl; }

echo "=== [3/8] Cloner le projet ==="
cd /var/www

if [ -d "erp-sgt" ]; then
    echo "Dossier existant — git pull"
    cd erp-sgt
    git pull origin main
else
    echo "Nouveau clone"
    git clone https://github.com/kaytechnologie/erp-sgt.git erp-sgt
    cd erp-sgt
fi

echo "=== [4/8] Installer les dépendances Composer ==="
composer install --no-dev --optimize-autoloader --no-interaction

echo "=== [5/8] Configurer .env production ==="
if [ ! -f .env ]; then
    cp .env.example .env
    chmod 640 .env
    php8.3 artisan key:generate
fi

# Appliquer les valeurs production
sed -i 's/APP_NAME=.*/APP_NAME="SGT KayTechnologie"/' .env
sed -i 's/APP_ENV=.*/APP_ENV=production/' .env
sed -i 's/APP_DEBUG=.*/APP_DEBUG=false/' .env
sed -i 's|APP_URL=.*|APP_URL=https://sgt.kaytechnologie.online|' .env
sed -i 's/DB_CONNECTION=.*/DB_CONNECTION=mysql/' .env
sed -i 's/DB_HOST=.*/DB_HOST=127.0.0.1/' .env
sed -i 's/DB_PORT=.*/DB_PORT=3306/' .env
sed -i 's/DB_DATABASE=.*/DB_DATABASE=erp_sgt/' .env
sed -i 's/DB_USERNAME=.*/DB_USERNAME=erp_sgt_user/' .env
# Préparer la base et son compte séparément, puis renseigner les identifiants
# dans le .env protégé. Laravel lit ce fichier au moment de la connexion.
echo "=== [6/8] Vérifier la connexion à la base MySQL ==="
php8.3 artisan config:clear
if ! php8.3 artisan db:show --database=mysql --no-interaction >/dev/null 2>&1; then
    echo "Connexion MySQL impossible. Vérifiez la base, le compte et les identifiants dans .env." >&2
    exit 1
fi

echo "=== [7/8] Migrations + storage ==="
php8.3 artisan config:clear
php8.3 artisan migrate --force
php8.3 artisan storage:link 2>/dev/null || true
php8.3 artisan config:cache
php8.3 artisan route:cache
php8.3 artisan view:cache

echo "=== [8/8] Permissions fichiers ==="
chown -R www-data:www-data /var/www/erp-sgt
chmod 755 /var/www/erp-sgt
chmod 640 /var/www/erp-sgt/.env
chmod -R 775 /var/www/erp-sgt/storage
chmod -R 775 /var/www/erp-sgt/bootstrap/cache

echo ""
echo "✅ Application déployée — prête pour la config Nginx"
echo ""
grep APP_DEBUG .env
grep APP_URL .env
