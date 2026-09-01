<?php
// ====================================================================
// CONTRÔLEUR DOCUMENTADMINCONTROLLER (GESTION DES DOCUMENTS OFFICIELS)
// ====================================================================

namespace Admin;

use Controller;
use Database;
use Security;
use Document;

require_once ROOT_PATH . '/core/Controller.php';
require_once APP_PATH . '/Models/Document.php';

class DocumentAdminController extends Controller {
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
        Security::checkAdminSessionTimeout(1800);
    }

    private function getValidAuthorId(): int {
        $this->checkAuth();
        $userId = (int)$_SESSION['user_id'];

        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT id FROM users WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $userId]);
        $exists = $stmt->fetchColumn();

        if ($exists) {
            return (int)$exists;
        }

        $firstAdmin = $db->query("SELECT id FROM users ORDER BY id ASC LIMIT 1")->fetchColumn();
        return $firstAdmin ? (int)$firstAdmin : 1;
    }

    public function index(): void {
        $this->checkAuth();
        $docModel = new Document();
        $documents = $docModel->getAll();

        $data = [
            'pageTitle'    => 'Gestion des Documents Administratifs — Espace Admin',
            'documents'    => $documents,
            'csrfToken'    => Security::generateCsrfToken(),
            'flashSuccess' => $_SESSION['flash_success'] ?? null,
            'flashError'   => $_SESSION['flash_error'] ?? null
        ];

        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        $this->render('admin/documents/index', $data);
    }

    public function store(): void {
        $authorId = $this->getValidAuthorId();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/documents');
            return;
        }

        if (!Security::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            $_SESSION['flash_error'] = 'Erreur de sécurité CSRF.';
            $this->redirect('/admin/documents');
            return;
        }

        $title       = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $category    = trim($_POST['category'] ?? 'Démarches');

        if (empty($title) || empty($_FILES['file']['name'])) {
            $_SESSION['flash_error'] = 'Le titre et le fichier PDF sont obligatoires.';
            $this->redirect('/admin/documents');
            return;
        }

        $file = $_FILES['file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'pdf') {
            $_SESSION['flash_error'] = 'Seuls les fichiers au format PDF sont autorisés.';
            $this->redirect('/admin/documents');
            return;
        }

        $formattedSize = round($file['size'] / 1024 / 1024, 2) . ' Mo';
        $filename = 'doc_' . time() . '_' . uniqid() . '.pdf';
        $targetPath = UPLOADS_PATH . '/documents/' . $filename;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            $docModel = new Document();
            $docModel->create([
                'title'       => $title,
                'description' => $description,
                'category'    => $category,
                'file_path'   => 'uploads/documents/' . $filename,
                'file_size'   => $formattedSize,
                'uploaded_by' => $authorId
            ]);

            $_SESSION['flash_success'] = 'Le document a été mis en ligne avec succès.';
        } else {
            $_SESSION['flash_error'] = 'Échec du téléversement du fichier.';
        }

        $this->redirect('/admin/documents');
    }

    public function update(int $id): void {
        $this->checkAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/documents');
            return;
        }

        if (!Security::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            $_SESSION['flash_error'] = 'Erreur de sécurité CSRF.';
            $this->redirect('/admin/documents');
            return;
        }

        $title       = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $category    = trim($_POST['category'] ?? 'Démarches');

        if (empty($title)) {
            $_SESSION['flash_error'] = 'Le titre du document ne peut pas être vide.';
            $this->redirect('/admin/documents');
            return;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM documents WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $existing = $stmt->fetch();

        if (!$existing) {
            $_SESSION['flash_error'] = 'Document introuvable.';
            $this->redirect('/admin/documents');
            return;
        }

        $filePath = $existing['file_path'];
        $fileSize = $existing['file_size'];

        if (!empty($_FILES['file']['name'])) {
            $file = $_FILES['file'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if ($ext === 'pdf') {
                $formattedSize = round($file['size'] / 1024 / 1024, 2) . ' Mo';
                $filename = 'doc_' . time() . '_' . uniqid() . '.pdf';
                $targetPath = UPLOADS_PATH . '/documents/' . $filename;
                if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                    $filePath = 'uploads/documents/' . $filename;
                    $fileSize = $formattedSize;
                }
            }
        }

        $updateStmt = $db->prepare("UPDATE documents SET title = :title, description = :description, category = :category, file_path = :file_path, file_size = :file_size WHERE id = :id");
        $updateStmt->execute([
            ':title'       => $title,
            ':description' => $description,
            ':category'    => $category,
            ':file_path'   => $filePath,
            ':file_size'   => $fileSize,
            ':id'          => $id
        ]);

        $_SESSION['flash_success'] = 'Le document a été mis à jour avec succès.';
        $this->redirect('/admin/documents');
    }

    public function delete(int $id): void {
        $this->checkAuth();
        $docModel = new Document();
        $docModel->delete($id);

        $_SESSION['flash_success'] = 'Le document a été supprimé.';
        $this->redirect('/admin/documents');
    }
}
