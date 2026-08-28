<?php
// ====================================================================
// CONTRÔLEUR COMMENTCONTROLLER (SOUMISSION DES COMMENTAIRES CITOYENS)
// ====================================================================

require_once ROOT_PATH . '/core/Controller.php';
require_once APP_PATH . '/Models/Post.php';
require_once APP_PATH . '/Models/Comment.php';

class CommentController extends Controller {
    public function store(string $slug): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/actualites/' . $slug);
            return;
        }

        // Obligation d'être connecté
        if (empty($_SESSION['user_id'])) {
            $_SESSION['redirect_after_login'] = '/actualites/' . $slug;
            $_SESSION['flash_error'] = 'Veuillez vous connecter à votre compte citoyen pour pouvoir laisser un commentaire.';
            $this->redirect('/login');
            return;
        }

        // Vérification CSRF
        if (!Security::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            $_SESSION['flash_error'] = 'Erreur de sécurité (Token CSRF invalide). Veuillez réessayer.';
            $this->redirect('/actualites/' . $slug);
            return;
        }

        // Vérification CAPTCHA
        $userCaptcha = (int)($_POST['captcha_answer'] ?? 0);
        if (!Security::verifyCaptchaMath($userCaptcha)) {
            $_SESSION['flash_error'] = 'La réponse au test anti-bot (CAPTCHA) est incorrecte.';
            $this->redirect('/actualites/' . $slug);
            return;
        }

        // Récupération de l'article
        $postModel = new Post();
        $post = $postModel->getBySlug($slug);
        if (!$post) {
            $this->redirect('/actualites');
            return;
        }

        $authorName  = $_SESSION['full_name'] ?? 'Citoyen';
        $authorEmail = $_SESSION['email'] ?? '';
        $content     = trim($_POST['content'] ?? '');

        if (empty($content)) {
            $_SESSION['flash_error'] = 'Veuillez rédiger votre commentaire.';
            $this->redirect('/actualites/' . $slug);
            return;
        }

        // Enregistrement avec statut 'pending' et association du user_id
        $commentModel = new Comment();
        $success = $commentModel->create([
            'post_id'      => $post['id'],
            'user_id'      => (int)$_SESSION['user_id'],
            'author_name'  => $authorName,
            'author_email' => $authorEmail,
            'content'      => $content
        ]);

        if ($success) {
            $_SESSION['flash_success'] = 'Votre commentaire a été soumis avec succès. Il apparaîtra sous l\'article dès sa modération par les agents de la mairie.';
        } else {
            $_SESSION['flash_error'] = 'Une erreur est survenue lors de l\'enregistrement de votre commentaire.';
        }

        $this->redirect('/actualites/' . $slug);
    }
}
