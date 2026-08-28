<?php
// ====================================================================
// CONTRÔLEUR CLIENTAUTHCONTROLLER (AUTHENTIFICATION GOOGLE & ESPACE CITOYEN)
// ====================================================================

require_once ROOT_PATH . '/core/Controller.php';
require_once APP_PATH . '/Models/User.php';

class ClientAuthController extends Controller {

    /**
     * Formulaire de connexion citoyenne
     */
    public function loginForm(): void {
        if (!empty($_SESSION['user_id'])) {
            $this->redirect('/mon-espace');
            return;
        }

        if (!empty($_GET['redirect'])) {
            $_SESSION['redirect_after_login'] = $_GET['redirect'];
        }

        $data = [
            'currentPage'    => 'login',
            'pageTitle'      => 'Connexion — Sunu Tattaguine',
            'csrfToken'      => Security::generateCsrfToken(),
            'googleClientId' => defined('GOOGLE_CLIENT_ID') ? GOOGLE_CLIENT_ID : '',
            'flashSuccess'   => $_SESSION['flash_success'] ?? null,
            'flashError'     => $_SESSION['flash_error'] ?? null
        ];

        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        $this->render('auth/login', $data);
    }

    /**
     * Traitement de la connexion classique par Email / Mot de passe
     */
    public function login(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/login');
            return;
        }

        if (!Security::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            $_SESSION['flash_error'] = 'Erreur de sécurité CSRF. Veuillez réessayer.';
            $this->redirect('/login');
            return;
        }

        $identifier = trim($_POST['identifier'] ?? '');
        $password   = $_POST['password'] ?? '';

        if (empty($identifier) || empty($password)) {
            $_SESSION['flash_error'] = 'Veuillez saisir votre e-mail (ou identifiant) et votre mot de passe.';
            $this->redirect('/login');
            return;
        }

        $userModel = new User();
        $user = $userModel->findByUsernameOrEmail($identifier);

        if ($user && !empty($user['password_hash']) && Security::verifyPassword($password, $user['password_hash'])) {
            if (isset($user['is_active']) && (int)$user['is_active'] === 0) {
                $_SESSION['flash_error'] = 'Votre compte a été suspendu par l\'administration.';
                $this->redirect('/login');
                return;
            }

            session_regenerate_id(true);
            $_SESSION['user_id']    = $user['id'];
            $_SESSION['username']   = $user['username'];
            $_SESSION['full_name']  = $user['full_name'];
            $_SESSION['email']      = $user['email'];
            $_SESSION['role_name']  = $user['role_name'];
            $_SESSION['role_label'] = $user['role_label'];

            $_SESSION['flash_success'] = 'Bienvenue ' . htmlspecialchars($user['full_name']) . ' ! Vous êtes connecté.';
            
            $redirectUrl = $_SESSION['redirect_after_login'] ?? '/mon-espace';
            unset($_SESSION['redirect_after_login']);
            
            $this->redirect($redirectUrl);
        } else {
            $_SESSION['flash_error'] = 'Identifiant ou mot de passe incorrect.';
            $this->redirect('/login');
        }
    }

    /**
     * Formulaire d'inscription citoyenne (Rôle Citoyen par défaut)
     */
    public function registerForm(): void {
        if (!empty($_SESSION['user_id'])) {
            $this->redirect('/mon-espace');
            return;
        }

        if (!empty($_GET['redirect'])) {
            $_SESSION['redirect_after_login'] = $_GET['redirect'];
        }

        $data = [
            'currentPage'    => 'register',
            'pageTitle'      => 'Inscription — Sunu Tattaguine',
            'csrfToken'      => Security::generateCsrfToken(),
            'googleClientId' => defined('GOOGLE_CLIENT_ID') ? GOOGLE_CLIENT_ID : '',
            'flashError'     => $_SESSION['flash_error'] ?? null
        ];

        unset($_SESSION['flash_error']);

        $this->render('auth/register', $data);
    }

    /**
     * Traitement de l'inscription d'un nouveau citoyen
     */
    public function register(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/register');
            return;
        }

        if (!Security::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            $_SESSION['flash_error'] = 'Erreur de sécurité CSRF.';
            $this->redirect('/register');
            return;
        }

        $fullName = trim($_POST['full_name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['password_confirm'] ?? '';

        if (empty($fullName) || empty($email) || empty($password)) {
            $_SESSION['flash_error'] = 'Veuillez remplir tous les champs obligatoires.';
            $this->redirect('/register');
            return;
        }

        if ($password !== $confirm) {
            $_SESSION['flash_error'] = 'Les deux mots de passe ne correspondent pas.';
            $this->redirect('/register');
            return;
        }

        if (strlen($password) < 6) {
            $_SESSION['flash_error'] = 'Le mot de passe doit comporter au moins 6 caractères.';
            $this->redirect('/register');
            return;
        }

        $userModel = new User();
        if ($userModel->findByUsernameOrEmail($email)) {
            $_SESSION['flash_error'] = 'Un compte existe déjà avec cette adresse e-mail.';
            $this->redirect('/register');
            return;
        }

        // Génération d'un nom d'utilisateur unique à partir de l'email
        $username = explode('@', $email)[0];
        $baseUsername = preg_replace('/[^a-zA-Z0-9_]/', '', $username);
        if (empty($baseUsername)) $baseUsername = 'citoyen';
        $username = $baseUsername;
        $counter = 1;
        while ($userModel->findByUsernameOrEmail($username)) {
            $username = $baseUsername . $counter;
            $counter++;
        }

        // Création du compte avec le rôle citoyen (ID 3)
        $success = $userModel->create([
            'role_id'       => 3, // Rôle Citoyen
            'is_active'     => 1,
            'username'      => $username,
            'email'         => $email,
            'password_hash' => Security::hashPassword($password),
            'full_name'     => $fullName
        ]);

        if ($success) {
            $user = $userModel->findByUsernameOrEmail($email);
            session_regenerate_id(true);
            $_SESSION['user_id']    = $user['id'];
            $_SESSION['username']   = $user['username'];
            $_SESSION['full_name']  = $user['full_name'];
            $_SESSION['email']      = $user['email'];
            $_SESSION['role_name']  = $user['role_name'];
            $_SESSION['role_label'] = $user['role_label'];

            $_SESSION['flash_success'] = 'Votre compte a été créé avec succès ! Bienvenue.';
            $redirectUrl = $_SESSION['redirect_after_login'] ?? '/mon-espace';
            unset($_SESSION['redirect_after_login']);
            $this->redirect($redirectUrl);
        } else {
            $_SESSION['flash_error'] = 'Erreur lors de la création du compte.';
            $this->redirect('/register');
        }
    }

    /**
     * Traitement du jeton d'authentification Google (Google OAuth / GIS One Tap)
     */
    public function googleAuth(): void {
        header('Content-Type: application/json; charset=utf-8');

        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        $idToken = $data['credential'] ?? $_POST['credential'] ?? null;

        if (empty($idToken)) {
            echo json_encode(['success' => false, 'message' => 'Jeton Google manquant.']);
            exit;
        }

        // Décodage sécurisé du JWT Google ID Token
        $parts = explode('.', $idToken);
        if (count($parts) !== 3) {
            echo json_encode(['success' => false, 'message' => 'Format de jeton invalide.']);
            exit;
        }

        $payloadJson = base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[1]));
        $payload = json_decode($payloadJson, true);

        if (empty($payload['sub']) || empty($payload['email'])) {
            echo json_encode(['success' => false, 'message' => 'Données de compte Google invalides.']);
            exit;
        }

        $googleId = $payload['sub'];
        $email    = filter_var($payload['email'], FILTER_SANITIZE_EMAIL);
        $fullName = trim(($payload['name'] ?? '') ?: ($payload['given_name'] ?? 'Citoyen'));

        $userModel = new User();
        // 1. Chercher si l'utilisateur existe déjà par Google ID
        $user = $userModel->findByGoogleId($googleId);

        // 2. Sinon chercher par email
        if (!$user) {
            $userByEmail = $userModel->findByUsernameOrEmail($email);
            if ($userByEmail) {
                // Associer son Google ID
                $userModel->updateGoogleId($userByEmail['id'], $googleId);
                $user = $userModel->getById($userByEmail['id']);
            }
        }

        // 3. Si l'utilisateur n'existe pas du tout, création automatique avec rôle Citoyen (ID 3)
        if (!$user) {
            $username = explode('@', $email)[0];
            $baseUsername = preg_replace('/[^a-zA-Z0-9_]/', '', $username);
            if (empty($baseUsername)) $baseUsername = 'citoyen';
            $username = $baseUsername;
            $counter = 1;
            while ($userModel->findByUsernameOrEmail($username)) {
                $username = $baseUsername . $counter;
                $counter++;
            }

            $created = $userModel->create([
                'role_id'   => 3, // Citoyen par défaut
                'is_active' => 1,
                'username'  => $username,
                'email'     => $email,
                'google_id' => $googleId,
                'full_name' => $fullName
            ]);

            if ($created) {
                $user = $userModel->findByGoogleId($googleId);
            }
        }

        if ($user) {
            if (isset($user['is_active']) && (int)$user['is_active'] === 0) {
                echo json_encode(['success' => false, 'message' => 'Votre compte citoyen a été suspendu.']);
                exit;
            }

            session_regenerate_id(true);
            $_SESSION['user_id']    = $user['id'];
            $_SESSION['username']   = $user['username'];
            $_SESSION['full_name']  = $user['full_name'];
            $_SESSION['email']      = $user['email'];
            $_SESSION['role_name']  = $user['role_name'];
            $_SESSION['role_label'] = $user['role_label'];

            $_SESSION['flash_success'] = 'Connexion réussie avec votre compte Google !';

            $redirectUrl = $_SESSION['redirect_after_login'] ?? '/mon-espace';
            unset($_SESSION['redirect_after_login']);

            if (!str_starts_with($redirectUrl, 'http')) {
                $redirectUrl = str_starts_with($redirectUrl, '/') ? BASE_URL . $redirectUrl : BASE_URL . '/' . $redirectUrl;
            }

            echo json_encode([
                'success'  => true,
                'redirect' => $redirectUrl
            ]);
            exit;
        }

        echo json_encode(['success' => false, 'message' => 'Impossible de vous connecter avec Google.']);
        exit;
    }

    /**
     * Espace Citoyen / Tableau de Bord Personnel
     */
    public function dashboard(): void {
        if (empty($_SESSION['user_id'])) {
            $_SESSION['flash_error'] = 'Veuillez vous connecter pour accéder à votre espace citoyen.';
            $this->redirect('/login');
            return;
        }

        $userId = (int)$_SESSION['user_id'];
        $userModel = new User();
        
        $userInfo = $userModel->getById($userId);
        $comments = $userModel->getUserComments($userId);
        $messages = $userModel->getUserContactMessages($userId);

        // Marquer les réponses Mairie comme vues par le citoyen
        $userModel->markNotificationsAsRead($userId);

        $data = [
            'currentPage'  => 'mon-espace',
            'pageTitle'    => 'Mon Espace Citoyen — Mairie de Tattaguine',
            'user'         => $userInfo,
            'comments'     => $comments,
            'messages'     => $messages,
            'flashSuccess' => $_SESSION['flash_success'] ?? null,
            'flashError'   => $_SESSION['flash_error'] ?? null
        ];

        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        $this->render('auth/dashboard', $data);
    }

    /**
     * Enregistre le jeton d'appareil Firebase Cloud Messaging (FCM) du citoyen connecté
     */
    public function saveFcmToken(): void {
        header('Content-Type: application/json; charset=utf-8');

        if (empty($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Non authentifié.']);
            exit;
        }

        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        $token = trim($data['token'] ?? '');

        if (empty($token)) {
            echo json_encode(['success' => false, 'message' => 'Jeton FCM manquant.']);
            exit;
        }

        require_once ROOT_PATH . '/core/FirebaseMessaging.php';
        $db = Database::getInstance();
        $saved = FirebaseMessaging::saveToken($db, (int)$_SESSION['user_id'], $token);

        echo json_encode(['success' => $saved]);
        exit;
    }

    /**
     * Déconnexion du citoyen
     */
    public function logout(): void {
        unset($_SESSION['user_id'], $_SESSION['username'], $_SESSION['full_name'], $_SESSION['email'], $_SESSION['role_name'], $_SESSION['role_label']);
        $_SESSION['flash_success'] = 'Vous avez été déconnecté avec succès.';
        $this->redirect('/login');
    }
}
