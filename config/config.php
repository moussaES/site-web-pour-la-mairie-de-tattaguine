<?php
// ====================================================================
// CONFIGURATION GLOBALE DU SITE WEB COMMUNE DE TATTAGUINE
// ====================================================================

// Configuration de la base de données MySQL / MariaDB
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'tattaguine_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// URLs et Chemins du projet
define('BASE_URL', 'http://localhost/site%20web%20mairie/public');
define('SITE_NAME', 'Commune de Tattaguine');
define('SITE_SLOGAN', 'République du Sénégal — Région de Fatick');

// Chemins réels sur le serveur
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('UPLOADS_PATH', PUBLIC_PATH . '/uploads');

// Configuration de la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
