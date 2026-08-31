<?php
// ====================================================================
// POINT D'ENTRÉE SERVERLESS VERCEL (ROUTAGE VERS PUBLIC/INDEX.PHP)
// ====================================================================

if (!headers_sent()) {
    header('Content-Type: text/html; charset=utf-8');
}

require __DIR__ . '/../public/index.php';
