<?php
// ====================================================================
// CONTRÔLEUR CONTACTADMINCONTROLLER (GESTION DES MESSAGES CITOYENS)
// ====================================================================

namespace Admin;

use Controller;
use Security;
use ContactMessage;

require_once ROOT_PATH . '/core/Controller.php';
require_once ROOT_PATH . '/core/Mailer.php';
require_once ROOT_PATH . '/core/FirebaseMessaging.php';
require_once APP_PATH . '/Models/ContactMessage.php';

class ContactAdminController extends Controller {
    private function checkAuth(): void {
        if (empty($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/admin/login');
            exit;
        }

        if (!in_array($_SESSION['role_name'] ?? '', ['super_admin', 'redacteur'])) {
            header('Location: ' . BASE_URL . '/mon-espace');
            exit;
        }

        // Vérification de l'inactivité administrateur (expiration après 30 minutes d'inactivité)
        \Security::checkAdminSessionTimeout(1800);
    }

    public function index(): void {
        $this->checkAuth();

        $contactModel = new ContactMessage();
        $messages = $contactModel->getAll();
        $unreadCount = $contactModel->countUnread();

        $data = [
            'pageTitle'    => 'Messages Citoyens & Requetes — Sunu Tattaguine',
            'messages'     => $messages,
            'unreadCount'  => $unreadCount,
            'csrfToken'    => Security::generateCsrfToken(),
            'flashSuccess' => $_SESSION['flash_success'] ?? null,
            'flashError'   => $_SESSION['flash_error'] ?? null
        ];

        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        $this->render('admin/messages/index', $data);
    }

    public function markAsRead(int $id): void {
        $this->checkAuth();
        $contactModel = new ContactMessage();
        $contactModel->markAsRead($id);

        $_SESSION['flash_success'] = 'Le message a été marqué comme lu.';
        $this->redirect('/admin/messages');
    }

    public function reply(int $id): void {
        $this->checkAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/messages');
            return;
        }

        if (!Security::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            $_SESSION['flash_error'] = 'Erreur de sécurité CSRF.';
            $this->redirect('/admin/messages');
            return;
        }

        $replyText = trim($_POST['admin_reply'] ?? '');
        $contactModel = new ContactMessage();

        if (!empty($replyText)) {
            $contactModel->saveAdminReply($id, $replyText);

            // Récupérer le message pour la notification email Resend
            $msg = $contactModel->getById($id);
            if ($msg && !empty($msg['email'])) {
                $subject = "🏛️ Réponse à votre message — Sunu Tattaguine";
                $html = "<div style='font-family:sans-serif; padding:20px; color:#333;'>" .
                        "<h2 style='color:#00853F;'>Sunu Tattaguine — Mairie de Tattaguine</h2>" .
                        "<p>Bonjour <strong>" . htmlspecialchars($msg['full_name']) . "</strong>,</p>" .
                        "<p>La Mairie de Tattaguine a répondu à votre message concernant <strong>\"" . htmlspecialchars($msg['subject']) . "\"</strong> :</p>" .
                        "<div style='background:#E8F5E9; border-left:4px solid #2E7D32; padding:15px; border-radius:6px; margin:20px 0;'>" .
                        "<strong style='color:#2E7D32; font-size:0.9rem;'>Réponse Officielle de la Mairie :</strong>" .
                        "<p style='margin:5px 0 0 0; color:#222;'>" . nl2br(htmlspecialchars($replyText)) . "</p>" .
                        "</div>" .
                        "<p><a href='" . BASE_URL . "/mon-espace' style='display:inline-block; padding:10px 20px; background:#00853F; color:#FFF; text-decoration:none; border-radius:6px; font-weight:bold;'>Consulter mon Espace Citoyen</a></p>" .
                        "<hr style='border:none; border-top:1px solid #EEE; margin-top:30px;'>" .
                        "<small style='color:#777;'>Cet e-mail automatique vous a été envoyé par le service aux citoyens de Sunu Tattaguine.</small>" .
                        "</div>";

                \Mailer::send($msg['email'], $subject, $html);
            }

            // Notification Push Web via Firebase Cloud Messaging (FCM)
            if ($msg && !empty($msg['user_id'])) {
                $db = \Database::getInstance();
                \FirebaseMessaging::sendPushNotification(
                    $db,
                    (int)$msg['user_id'],
                    "🏛️ Mairie de Tattaguine",
                    "La Mairie a répondu à votre requête : " . ($msg['subject'] ?? 'Message Citoyen'),
                    BASE_URL . "/mon-espace"
                );
            }

            $_SESSION['flash_success'] = 'Votre réponse officielle a été enregistrée et transmise au citoyen (E-mail & Notification FCM) !';
        } else {
            $_SESSION['flash_error'] = 'Veuillez saisir le contenu de la réponse.';
        }

        $this->redirect('/admin/messages');
    }

    public function delete(int $id): void {
        $this->checkAuth();
        $contactModel = new ContactMessage();
        $contactModel->delete($id);

        $_SESSION['flash_success'] = 'Le message a été supprimé avec succès.';
        $this->redirect('/admin/messages');
    }
}
