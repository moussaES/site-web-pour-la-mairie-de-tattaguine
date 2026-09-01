<?php
// ====================================================================
// CONFIGURATION GLOBALE DU SITE WEB COMMUNE DE TATTAGUINE
// ====================================================================

// Configuration de la base de données MySQL / MariaDB (Alwaysdata Cloud & Vercel)
define('DB_HOST', getenv('DB_HOST') ?: 'mysql-moustaphacode.alwaysdata.net');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'moustaphacode_tattaguine_db');
define('DB_USER', getenv('DB_USER') ?: 'moustaphacode');
define('DB_PASS', getenv('DB_PASS') !== false && getenv('DB_PASS') !== '' ? getenv('DB_PASS') : 'faye1167');
define('DB_CHARSET', getenv('DB_CHARSET') ?: 'utf8mb4');

// URLs et Chemins du projet avec détection dynamique de l'hôte (Vercel, Cloud & Local)
if (!empty(getenv('BASE_URL'))) {
    $baseUrl = getenv('BASE_URL');
    if (str_contains($_SERVER['HTTP_HOST'] ?? '', 'vercel.app') && !str_ends_with(rtrim($baseUrl, '/'), '/public')) {
        $baseUrl = rtrim($baseUrl, '/') . '/public';
    }
} elseif (!empty($_SERVER['HTTP_HOST'])) {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    $scheme = $isHttps ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    $scriptDir = rtrim($scriptDir, '/');
    if (!empty($scriptDir) && $scriptDir !== '.' && $scriptDir !== '/' && $scriptDir !== '/api') {
        $baseUrl = $scheme . $host . $scriptDir;
    } elseif (str_contains($host, 'localhost') || str_contains($host, '127.0.0.1')) {
        $baseUrl = $scheme . $host . '/site%20web%20mairie/public';
    } else {
        $baseUrl = $scheme . $host . '/public';
    }
} else {
    $baseUrl = 'http://localhost/site%20web%20mairie/public';
}
define('BASE_URL', rtrim($baseUrl, '/'));
define('SITE_NAME', getenv('SITE_NAME') ?: 'Sunu Tattaguine');
define('SITE_SLOGAN', getenv('SITE_SLOGAN') ?: 'Portail Citoyen & Mairie de Tattaguine (PATIP-JF)');

// Chemins réels sur le serveur
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('UPLOADS_PATH', PUBLIC_PATH . '/uploads');

// Configuration OAuth 2.0 Google Identity
define('GOOGLE_CLIENT_ID', getenv('GOOGLE_CLIENT_ID') ?: '254091229105-psvqe9pn73e1o8duu3sk9jh0f17rhunb.apps.googleusercontent.com');

// Configuration API Resend pour l'envoi d'e-mails transactionnels (https://resend.com)
define('RESEND_API_KEY', getenv('RESEND_API_KEY') ?: 'VOTRE_CLE_API_RESEND');
define('RESEND_FROM_EMAIL', getenv('RESEND_FROM_EMAIL') ?: 'Sunu Tattaguine <onboarding@resend.dev>');

// Configuration Firebase Cloud Messaging (FCM) Web Push Notifications (https://console.firebase.google.com)
define('FIREBASE_API_KEY', getenv('FIREBASE_API_KEY') ?: 'AIzaSyA1LDS0_Be7TARajRi4gHMWsZETDaaKf3A');
define('FIREBASE_PROJECT_ID', getenv('FIREBASE_PROJECT_ID') ?: 'site-web-mairie-506810');
define('FIREBASE_MESSAGING_SENDER_ID', getenv('FIREBASE_MESSAGING_SENDER_ID') ?: '254091229105');
define('FIREBASE_APP_ID', getenv('FIREBASE_APP_ID') ?: '1:254091229105:web:1b7679669e5636cacc6f90');
define('FIREBASE_VAPID_KEY', getenv('FIREBASE_VAPID_KEY') ?: 'BN0rV3QbhkD1Hnc6iqCylOYbf-Jykl0aGdh2ahxD_zUfQzl8FlL8EyWQNSSiq7_bt5yuLs7UKwwLCL6ETVg5WDg');
define('FIREBASE_SERVER_KEY', getenv('FIREBASE_SERVER_KEY') ?: 'VOTRE_SERVER_KEY_FIREBASE');

// Configuration de la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

