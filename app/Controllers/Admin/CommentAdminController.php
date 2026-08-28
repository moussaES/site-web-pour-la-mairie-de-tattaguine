<?php
// ====================================================================
// CONTRÔLEUR COMMENTADMINCONTROLLER (MODÉRATION DES COMMENTAIRES)
// ====================================================================

namespace Admin;

use Controller;
use Security;
use Comment;

require_once ROOT_PATH . '/core/Controller.php';
require_once ROOT_PATH . '/core/Mailer.php';
require_once ROOT_PATH . '/core/FirebaseMessaging.php';
require_once APP_PATH . '/Models/Comment.php';

class CommentAdminController extends Controller {
    private function checkAuth(): void {
        if (empty($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/admin/login');
            exit;
        }

        if (!in_array($_SESSION['role_name'] ?? '', ['super_admin', 'redacteur'])) {
            header('Location: ' . BASE_URL . '/mon-espace');
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

    public function reply(int $id): void {
        $this->checkAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/comments');
            return;
        }

        if (!Security::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            $_SESSION['flash_error'] = 'Erreur de sécurité CSRF.';
            $this->redirect('/admin/comments');
            return;
        }

        $adminResponse = trim($_POST['admin_response'] ?? '');
        $commentModel  = new Comment();

        if (!empty($adminResponse)) {
            $commentModel->updateStatus($id, 'approved', $adminResponse);

            // Récupérer les infos du commentaire pour notifier par email via Resend
            $allComments = $commentModel->getAllForAdmin();
            $targetComment = null;
            foreach ($allComments as $c) {
                if ((int)$c['id'] === $id) {
                    $targetComment = $c;
                    break;
                }
            }

            if ($targetComment && !empty($targetComment['author_email'])) {
                $subject = "🏛️ Réponse de la Mairie — Sunu Tattaguine";
                $html = "<div style='font-family:sans-serif; padding:20px; color:#333;'>" .
                        "<h2 style='color:#00853F;'>Sunu Tattaguine — Mairie de Tattaguine</h2>" .
                        "<p>Bonjour <strong>" . htmlspecialchars($targetComment['author_name']) . "</strong>,</p>" .
                        "<p>La Mairie de Tattaguine vient de répondre à votre commentaire sur l'article <strong>\"" . htmlspecialchars($targetComment['post_title']) . "\"</strong> :</p>" .
                        "<div style='background:#F4F9F5; border-left:4px solid #00853F; padding:15px; border-radius:6px; margin:20px 0;'>" .
                        "<strong style='color:#00853F; font-size:0.9rem;'>Réponse Officielle :</strong>" .
                        "<p style='margin:5px 0 0 0; color:#222;'>" . nl2br(htmlspecialchars($adminResponse)) . "</p>" .
                        "</div>" .
                        "<p><a href='" . BASE_URL . "/mon-espace' style='display:inline-block; padding:10px 20px; background:#00853F; color:#FFF; text-decoration:none; border-radius:6px; font-weight:bold;'>Accéder à mon Espace Citoyen</a></p>" .
                        "<hr style='border:none; border-top:1px solid #EEE; margin-top:30px;'>" .
                        "<small style='color:#777;'>Cet e-mail automatique vous a été envoyé par le portail municipal Sunu Tattaguine.</small>" .
                        "</div>";

                \Mailer::send($targetComment['author_email'], $subject, $html);
            }

            // Envoyer une notification Push Web via Firebase Cloud Messaging (FCM)
            if ($targetComment && !empty($targetComment['user_id'])) {
                $db = \Database::getInstance();
                \FirebaseMessaging::sendPushNotification(
                    $db,
                    (int)$targetComment['user_id'],
                    "🏛️ Mairie de Tattaguine",
                    "La Mairie a répondu à votre commentaire sur : " . ($targetComment['post_title'] ?? 'Actualités'),
                    BASE_URL . "/mon-espace"
                );
            }

            $_SESSION['flash_success'] = 'Votre réponse officielle a été publiée avec succès et transmise au citoyen (E-mail & Notification FCM) !';
        } else {
            $_SESSION['flash_error'] = 'Veuillez saisir le texte de votre réponse.';
        }

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
