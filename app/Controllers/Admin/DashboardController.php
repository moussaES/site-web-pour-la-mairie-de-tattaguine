<?php
// ====================================================================
// CONTRÔLEUR DASHBOARDCONTROLLER (TABLEAU DE BORD ADMIN)
// ====================================================================

namespace Admin;

use Controller;
use Post;
use Comment;
use ContactMessage;
use Stat;
use Category;

require_once ROOT_PATH . '/core/Controller.php';
require_once APP_PATH . '/Models/Post.php';
require_once APP_PATH . '/Models/Comment.php';
require_once APP_PATH . '/Models/ContactMessage.php';
require_once APP_PATH . '/Models/Stat.php';
require_once APP_PATH . '/Models/Category.php';

class DashboardController extends Controller {
    private function checkAuth(): void {
        if (empty($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/admin/login');
            exit;
        }

        if (!in_array($_SESSION['role_name'] ?? '', ['super_admin', 'redacteur'])) {
            $_SESSION['flash_error'] = 'Accès réservé aux agents de la mairie. Redirection vers votre espace citoyen.';
            header('Location: ' . BASE_URL . '/mon-espace');
            exit;
        }

        // Vérification de l'inactivité administrateur (expiration après 30 minutes d'inactivité)
        Security::checkAdminSessionTimeout(1800);
    }

    public function index(): void {
        $this->checkAuth();

        $commentModel  = new Comment();
        $contactModel  = new ContactMessage();
        $statModel     = new Stat();
        $postModel     = new Post();
        $categoryModel = new Category();

        $pendingComments = $commentModel->countPending();
        $unreadMessages  = $contactModel->countUnread();
        $totalVisits     = $statModel->getTotalVisits();
        $latestPosts     = $postModel->getLatest(5);
        $recentComments  = $commentModel->getAllForAdmin('pending');
        $dailyVisits     = $statModel->getDailyVisits(7);
        $categories      = $categoryModel->getAll();

        $data = [
            'pageTitle'        => 'Tableau de Bord — Administration Sunu Tattaguine',
            'pendingComments'  => $pendingComments,
            'unreadMessages'   => $unreadMessages,
            'totalVisits'      => $totalVisits,
            'latestPosts'      => $latestPosts,
            'recentComments'   => $recentComments,
            'dailyVisits'      => $dailyVisits,
            'categories'       => $categories,
            'csrfToken'        => \Security::generateCsrfToken(),
            'flashSuccess'     => $_SESSION['flash_success'] ?? null,
            'flashError'       => $_SESSION['flash_error'] ?? null
        ];

        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        $this->render('admin/dashboard', $data);
    }
}
