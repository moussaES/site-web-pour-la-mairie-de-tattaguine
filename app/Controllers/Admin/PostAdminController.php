<?php
// ====================================================================
// CONTRÔLEUR POSTADMINCONTROLLER (GESTION ADMIN DES PUBLICATIONS ET VIDÉOS)
// ====================================================================

namespace Admin;

use Controller;
use Database;
use Security;
use Category;

require_once ROOT_PATH . '/core/Controller.php';
require_once APP_PATH . '/Models/Category.php';

class PostAdminController extends Controller {
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

    private function slugify(string $text): string {
        $transliterator = [
            'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'AE', 'Ç'=>'C',
            'È'=>'E', 'É'=>'E', 'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I',
            'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'ae', 'ç'=>'c',
            'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i',
            'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u',
            'û'=>'u', 'ü'=>'u', 'ý'=>'y', '\''=>'-', '"'=>'-', '’'=>'-'
        ];
        $text = strtr($text, $transliterator);
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = trim($text, '-');
        $text = strtolower($text);
        return empty($text) ? 'post-' . time() : $text;
    }

    public function index(): void {
        $this->checkAuth();
        $db = Database::getInstance();
        $categoryModel = new Category();

        $sql = "SELECT p.*, c.name AS category_name, u.full_name AS author_name 
                FROM posts p
                JOIN categories c ON p.category_id = c.id
                JOIN users u ON p.author_id = u.id
                ORDER BY p.created_at DESC";
        $posts = $db->query($sql)->fetchAll();
        $categories = $categoryModel->getAll();

        $data = [
            'pageTitle'    => 'Gestion des Actualités & Vidéos — Espace Admin',
            'posts'        => $posts,
            'categories'   => $categories,
            'csrfToken'    => Security::generateCsrfToken(),
            'flashSuccess' => $_SESSION['flash_success'] ?? null,
            'flashError'   => $_SESSION['flash_error'] ?? null
        ];

        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        $this->render('admin/posts/index', $data);
    }

    public function createForm(): void {
        $this->checkAuth();
        $categoryModel = new Category();
        
        $data = [
            'pageTitle'  => 'Nouvelle Publication — Espace Admin',
            'categories' => $categoryModel->getAll(),
            'csrfToken'  => Security::generateCsrfToken()
        ];

        $this->render('admin/posts/create', $data);
    }

    public function store(): void {
        $authorId = $this->getValidAuthorId();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/posts');
            return;
        }

        if (!Security::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            $_SESSION['flash_error'] = 'Erreur de sécurité CSRF.';
            $this->redirect('/admin/posts/create');
            return;
        }

        $rawTitle   = trim($_POST['title'] ?? '');
        $title      = $rawTitle;
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $excerpt    = trim($_POST['excerpt'] ?? '');
        $content    = $_POST['content'] ?? ''; 
        $videoUrl   = trim($_POST['video_url'] ?? '');
        $status     = trim($_POST['status'] ?? 'published');

        if (empty($rawTitle) || empty($categoryId) || empty($content)) {
            $_SESSION['flash_error'] = 'Le titre, la catégorie et le contenu sont obligatoires.';
            $this->redirect('/admin/posts/create');
            return;
        }

        // Générer un slug unique à partir du titre brut
        $slug = $this->slugify($rawTitle);

        // Traitement du téléversement de l'image
        $imagePath = null;
        if (!empty($_FILES['image']['name'])) {
            $file = $_FILES['image'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];

            if (in_array($ext, $allowedExts)) {
                $filename = 'post_' . time() . '_' . uniqid() . '.' . $ext;
                $target = UPLOADS_PATH . '/posts/' . $filename;
                if (move_uploaded_file($file['tmp_name'], $target)) {
                    $imagePath = 'uploads/posts/' . $filename;
                }
            }
        }

        $db = Database::getInstance();
        $sql = "INSERT INTO posts (category_id, author_id, title, slug, excerpt, content, image_path, video_url, status) 
                VALUES (:category_id, :author_id, :title, :slug, :excerpt, :content, :image_path, :video_url, :status)";
        $stmt = $db->prepare($sql);
        $success = $stmt->execute([
            ':category_id' => $categoryId,
            ':author_id'   => $authorId,
            ':title'       => $title,
            ':slug'        => $slug,
            ':excerpt'     => $excerpt,
            ':content'     => $content,
            ':image_path'  => $imagePath,
            ':video_url'   => $videoUrl,
            ':status'      => $status
        ]);

        if ($success) {
            $_SESSION['flash_success'] = 'La publication a été créée avec succès.';
        } else {
            $_SESSION['flash_error'] = 'Erreur lors de la création de la publication.';
        }

        $this->redirect('/admin/posts');
    }

    public function update(int $id): void {
        $this->checkAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/posts');
            return;
        }

        if (!Security::verifyCsrfToken($_POST['csrf_token'] ?? null)) {
            $_SESSION['flash_error'] = 'Erreur de sécurité CSRF.';
            $this->redirect('/admin/posts');
            return;
        }

        $rawTitle   = trim($_POST['title'] ?? '');
        $title      = $rawTitle;
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $excerpt    = trim($_POST['excerpt'] ?? '');
        $content    = $_POST['content'] ?? ''; 
        $videoUrl   = trim($_POST['video_url'] ?? '');
        $status     = trim($_POST['status'] ?? 'published');

        if (empty($rawTitle) || empty($categoryId) || empty($content)) {
            $_SESSION['flash_error'] = 'Le titre, la catégorie et le contenu sont obligatoires.';
            $this->redirect('/admin/posts');
            return;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM posts WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $existing = $stmt->fetch();

        if (!$existing) {
            $_SESSION['flash_error'] = 'Publication introuvable.';
            $this->redirect('/admin/posts');
            return;
        }

        $imagePath = $existing['image_path'];

        if (!empty($_FILES['image']['name'])) {
            $file = $_FILES['image'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];

            if (in_array($ext, $allowedExts)) {
                $filename = 'post_' . time() . '_' . uniqid() . '.' . $ext;
                $target = UPLOADS_PATH . '/posts/' . $filename;
                if (move_uploaded_file($file['tmp_name'], $target)) {
                    $imagePath = 'uploads/posts/' . $filename;
                }
            }
        }

        $slug = ($existing['title'] !== $title) ? $this->slugify($rawTitle) : $existing['slug'];

        $sql = "UPDATE posts SET category_id = :category_id, title = :title, slug = :slug, excerpt = :excerpt, content = :content, image_path = :image_path, video_url = :video_url, status = :status WHERE id = :id";
        $updateStmt = $db->prepare($sql);
        $success = $updateStmt->execute([
            ':category_id' => $categoryId,
            ':title'       => $title,
            ':slug'        => $slug,
            ':excerpt'     => $excerpt,
            ':content'     => $content,
            ':image_path'  => $imagePath,
            ':video_url'   => $videoUrl,
            ':status'      => $status,
            ':id'          => $id
        ]);

        if ($success) {
            $_SESSION['flash_success'] = 'La publication a été mise à jour avec succès.';
        } else {
            $_SESSION['flash_error'] = 'Erreur lors de la mise à jour de la publication.';
        }

        $this->redirect('/admin/posts');
    }

    public function delete(int $id): void {
        $this->checkAuth();
        $db = Database::getInstance();

        $stmt = $db->prepare("DELETE FROM posts WHERE id = :id");
        $stmt->execute([':id' => $id]);

        $_SESSION['flash_success'] = 'La publication a été supprimée.';
        $this->redirect('/admin/posts');
    }
}
