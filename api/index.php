<?php
// ====================================================================
// POINT D'ENTRÉE SERVERLESS VERCEL (ROUTAGE VERS PUBLIC/INDEX.PHP)
// ====================================================================

// Activer l'affichage des erreurs pour éviter toute page blanche sur Vercel
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!headers_sent()) {
    header('Content-Type: text/html; charset=utf-8');
}

require __DIR__ . '/../public/index.php';
