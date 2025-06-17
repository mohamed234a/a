# 🚀 Guide de Déploiement - Auto-Entrepreneur Tunisie

## 📋 **Informations du Projet**

- **Repository GitHub**: https://github.com/mohamed234a/a
- **Nom du projet**: Auto-Entrepreneur Tunisie
- **Développeur**: mohamed234a (stage@ironbyte.tn)

## 🛠️ **Déploiement Local**

### **1. Cloner le repository**
```bash
git clone https://github.com/mohamed234a/a.git
cd a
```

### **2. Configuration de la base de données**
```bash
# Créer la base de données
mysql -u root -p -e "CREATE DATABASE autoentre_db;"

# Importer le schéma
mysql -u root -p autoentre_db < backend/database/schema.sql

# Importer les données de test (optionnel)
mysql -u root -p autoentre_db < backend/database.sql
```

### **3. Configuration PHP**
```bash
# Copier le fichier de configuration
cp backend/config/database.example.php backend/config/database.php

# Modifier les paramètres de connexion dans database.php
```

### **4. Démarrer le serveur**
```bash
# Serveur de développement PHP
php -S localhost:8000

# Ou avec Apache/Nginx, pointer vers le dossier racine
```

## 🌐 **Accès à la Plateforme**

### **Site Web**
- **URL**: http://localhost:8000/frontend/home.html
- **Pages principales**:
  - Accueil: `/frontend/home.html`
  - Services: `/frontend/services.html`
  - Auto-entrepreneurs: `/frontend/autoentrepreneurs.html`
  - Inscription: `/frontend/register.html`
  - Connexion: `/frontend/login.html`

### **Administration**
- **URL**: http://localhost:8000/frontend/admin_login.html
- **Identifiants**:
  - Email: `admin@autoentrepreneur.tn`
  - Mot de passe: `admin123`

## 🔧 **Configuration de Production**

### **1. Serveur Web**
```apache
# Apache .htaccess (dans le dossier racine)
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^api/(.*)$ backend/index.php?route=api/$1 [QSA,L]
```

### **2. Base de données**
```php
// backend/config/database.php
define('DB_HOST', 'your_production_host');
define('DB_NAME', 'your_production_db');
define('DB_USER', 'your_production_user');
define('DB_PASS', 'your_production_password');
```

### **3. Sécurité**
- Changer les mots de passe par défaut
- Configurer HTTPS
- Activer les logs d'erreur
- Configurer les sauvegardes automatiques

## 📊 **Structure des Fichiers**

```
a/
├── frontend/                 # Interface utilisateur
│   ├── assets/              # CSS, images, fonts
│   │   ├── css/            # Feuilles de style
│   │   ├── images/         # Images et logos
│   │   └── fonts/          # Polices personnalisées
│   ├── js/                 # JavaScript
│   ├── *.html             # Pages HTML
├── backend/                # API et logique métier
│   ├── config/            # Configuration
│   ├── models/            # Modèles de données
│   ├── routes/            # Routes API
│   ├── middleware/        # Middleware d'authentification
│   └── utils/             # Utilitaires
├── database/              # Scripts SQL
│   ├── schema.sql         # Structure de la base
│   └── sample_data.sql    # Données de test
├── deploy.sh             # Script de déploiement
└── README.md             # Documentation
```

## 🎯 **Fonctionnalités Principales**

### **Pour les Auto-entrepreneurs**
- ✅ Inscription et profil professionnel
- ✅ Gestion des services
- ✅ Options premium (vedette, urgent, extra)
- ✅ Dashboard avec statistiques
- ✅ 4 templates de CV personnalisables

### **Pour les Clients**
- ✅ Recherche et filtrage des services
- ✅ Contact direct (email/téléphone)
- ✅ Consultation des profils détaillés
- ✅ Interface responsive

### **Pour les Administrateurs**
- ✅ Dashboard complet
- ✅ Gestion des utilisateurs (modification/suppression)
- ✅ Gestion des services
- ✅ Statistiques de la plateforme

## 💰 **Modèle de Revenus**

### **Options Premium**
- 🔥 **Service en vedette**: 50 DT/30 jours
- ⚡ **Badge urgent**: 25 DT/15 jours  
- ➕ **Service supplémentaire**: 30 DT (permanent)

### **Pas de Transaction**
- Contact direct entre utilisateurs
- Pas de système de paiement intégré
- Revenus uniquement via les options premium

## 🔐 **Comptes de Test**

### **Admin**
- Email: `admin@autoentrepreneur.tn`
- Mot de passe: `admin123`

### **Auto-entrepreneur de test**
- Email: `test@autoentrepreneur.tn`
- Mot de passe: `test123`

### **Client de test**
- Email: `client@test.tn`
- Mot de passe: `client123`

## 📞 **Support**

Pour toute question ou problème :
- **Email**: stage@ironbyte.tn
- **GitHub**: https://github.com/mohamed234a/a
- **Issues**: https://github.com/mohamed234a/a/issues

## 🎉 **Félicitations!**

Votre plateforme Auto-Entrepreneur Tunisie est maintenant déployée et prête à l'emploi! 🇹🇳✨

---

**Développé avec ❤️ pour les auto-entrepreneurs tunisiens**
