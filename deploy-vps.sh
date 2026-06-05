#!/bin/bash
# ============================================================
# deploy-vps.sh — Déploiement SGT sur VPS Hostinger
# Serveur : root@72.61.195.69
# URL cible : https://sgt.kaytechnologie.online
# ============================================================

set -e  # Arrêter si une commande échoue

echo "=== [1/8] Mise à jour des paquets ==="
apt-get update -qq

echo "=== [2/8] Vérifier PHP 8.3 + extensions ==="
php -v || { apt-get install -y php8.3 php8.3-cli php8.3-fpm php8.3-mysql php8.3-mbstring php8.3-xml php8.3-zip php8.3-curl php8.3-bcmath php8.3-intl; }

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
    php artisan key:generate
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
# DB_PASSWORD à renseigner manuellement après

echo "=== [6/8] Créer BDD MySQL ==="
mysql -u root -e "
CREATE DATABASE IF NOT EXISTS erp_sgt CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'erp_sgt_user'@'localhost' IDENTIFIED BY 'SGT_Kaytech_2026!';
GRANT ALL PRIVILEGES ON erp_sgt.* TO 'erp_sgt_user'@'localhost';
FLUSH PRIVILEGES;
" 2>/dev/null || echo "BDD déjà configurée"

# Mettre à jour le mot de passe dans .env
sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=SGT_Kaytech_2026!/" .env

echo "=== [7/8] Migrations + storage ==="
php artisan config:clear
php artisan migrate --force
php artisan storage:link 2>/dev/null || true
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "=== [8/8] Permissions fichiers ==="
chown -R www-data:www-data /var/www/erp-sgt
chmod -R 755 /var/www/erp-sgt
chmod -R 775 /var/www/erp-sgt/storage
chmod -R 775 /var/www/erp-sgt/bootstrap/cache

echo ""
echo "✅ Application déployée — prête pour la config Nginx"
echo ""
grep APP_DEBUG .env
grep APP_URL .env
