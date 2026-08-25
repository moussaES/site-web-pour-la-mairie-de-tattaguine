<?php
// ====================================================================
// CONTRÔLEUR AUTHCONTROLLER (AUTHENTIFICATION ET SESSIONS ADMIN)
// ====================================================================

namespace Admin;

use Controller;
use Security;
use User;

require_once ROOT_PATH . '/core/Controller.php';
require_once APP_PATH . '/Models/User.php';

class AuthController extends Controller {
    /**
     * Formulaire de connexion
     */
    public function loginForm(): void {
        if (!empty($_SESSION['user_id'])) {
            $this->redirect('/admin/dashboard');
            return;
        }

        $data = [
            'pageTitle'    => 'Connexion Espace Administrateur — Mairie de Tattaguine',
            'csrfToken'    => Security::generateCsrfToken(),
            'flashError'   => $_SESSION['flash_error'] ?? null
        ];

        unset($_SESSION['flash_error']);

        $this->render('admin/auth/login', $data);
    }

    /**
     * Traitement de la connexion avec vérification bcrypt & état actif
     */
    public function login(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/login');
            return;
        }

        if (!Security::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            $_SESSION['flash_error'] = 'Erreur de sécurité CSRF.';
            $this->redirect('/admin/login');
            return;
        }

        $identifier = Security::sanitize($_POST['identifier'] ?? '');
        $password   = $_POST['password'] ?? '';

        if (empty($identifier) || empty($password)) {
            $_SESSION['flash_error'] = 'Veuillez saisir votre identifiant et votre mot de passe.';
            $this->redirect('/admin/login');
            return;
        }

        $userModel = new User();
        $user = $userModel->findByUsernameOrEmail($identifier);

        if ($user && Security::verifyPassword($password, $user['password_hash'])) {
            // Vérifier si le compte est actif
            if (isset($user['is_active']) && (int)$user['is_active'] === 0) {
                $_SESSION['flash_error'] = 'Votre compte d\'agent municipal a été désactivé par le Super-Administrateur. Veuillez contacter la mairie.';
                $this->redirect('/admin/login');
                return;
            }

            // Régénérer l'ID de session pour prévenir la fixation de session
            session_regenerate_id(true);

            $_SESSION['user_id']     = $user['id'];
            $_SESSION['username']    = $user['username'];
            $_SESSION['full_name']   = $user['full_name'];
            $_SESSION['role_name']   = $user['role_name'];
            $_SESSION['role_label']  = $user['role_label'];

            $this->redirect('/admin/dashboard');
        } else {
            $_SESSION['flash_error'] = 'Identifiant ou mot de passe incorrect.';
            $this->redirect('/admin/login');
        }
    }

    /**
     * Déconnexion
     */
    public function logout(): void {
        unset($_SESSION['user_id'], $_SESSION['username'], $_SESSION['full_name'], $_SESSION['role_name'], $_SESSION['role_label']);
        session_destroy();
        $this->redirect('/admin/login');
    }
}
