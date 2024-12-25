# IAAS-FireCracker : Plateforme de Gestion de Machines Virtuelles

## 📋 Description du Projet

IAAS-FireCracker est une plateforme complète de gestion de machines virtuelles basée sur la technologie Firecracker de AWS. Cette solution combine une interface web élégante développée avec Laravel et une API Python robuste pour offrir une expérience IaaS (Infrastructure as a Service) complète.

![Dashboard Admin](docs/images/admin/dashboard.png)

### 🎯 Fonctionnalités Principales

- **Gestion des Machines Virtuelles**
  - Création et déploiement rapide de VMs
  - Surveillance en temps réel de l'état des VMs
  - Gestion du cycle de vie (démarrage, arrêt, suppression)
  - Configuration personnalisée des ressources

- **Interface Administrateur**
  - Tableau de bord complet
  - Gestion des utilisateurs
  - Gestion des offres VM
  - Suivi des images système
  - Historique des opérations

- **Espace Utilisateur**
  - Interface intuitive de gestion des VMs
  - Gestion des clés SSH
  - Suivi des ressources utilisées
  - Configuration personnalisée

## 🔧 Prérequis

### Système
- Ubuntu 20.04 LTS ou version supérieure
- Minimum 4GB RAM
- 20GB d'espace disque
- Processeur compatible avec la virtualisation

### Logiciels Requis
1. **Apache2**
   ```bash
   sudo apt update
   sudo apt install apache2
   ```

2. **PHP 8.2**
   ```bash
   sudo apt install software-properties-common
   sudo add-apt-repository ppa:ondrej/php
   sudo apt update
   sudo apt install php8.2 php8.2-common php8.2-mysql php8.2-xml php8.2-curl php8.2-gd php8.2-mbstring php8.2-zip php8.2-fpm
   ```

3. **MySQL**
   ```bash
   sudo apt install mysql-server
   sudo mysql_secure_installation
   ```

4. **Composer**
   ```bash
   curl -sS https://getcomposer.org/installer | php
   sudo mv composer.phar /usr/local/bin/composer
   ```

5. **Python 3.8+**
   ```bash
   sudo apt install python3 python3-pip
   ```

## 🚀 Installation

### 1. Configuration de l'Application Laravel

1. **Cloner le projet**
   ```bash
   git clone https://github.com/Achaire-Zogo/IAAS-FireCracker.git
   cd IAAS-FireCracker
   ```

2. **Configuration de Laravel**
   ```bash
   cd laravel-app
   composer install
   cp .env.example .env
   php artisan key:generate
   ```

3. **Configuration de la base de données**
   ```bash
   # Dans le fichier .env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=iaas_firecracker
   DB_USERNAME=votre_utilisateur
   DB_PASSWORD=votre_mot_de_passe
   ```

4. **Création de la base de données**
   ```bash
   mysql -u root -p
   CREATE DATABASE iaas_firecracker;
   exit;
   ```

5. **Migration et seeding**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

6. **Configuration d'Apache**
   ```bash
   sudo nano /etc/apache2/sites-available/iaas-firecracker.conf
   ```
   Ajouter :
   ```apache
   <VirtualHost *:80>
       ServerName iaas-firecracker.local
       DocumentRoot /var/www/IAAS-FireCracker/laravel-app/public
       
       <Directory /var/www/IAAS-FireCracker/laravel-app/public>
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```

7. **Activation du site**
   ```bash
   sudo a2ensite iaas-firecracker.conf
   sudo a2enmod rewrite
   sudo systemctl restart apache2
   ```

### 2. Configuration de l'API Python

1. **Installation des dépendances système**
   ```bash
   sudo apt install -y qemu-kvm
   sudo apt install -y python3-venv
   ```

2. **Configuration de l'environnement Python**
   ```bash
   cd python-api
   python3 -m venv venv
   source venv/bin/activate
   pip install -r requirements.txt
   ```

3. **Installation de Firecracker**
   ```bash
   sudo ./install_firecracker.sh
   sudo ./setup_firecracker.sh
   ```

4. **Configuration des permissions**
   ```bash
   sudo cp firecracker-sudoers /etc/sudoers.d/firecracker
   sudo chmod 440 /etc/sudoers.d/firecracker
   ```

5. **Création des dossiers nécessaires**
   ```bash
   sudo mkdir -p /var/lib/firecracker/images
   sudo mkdir -p /var/lib/firecracker/kernels
   sudo mkdir -p /var/lib/firecracker/sockets
   sudo chown -R www-data:www-data /var/lib/firecracker
   ```

## 🎮 Utilisation

### Démarrage des Services

1. **API Python**
   ```bash
   cd python-api
   source venv/bin/activate
   sudo python3 main.py
   ```

2. **Application Laravel**
   L'application est déjà accessible via Apache à l'adresse configurée.

### Accès à l'Application

1. **Interface Administrateur**
   - URL : `http://votre-domaine/login`
   - Identifiants par défaut :
     - Email : admin@example.com
     - Mot de passe : password

2. **Interface Utilisateur**
   - URL : `http://votre-domaine`
   - Créez un compte utilisateur via l'interface d'inscription

## 📸 Captures d'écran

### Interface Administrateur
![Admin Dashboard](docs/images/admin/dashboard.png)
![Gestion des VMs](docs/images/admin/vm-management.png)

### Interface Utilisateur
![User Dashboard](docs/images/users/dashboard.png)
![VM Creation](docs/images/users/vm-creation.png)

## 🔒 Sécurité

- Tous les mots de passe sont hashés
- Protection CSRF activée
- Validation des entrées utilisateur
- Gestion des permissions par rôle
- Journalisation des actions importantes

## 🤝 Contribution

Les contributions sont les bienvenues ! N'hésitez pas à :
1. Fork le projet
2. Créer une branche pour votre fonctionnalité
3. Commit vos changements
4. Push sur la branche
5. Ouvrir une Pull Request

## 📝 License

Ce projet est sous licence MIT - voir le fichier [LICENSE](LICENSE) pour plus de détails.

## 🆘 Support

Pour toute question ou problème :
1. Consultez la documentation
2. Ouvrez une issue sur GitHub
3. Contactez l'équipe de support
