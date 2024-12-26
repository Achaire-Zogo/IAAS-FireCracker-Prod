#!/bin/bash

# Couleurs pour les messages
ORANGE='\033[0;33m'
GREEN='\033[0;32m'
NC='\033[0m' # No Color

# Fonction pour afficher les messages de début et de fin
start_step() {
    echo -e "${ORANGE} $1...${NC}"
}

end_step() {
    echo -e "${GREEN} $1 terminé!${NC}"
    echo
}

# Vérification des privilèges root
if [[ $EUID -ne 0 ]]; then
   echo "Ce script doit être exécuté en tant que root (sudo)" 
   exit 1
fi

# Mise à jour du système
start_step "Mise à jour du système"
apt update && apt upgrade -y
end_step "Mise à jour du système"

# Installation des dépendances système
start_step "Installation des dépendances système"
apt install -y software-properties-common curl wget git unzip
end_step "Installation des dépendances système"

# Installation d'Apache
start_step "Installation d'Apache"
apt install -y apache2
a2enmod rewrite
systemctl start apache2
systemctl enable apache2
end_step "Installation d'Apache"

# Installation de PHP 8.2
start_step "Installation de PHP 8.2 et ses extensions"
add-apt-repository -y ppa:ondrej/php
apt update
apt install -y php8.2 php8.2-common php8.2-mysql php8.2-xml php8.2-curl php8.2-gd \
    php8.2-mbstring php8.2-zip php8.2-fpm php8.2-cli
end_step "Installation de PHP 8.2"

# Installation de MySQL
start_step "Installation de MySQL"
apt install -y mysql-server
systemctl start mysql
systemctl enable mysql

# Configuration de la sécurité MySQL
mysql_secure_installation << EOF

n
y
y
y
y
y
EOF
end_step "Installation de MySQL"

# Installation de phpMyAdmin
start_step "Installation de phpMyAdmin"
DEBIAN_FRONTEND=noninteractive apt install -y phpmyadmin php8.2-mbstring php8.2-zip php8.2-gd php8.2-json php8.2-curl

# Configuration de phpMyAdmin pour Apache
ln -s /usr/share/phpmyadmin /var/www/html/phpmyadmin

# Création de l'utilisateur phpMyAdmin
mysql -e "CREATE USER IF NOT EXISTS 'phpmyadmin'@'localhost' IDENTIFIED BY 'phpmyadmin_password';"
mysql -e "GRANT ALL PRIVILEGES ON *.* TO 'phpmyadmin'@'localhost' WITH GRANT OPTION;"
mysql -e "FLUSH PRIVILEGES;"
end_step "Installation de phpMyAdmin"

# Installation de Composer
start_step "Installation de Composer"
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer
end_step "Installation de Composer"

# Installation des dépendances Python
start_step "Installation des dépendances Python"
apt install -y python3 python3-pip python3-venv qemu-kvm
end_step "Installation des dépendances Python"

# Configuration du projet
start_step "Clonage du projet"
cd /var/www
git clone https://github.com/Achaire-Zogo/IAAS-FireCracker-Prod.git
chown -R www-data:www-data IAAS-FireCracker-Prod
cd IAAS-FireCracker-Prod
end_step "Clonage du projet"

# Configuration de Laravel
start_step "Configuration de Laravel"
cd laravel-app
composer install
cp .env.example .env
php artisan key:generate
end_step "Configuration de Laravel"

# Configuration de la base de données
start_step "Configuration de la base de données"
mysql -e "CREATE DATABASE IF NOT EXISTS iaas_firecracker;"
mysql -e "CREATE USER IF NOT EXISTS 'iaas_user'@'localhost' IDENTIFIED BY 'iaas_password';"
mysql -e "GRANT ALL PRIVILEGES ON iaas_firecracker.* TO 'iaas_user'@'localhost';"
mysql -e "FLUSH PRIVILEGES;"

# Mise à jour du fichier .env
sed -i 's/DB_DATABASE=.*/DB_DATABASE=iaas_firecracker/' .env
sed -i 's/DB_USERNAME=.*/DB_USERNAME=iaas_user/' .env
sed -i 's/DB_PASSWORD=.*/DB_PASSWORD=iaas_password/' .env
end_step "Configuration de la base de données"

# Migration de la base de données
start_step "Migration de la base de données"
php artisan migrate --force
php artisan db:seed --force
end_step "Migration de la base de données"

# Configuration d'Apache
start_step "Configuration d'Apache"
cat > /etc/apache2/sites-available/iaas-firecracker.conf << 'EOL'
<VirtualHost *:80>
    ServerName iaas-firecracker.local
    DocumentRoot /var/www/IAAS-FireCracker-Prod/laravel-app/public
    
    <Directory /var/www/IAAS-FireCracker-Prod/laravel-app/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/iaas-error.log
    CustomLog ${APACHE_LOG_DIR}/iaas-access.log combined
</VirtualHost>
EOL

a2ensite iaas-firecracker.conf
a2dissite 000-default.conf
systemctl restart apache2
end_step "Configuration d'Apache"

# Configuration de l'API Python
start_step "Configuration de l'API Python"
cd ../python-api
python3 -m venv venv
source venv/bin/activate
pip install -r requirements.txt

# Installation de Firecracker
chmod +x install_firecracker.sh
./install_firecracker.sh
chmod +x setup_firecracker.sh
./setup_firecracker.sh

# Configuration des permissions
cp firecracker-sudoers /etc/sudoers.d/firecracker
chmod 440 /etc/sudoers.d/firecracker

# Création des dossiers nécessaires
mkdir -p /var/lib/firecracker/images
mkdir -p /var/lib/firecracker/kernels
mkdir -p /var/lib/firecracker/sockets
chown -R www-data:www-data /var/lib/firecracker
end_step "Configuration de l'API Python"

# Configuration des permissions finales
start_step "Configuration des permissions finales"
chown -R www-data:www-data /var/www/IAAS-FireCracker-Prod
chmod -R 777 /var/www/IAAS-FireCracker-Prod
chmod -R 777 /var/www/IAAS-FireCracker-Prod/laravel-app/storage
chmod -R 777 /var/www/IAAS-FireCracker-Prod/laravel-app/bootstrap/cache
end_step "Configuration des permissions finales"

echo -e "${GREEN} Installation terminée avec succès!${NC}"
echo -e "${GREEN} Identifiants par défaut:${NC}"
echo -e "   Email: admin@example.com"
echo -e "   Mot de passe: password"
echo -e "${GREEN} Accédez à l'application via:${NC}"
echo -e "   http://votre-domaine"
echo -e "${GREEN} Accédez à phpMyAdmin via:${NC}"
echo -e "   http://votre-domaine/phpmyadmin"
echo -e "   Utilisateur: phpmyadmin"
echo -e "   Mot de passe: phpmyadmin_password"
echo
echo -e "${ORANGE} N'oubliez pas de:${NC}"
echo -e "1. Configurer votre nom de domaine dans /etc/hosts"
echo -e "2. Démarrer l'API Python avec: sudo cd /var/www/IAAS-FireCracker-Prod/python-api && source venv/bin/activate && sudo python3 main.py"
echo -e "3. Changer les identifiants par défaut après la première connexion"
echo -e "4. Changer le mot de passe de phpMyAdmin pour plus de sécurité"
