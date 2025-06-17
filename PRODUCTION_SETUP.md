# 🚀 TunisiaFreelance - Guide de Mise en Production

## 📋 Configuration Requise

### Serveur Web
- **PHP 8.0+** avec extensions MySQL/PDO
- **MySQL 8.0+** ou MariaDB 10.4+
- **Apache 2.4+** ou **Nginx 1.18+**
- **SSL/HTTPS** (obligatoire pour la production)

### Domaine et Hébergement
- Nom de domaine (ex: tunisiafreelance.tn)
- Certificat SSL (Let's Encrypt recommandé)
- Hébergement avec au moins 2GB RAM

## 🔧 Installation Production

### 1. Configuration Base de Données

```sql
-- Créer la base de données
CREATE DATABASE tunisiafreelance_prod DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Créer un utilisateur dédié
CREATE USER 'tf_user'@'localhost' IDENTIFIED BY 'MOT_DE_PASSE_SECURISE';
GRANT ALL PRIVILEGES ON tunisiafreelance_prod.* TO 'tf_user'@'localhost';
FLUSH PRIVILEGES;

-- Importer le schéma
mysql -u tf_user -p tunisiafreelance_prod < backend/database.sql
```

### 2. Configuration Backend

Modifier `backend/config/config.php`:

```php
<?php
return [
    'host' => 'localhost',
    'dbname' => 'tunisiafreelance_prod',
    'user' => 'tf_user',
    'pass' => 'MOT_DE_PASSE_SECURISE',
    'charset' => 'utf8mb4',
    'jwt_secret' => 'CLE_JWT_SECURISEE_64_CARACTERES_MINIMUM_PRODUCTION',
];
```

### 3. Configuration Apache (.htaccess)

Créer `backend/.htaccess`:
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php?route=$1 [QSA,L]

# Sécurité
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
```

Créer `frontend/.htaccess`:
```apache
# Redirection vers HTTPS
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Cache des assets
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 year"
    ExpiresByType application/javascript "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
</IfModule>
```

### 4. Configuration Nginx (Alternative)

```nginx
server {
    listen 80;
    server_name tunisiafreelance.tn www.tunisiafreelance.tn;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name tunisiafreelance.tn www.tunisiafreelance.tn;
    
    root /var/www/tunisiafreelance;
    index index.html;
    
    ssl_certificate /path/to/certificate.crt;
    ssl_certificate_key /path/to/private.key;
    
    location / {
        try_files $uri $uri/ /frontend/index.html;
    }
    
    location /backend/ {
        try_files $uri $uri/ /backend/index.php?$query_string;
        
        location ~ \.php$ {
            fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
            fastcgi_index index.php;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            include fastcgi_params;
        }
    }
    
    location /frontend/ {
        try_files $uri $uri/ =404;
    }
}
```

## 🔒 Sécurité Production

### 1. Variables d'Environnement
Créer `.env` (ne pas commiter):
```env
DB_HOST=localhost
DB_NAME=tunisiafreelance_prod
DB_USER=tf_user
DB_PASS=MOT_DE_PASSE_SECURISE
JWT_SECRET=CLE_JWT_SECURISEE_64_CARACTERES_MINIMUM
```

### 2. Permissions Fichiers
```bash
# Propriétaire des fichiers
chown -R www-data:www-data /var/www/tunisiafreelance

# Permissions sécurisées
find /var/www/tunisiafreelance -type d -exec chmod 755 {} \;
find /var/www/tunisiafreelance -type f -exec chmod 644 {} \;

# Fichiers de configuration en lecture seule
chmod 600 backend/config/config.php
chmod 600 .env
```

### 3. Sauvegarde Automatique
```bash
#!/bin/bash
# Script de sauvegarde quotidienne
DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u tf_user -p tunisiafreelance_prod > /backups/db_$DATE.sql
tar -czf /backups/files_$DATE.tar.gz /var/www/tunisiafreelance
```

## 📊 Monitoring et Logs

### 1. Logs Apache/Nginx
- Activer les logs d'accès et d'erreur
- Rotation automatique des logs
- Monitoring des erreurs 500/404

### 2. Monitoring Base de Données
- Surveillance des performances MySQL
- Backup automatique quotidien
- Monitoring de l'espace disque

### 3. Monitoring Application
- Logs PHP des erreurs
- Monitoring des temps de réponse API
- Alertes en cas de problème

## 🚀 Optimisations Performance

### 1. Cache
- Activer le cache navigateur (expires headers)
- Compression gzip/brotli
- Cache des requêtes MySQL

### 2. CDN (Optionnel)
- Cloudflare pour la distribution globale
- Cache des assets statiques
- Protection DDoS

### 3. Optimisation Base de Données
```sql
-- Index pour les performances
CREATE INDEX idx_services_category ON services(category);
CREATE INDEX idx_services_user_id ON services(user_id);
CREATE INDEX idx_reviews_service_id ON reviews(service_id);
CREATE INDEX idx_users_type ON users(type);
```

## 📧 Configuration Email (Optionnel)

Pour les notifications:
```php
// Configuration SMTP
$mail_config = [
    'host' => 'smtp.gmail.com',
    'port' => 587,
    'username' => 'noreply@tunisiafreelance.tn',
    'password' => 'MOT_DE_PASSE_APP',
    'encryption' => 'tls'
];
```

## ✅ Checklist Mise en Production

- [ ] Base de données configurée et sécurisée
- [ ] Fichiers uploadés sur le serveur
- [ ] Permissions fichiers correctes
- [ ] SSL/HTTPS activé
- [ ] Configuration backend mise à jour
- [ ] Tests de fonctionnement complets
- [ ] Sauvegarde automatique configurée
- [ ] Monitoring activé
- [ ] DNS configuré
- [ ] Certificat SSL valide

## 🎯 Post-Déploiement

1. **Tests complets** de toutes les fonctionnalités
2. **Monitoring** des performances les premiers jours
3. **Sauvegarde** immédiate après déploiement
4. **Documentation** des procédures d'urgence
5. **Formation** des administrateurs

---

**🎉 TunisiaFreelance est maintenant prêt pour la production !**
