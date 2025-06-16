<?php
// Database configuration
return [
    'host' => 'localhost',
    'dbname' => 'autoentre_db',
    'user' => 'autoentre_user',
    'pass' => 'autoentre_pass',
    'charset' => 'utf8mb4',
    'jwt_secret' => 'autoentrepreneur_tn_secret_key_2025',
    'jwt_expiry' => 86400, // 24 hours
    'upload_path' => '../uploads/',
    'max_file_size' => 5242880, // 5MB
    'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
    'site_name' => 'Auto-Entrepreneur Tunisie',
    'site_url' => 'http://localhost:8000',
    'admin_email' => 'admin@autoentrepreneur-tn.com'
];
