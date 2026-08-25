<?php
// ====================================================================
// CONTRÔLEUR COMMENTADMINCONTROLLER (MODÉRATION DES COMMENTAIRES)
// ====================================================================

namespace Admin;

use Controller;
use Security;
use Comment;

require_once ROOT_PATH . '/core/Controller.php';
require_once APP_PATH . '/Models/Comment.php';

class CommentAdminController extends Controller {
    private function checkAuth(): void {
        if (empty($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/admin/login');
            exit;
        }
    }

    public function index(): void {
        $this->checkAuth();

        $statusFilter = Security::sanitize($_GET['status'] ?? 'pending');
        $commentModel = new Comment();

        $comments = $commentModel->getAllForAdmin(!empty($statusFilter) ? $statusFilter : null);

        $data = [
            'pageTitle'    => 'Modération des Commentaires — Espace Admin',
            'comments'     => $comments,
            'statusFilter' => $statusFilter,
            'flashSuccess' => $_SESSION['flash_success'] ?? null,
            'flashError'   => $_SESSION['flash_error'] ?? null,
            'csrfToken'    => Security::generateCsrfToken()
        ];

        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        $this->render('admin/comments/index', $data);
    }

    public function approve(int $id): void {
        $this->checkAuth();
        $commentModel = new Comment();
        $commentModel->updateStatus($id, 'approved');

        $_SESSION['flash_success'] = 'Le commentaire a été approuvé et est désormais visible publiquement.';
        $this->redirect('/admin/comments');
    }

    public function reject(int $id): void {
        $this->checkAuth();
        $commentModel = new Comment();
        $commentModel->updateStatus($id, 'rejected');

        $_SESSION['flash_success'] = 'Le commentaire a été rejeté.';
        $this->redirect('/admin/comments');
    }

    public function delete(int $id): void {
        $this->checkAuth();
        $commentModel = new Comment();
        $commentModel->delete($id);

        $_SESSION['flash_success'] = 'Le commentaire a été définitivement supprimé.';
        $this->redirect('/admin/comments');
    }
}
