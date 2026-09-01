<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Security::sanitize($pageTitle ?? 'Espace Admin — Sunu Tattaguine') ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css">
</head>
<body class="admin-body">

    <!-- Fond Flouté Mobile (Backdrop) -->
    <div id="sidebarBackdrop" class="sidebar-backdrop" onclick="toggleMobileSidebar()"></div>

    <!-- Sidebar Admin Indépendant (Fixe sur PC, Off-canvas sur Mobile/Tablette) -->
    <aside id="adminSidebar" class="admin-sidebar">
        <div class="sidebar-header">
            <h2>SUNU TATTAGUINE</h2>
            <p>Espace d'Administration</p>
        </div>
        <ul class="sidebar-menu">
            <li>
                <a href="<?= BASE_URL ?>/admin/dashboard" class="<?= ($activeTab ?? '') === 'dashboard' ? 'active' : '' ?>">
                    <span>Tableau de Bord</span>
                </a>
            </li>
            <li>
                <a href="<?= BASE_URL ?>/admin/posts" class="<?= ($activeTab ?? '') === 'posts' ? 'active' : '' ?>">
                    <span>Actualités & Vidéos</span>
                </a>
            </li>
            <li>
                <a href="<?= BASE_URL ?>/admin/comments" class="<?= ($activeTab ?? '') === 'comments' ? 'active' : '' ?>">
                    <span>Modération Commentaires</span>
                    <?php if (!empty($pendingCommentsCount) && $pendingCommentsCount > 0): ?>
                        <span class="badge-pending" style="background:#E31B23; color:#FFF; font-weight:bold; padding:2px 8px; border-radius:10px; box-shadow:0 0 6px rgba(227,27,35,0.6);"><?= $pendingCommentsCount ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li>
                <a href="<?= BASE_URL ?>/admin/messages" class="<?= ($activeTab ?? '') === 'messages' ? 'active' : '' ?>">
                    <span>Messages Citoyens</span>
                    <?php if (!empty($unreadMessagesCount) && $unreadMessagesCount > 0): ?>
                        <span class="badge-pending" style="background:#E31B23; color:#FFF; font-weight:bold; padding:2px 8px; border-radius:10px; box-shadow:0 0 6px rgba(227,27,35,0.6);"><?= $unreadMessagesCount ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li>
                <a href="<?= BASE_URL ?>/admin/documents" class="<?= ($activeTab ?? '') === 'documents' ? 'active' : '' ?>">
                    <span>Documents PDF</span>
                </a>
            </li>
            <?php if (($_SESSION['role_name'] ?? '') === 'super_admin'): ?>
                <li>
                    <a href="<?= BASE_URL ?>/admin/users" class="<?= ($activeTab ?? '') === 'users' ? 'active' : '' ?>">
                        <span>Gestion des Agents</span>
                    </a>
                </li>
            <?php endif; ?>
            <li style="margin-top:20px; border-top: 1px solid rgba(255,255,255,0.1);">
                <a href="<?= BASE_URL ?>" target="_blank" style="color:var(--admin-accent);">
                    <span>Voir le Site Public ↗</span>
                </a>
            </li>
        </ul>
        <div class="sidebar-footer">
            Connecté : <strong><?= Security::sanitize($_SESSION['full_name'] ?? 'Agent') ?></strong><br>
            Rôle : <small><?= Security::sanitize($_SESSION['role_label'] ?? 'Agent') ?></small>
        </div>
    </aside>

    <!-- Zone de Contenu Principale Indépendante -->
    <main class="admin-main">
        <header class="admin-topbar">
            <div style="display:flex; align-items:center; gap:15px;">
                <!-- Bouton Hamburger (Visible sur Mobile/Tablette <= 992px) -->
                <button type="button" class="mobile-nav-toggle" onclick="toggleMobileSidebar()">
                    ☰ Menu
                </button>
                <strong style="color:var(--admin-sidebar-bg);">Sunu Tattaguine — Administration</strong>
            </div>
            <div class="admin-user-info">
                <a href="<?= BASE_URL ?>/admin/logout" class="btn-action btn-danger">Déconnexion</a>
            </div>
        </header>

        <div class="admin-content">

    <script>
        function toggleMobileSidebar() {
            const sidebar = document.getElementById('adminSidebar');
            const backdrop = document.getElementById('sidebarBackdrop');
            if (sidebar && backdrop) {
                sidebar.classList.toggle('sidebar-open');
                backdrop.classList.toggle('active');
            }
        }
    </script>
