<?php
// ====================================================================
// CONTRÔLEUR CONTACTCONTROLLER (FORMULAIRE DE CONTACT CITOYEN)
// ====================================================================

require_once ROOT_PATH . '/core/Controller.php';
require_once APP_PATH . '/Models/ContactMessage.php';
require_once APP_PATH . '/Models/Stat.php';

class ContactController extends Controller {
    public function index(): void {
        $statModel = new Stat();
        $statModel->recordVisit('/contact');

        $captcha = Security::generateCaptchaMath();

        $data = [
            'pageTitle'       => 'Contact Mairie — Commune de Tattaguine',
            'currentPage'     => 'contact',
            'captchaQuestion' => $captcha['question'],
            'csrfToken'       => Security::generateCsrfToken(),
            'flashSuccess'    => $_SESSION['flash_success'] ?? null,
            'flashError'      => $_SESSION['flash_error'] ?? null
        ];

        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        $this->render('contact/index', $data);
    }

    public function store(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/contact');
            return;
        }

        // Obligation de connexion
        if (empty($_SESSION['user_id'])) {
            $_SESSION['redirect_after_login'] = '/contact';
            $_SESSION['flash_error'] = 'Veuillez vous connecter à votre compte citoyen pour envoyer un message à la Mairie.';
            $this->redirect('/login');
            return;
        }

        if (!Security::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            $_SESSION['flash_error'] = 'Erreur de sécurité CSRF. Veuillez réessayer.';
            $this->redirect('/contact');
            return;
        }

        $userCaptcha = (int)($_POST['captcha_answer'] ?? 0);
        if (!Security::verifyCaptchaMath($userCaptcha)) {
            $_SESSION['flash_error'] = 'La réponse au test anti-bot est incorrecte.';
            $this->redirect('/contact');
            return;
        }

        $fullName = $_SESSION['full_name'] ?? 'Citoyen';
        $email    = $_SESSION['email'] ?? '';
        $phone    = trim($_POST['phone'] ?? '');
        $subject  = trim($_POST['subject'] ?? '');
        $message  = trim($_POST['message'] ?? '');

        if (empty($subject) || empty($message)) {
            $_SESSION['flash_error'] = 'Veuillez remplir l\'objet et le message.';
            $this->redirect('/contact');
            return;
        }

        $contactModel = new ContactMessage();
        $success = $contactModel->create([
            'user_id'   => (int)$_SESSION['user_id'],
            'full_name' => $fullName,
            'email'     => $email,
            'phone'     => $phone,
            'subject'   => $subject,
            'message'   => $message
        ]);

        if ($success) {
            $_SESSION['flash_success'] = 'Votre message a bien été transmis aux services de la Mairie de Tattaguine. Nous vous répondrons dans les plus brefs délais.';
        } else {
            $_SESSION['flash_error'] = 'Une erreur est survenue lors de l\'envoi de votre message.';
        }

        $this->redirect('/contact');
    }
}
