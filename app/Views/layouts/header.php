<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Security::sanitize($pageTitle ?? 'Commune de Tattaguine') ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>

    <div class="senegal-flag-bar"></div>

    <div class="top-bar">
        <span>République du Sénégal — Région de Fatick — Département de Fatick</span>
        <span>Contact Mairie : +221 33 XXX XX XX</span>
    </div>

    <header class="main-header">
        <div class="nav-container">
            <a href="<?= BASE_URL ?>" class="logo-section">
                <div>
                    <h1>COMMUNE DE TATTAGUINE</h1>
                    <p>Portail d'information et d'interaction citoyenne (PATIP-JF)</p>
                </div>
            </a>
            <nav>
                <ul class="nav-links">
                    <li><a href="<?= BASE_URL ?>" class="<?= empty($currentPage) || $currentPage === 'home' ? 'active' : '' ?>">Accueil</a></li>
                    <li><a href="<?= BASE_URL ?>/actualites" class="<?= ($currentPage ?? '') === 'actualites' ? 'active' : '' ?>">Actualités</a></li>
                    <li><a href="<?= BASE_URL ?>/documents" class="<?= ($currentPage ?? '') === 'documents' ? 'active' : '' ?>">Actes & Documents</a></li>
                    <li><a href="<?= BASE_URL ?>/contact" class="<?= ($currentPage ?? '') === 'contact' ? 'active' : '' ?>">Contact Mairie</a></li>
                    <li><a href="<?= BASE_URL ?>/admin/login" class="btn-admin">Espace Admin</a></li>
                </ul>
            </nav>
        </div>
    </header>
