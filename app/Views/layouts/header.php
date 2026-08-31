<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Security::sanitize($pageTitle ?? (defined('SITE_NAME') ? SITE_NAME . ' — Commune de Tattaguine' : 'Sunu Tattaguine')) ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>

    <div class="senegal-flag-bar"></div>

    <div class="top-bar">
        <span>République du Sénégal — Région de Fatick — Commune de Tattaguine</span>
        <span>Contact Mairie : +221 33 XXX XX XX</span>
    </div>

    <header class="main-header">
        <div class="nav-container">
            <a href="<?= BASE_URL ?>" class="logo-section">
                <div>
                    <h1>SUNU TATTAGUINE</h1>
                    <p>Portail citoyen et d'information municipale (PATIP-JF)</p>
                </div>
            </a>
            <button class="mobile-menu-btn" onclick="toggleMobileMenu(event)" aria-label="Menu Mobile">☰</button>
            <nav>
                <ul class="nav-links" id="mainNavLinks">
                    <li><a href="<?= BASE_URL ?>" class="<?= ($currentPage ?? 'home') === 'home' ? 'active' : '' ?>">Accueil</a></li>
                    <li><a href="<?= BASE_URL ?>/actualites" class="<?= ($currentPage ?? '') === 'actualites' ? 'active' : '' ?>">Actualités</a></li>
                    <li><a href="<?= BASE_URL ?>/documents" class="<?= ($currentPage ?? '') === 'documents' ? 'active' : '' ?>">Actes & Documents</a></li>
                    <li><a href="<?= BASE_URL ?>/contact" class="<?= ($currentPage ?? '') === 'contact' ? 'active' : '' ?>">Contact Mairie</a></li>
                    
                    <?php if (!empty($_SESSION['user_id'])): ?>
                        <?php 
                            require_once APP_PATH . '/Models/User.php';
                            $headerUserModel = new User();
                            $userNotifCount = $headerUserModel->getUnreadNotificationCount((int)$_SESSION['user_id']);
                        ?>
                        <li>
                            <a href="<?= BASE_URL ?>/mon-espace" class="btn-citoyen <?= ($currentPage ?? '') === 'mon-espace' ? 'active' : '' ?>">
                                👤 <?= Security::sanitize($_SESSION['full_name']) ?>
                                <?php if ($userNotifCount > 0): ?>
                                    <span style="background-color:#E53935; color:#FFF; font-size:0.75rem; font-weight:bold; padding:2px 7px; border-radius:10px; margin-left:6px; vertical-align:middle; display:inline-block; box-shadow:0 0 6px rgba(229,57,53,0.6);" title="<?= $userNotifCount ?> nouvelle(s) réponse(s) de la Mairie">
                                        🔔 <?= $userNotifCount ?>
                                    </span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <?php if (in_array($_SESSION['role_name'] ?? '', ['super_admin', 'redacteur'])): ?>
                            <li><a href="<?= BASE_URL ?>/admin/dashboard" class="btn-admin">Espace Admin</a></li>
                        <?php endif; ?>
                        <li><a href="<?= BASE_URL ?>/logout" class="btn-logout" title="Déconnexion">Déconnexion</a></li>
                    <?php else: ?>
                        <li><a href="<?= BASE_URL ?>/login" class="btn-login <?= ($currentPage ?? '') === 'login' ? 'active' : '' ?>">Se connecter</a></li>
                        <li><a href="<?= BASE_URL ?>/register" class="btn-register <?= ($currentPage ?? '') === 'register' ? 'active' : '' ?>">S'inscrire</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <script>
        function toggleMobileMenu(event) {
            if (event) event.stopPropagation();
            const navLinks = document.getElementById('mainNavLinks');
            if (navLinks) {
                navLinks.classList.toggle('mobile-open');
            }
        }
    </script>
