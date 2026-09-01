<?php
// ====================================================================
// CONTRÔLEUR USERADMINCONTROLLER (GESTION DES AGENTS & RÔLES RBAC)
// ====================================================================

namespace Admin;

use Controller;
use Security;
use User;

require_once ROOT_PATH . '/core/Controller.php';
require_once APP_PATH . '/Models/User.php';

class UserAdminController extends Controller {
    private function checkSuperAdmin(): void {
        if (empty($_SESSION['user_id']) || ($_SESSION['role_name'] ?? '') !== 'super_admin') {
            $_SESSION['flash_error'] = 'Accès refusé : Seul le Super-Administrateur peut gérer les comptes utilisateurs.';
            header('Location: ' . BASE_URL . '/admin/dashboard');
            exit;
        }

        // Vérification de l'inactivité administrateur (expiration après 30 minutes d'inactivité)
        \Security::checkAdminSessionTimeout(1800);
    }

    public function index(): void {
        $this->checkSuperAdmin();
        $userModel = new User();
        $users = $userModel->getAll();

        $data = [
            'pageTitle'    => 'Gestion des Agents Municipaux (RBAC) — Espace Admin',
            'users'        => $users,
            'csrfToken'    => Security::generateCsrfToken(),
            'flashSuccess' => $_SESSION['flash_success'] ?? null,
            'flashError'   => $_SESSION['flash_error'] ?? null
        ];

        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        $this->render('admin/users/index', $data);
    }

    public function store(): void {
        $this->checkSuperAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/users');
            return;
        }

        if (!Security::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            $_SESSION['flash_error'] = 'Erreur de sécurité CSRF.';
            $this->redirect('/admin/users');
            return;
        }

        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $fullName = trim($_POST['full_name'] ?? '');
        $password = $_POST['password'] ?? '';
        $roleId   = (int)($_POST['role_id'] ?? 2);
        $isActive = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;

        if (empty($username) || empty($email) || empty($password) || empty($fullName)) {
            $_SESSION['flash_error'] = 'Veuillez remplir tous les champs.';
            $this->redirect('/admin/users');
            return;
        }

        $userModel = new User();
        
        if ($userModel->findByUsernameOrEmail($username) || $userModel->findByUsernameOrEmail($email)) {
            $_SESSION['flash_error'] = 'Cet identifiant ou cet e-mail est déjà utilisé.';
            $this->redirect('/admin/users');
            return;
        }

        $success = $userModel->create([
            'role_id'       => $roleId,
            'is_active'     => $isActive,
            'username'      => $username,
            'email'         => $email,
            'password_hash' => Security::hashPassword($password),
            'full_name'     => $fullName
        ]);

        if ($success) {
            $_SESSION['flash_success'] = 'Le compte de l\'agent municipal a été créé avec succès.';
        } else {
            $_SESSION['flash_error'] = 'Erreur lors de la création du compte.';
        }

        $this->redirect('/admin/users');
    }

    public function update(int $id): void {
        $this->checkSuperAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/users');
            return;
        }

        if (!Security::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            $_SESSION['flash_error'] = 'Erreur de sécurité CSRF.';
            $this->redirect('/admin/users');
            return;
        }

        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $fullName = trim($_POST['full_name'] ?? '');
        $password = $_POST['password'] ?? '';
        $roleId   = (int)($_POST['role_id'] ?? 2);
        $isActive = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;

        if (empty($username) || empty($email) || empty($fullName)) {
            $_SESSION['flash_error'] = 'Le nom, l\'identifiant et l\'adresse e-mail sont obligatoires.';
            $this->redirect('/admin/users');
            return;
        }

        if ($id === (int)$_SESSION['user_id'] && $isActive === 0) {
            $_SESSION['flash_error'] = 'Vous ne pouvez pas désactiver votre propre compte d\'administrateur connecté.';
            $this->redirect('/admin/users');
            return;
        }

        $userModel = new User();
        $existing = $userModel->getById($id);

        if (!$existing) {
            $_SESSION['flash_error'] = 'Compte d\'agent introuvable.';
            $this->redirect('/admin/users');
            return;
        }

        $updateData = [
            'role_id'   => $roleId,
            'is_active' => $isActive,
            'username'  => $username,
            'email'     => $email,
            'full_name' => $fullName
        ];

        if (!empty($password)) {
            $updateData['password_hash'] = Security::hashPassword($password);
        }

        $success = $userModel->update($id, $updateData);

        if ($success) {
            $_SESSION['flash_success'] = 'Le compte de l\'agent municipal a été mis à jour avec succès.';
        } else {
            $_SESSION['flash_error'] = 'Erreur lors de la mise à jour du compte.';
        }

        $this->redirect('/admin/users');
    }

    public function delete(int $id): void {
        $this->checkSuperAdmin();

        if ($id === (int)$_SESSION['user_id']) {
            $_SESSION['flash_error'] = 'Vous ne pouvez pas supprimer votre propre compte.';
            $this->redirect('/admin/users');
            return;
        }

        $userModel = new User();
        $userModel->delete($id);

        $_SESSION['flash_success'] = 'Le compte de l\'agent municipal a été supprimé.';
        $this->redirect('/admin/users');
    }
}
