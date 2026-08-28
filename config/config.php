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
define('SITE_NAME', 'Sunu Tattaguine');
define('SITE_SLOGAN', 'Portail Citoyen & Mairie de Tattaguine (PATIP-JF)');

// Chemins réels sur le serveur
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('UPLOADS_PATH', PUBLIC_PATH . '/uploads');

// Configuration OAuth 2.0 Google Identity (Remplacez par votre Client ID issu de https://console.cloud.google.com/apis/credentials)
define('GOOGLE_CLIENT_ID', '254091229105-psvqe9pn73e1o8duu3sk9jh0f17rhunb.apps.googleusercontent.com');

// Configuration API Resend pour l'envoi d'e-mails transactionnels (https://resend.com)
define('RESEND_API_KEY', 'VOTRE_CLE_API_RESEND'); // Remplacez par votre clé API Resend (ex: re_xxx)
define('RESEND_FROM_EMAIL', 'Sunu Tattaguine <onboarding@resend.dev>');

// Configuration Firebase Cloud Messaging (FCM) Web Push Notifications (https://console.firebase.google.com)
define('FIREBASE_API_KEY', 'AIzaSyA1LDS0_Be7TARajRi4gHMWsZETDaaKf3A');
define('FIREBASE_PROJECT_ID', 'site-web-mairie-506810');
define('FIREBASE_MESSAGING_SENDER_ID', '254091229105');
define('FIREBASE_APP_ID', '1:254091229105:web:1b7679669e5636cacc6f90');
define('FIREBASE_VAPID_KEY', 'BN0rV3QbhkD1Hnc6iqCylOYbf-Jykl0aGdh2ahxD_zUfQzl8FlL8EyWQNSSiq7_bt5yuLs7UKwwLCL6ETVg5WDg'); // Paire de clés VAPID Web Push
define('FIREBASE_SERVER_KEY', 'VOTRE_SERVER_KEY_FIREBASE'); // Clé Serveur Cloud Messaging Legacy/HTTP (ou Service Account)

// Configuration de la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

