<?php
// ====================================================================
// CONTRÔLEUR POSTCONTROLLER (CONSULTATION DES PUBLICATIONS ET VIDÉOS)
// ====================================================================

require_once ROOT_PATH . '/core/Controller.php';
require_once APP_PATH . '/Models/Post.php';
require_once APP_PATH . '/Models/Category.php';
require_once APP_PATH . '/Models/Comment.php';
require_once APP_PATH . '/Models/Stat.php';

class PostController extends Controller {
    /**
     * Liste des actualités avec moteur de recherche et filtre par catégorie
     */
    public function index(): void {
        $statModel = new Stat();
        $statModel->recordVisit('/actualites');

        $searchQuery = Security::sanitize($_GET['q'] ?? '');
        $categorySlug = Security::sanitize($_GET['category'] ?? '');

        $postModel = new Post();
        $categoryModel = new Category();

        // Récupérer les catégories pour la barre de filtre
        $categories = $categoryModel->getAll();

        // Construction de la requête avec filtres facultatifs
        $db = Database::getInstance();
        $sql = "SELECT p.*, c.name AS category_name, u.full_name AS author_name 
                FROM posts p
                JOIN categories c ON p.category_id = c.id
                JOIN users u ON p.author_id = u.id
                WHERE p.status = 'published'";
        
        $params = [];

        if (!empty($searchQuery)) {
            $sql .= " AND (p.title LIKE :search OR p.content LIKE :search OR p.excerpt LIKE :search)";
            $params[':search'] = '%' . $searchQuery . '%';
        }

        if (!empty($categorySlug)) {
            $sql .= " AND c.slug = :category";
            $params[':category'] = $categorySlug;
        }

        $sql .= " ORDER BY p.created_at DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $posts = $stmt->fetchAll();

        $data = [
            'pageTitle'    => 'Actualités & Communiqués — Commune de Tattaguine',
            'currentPage'  => 'actualites',
            'posts'        => $posts,
            'categories'   => $categories,
            'searchQuery'  => $searchQuery,
            'categorySlug' => $categorySlug
        ];

        $this->render('posts/index', $data);
    }

    /**
     * Affichage détaillé d'une publication avec commentaires et lecteur média
     */
    public function show(string $slug): void {
        $postModel = new Post();
        $post = $postModel->getBySlug($slug);

        if (!$post) {
            $_SESSION['flash_error'] = "L'article ou la vidéo demandée n'a pas été trouvé(e).";
            header('Location: ' . BASE_URL . '/actualites');
            exit;
        }

        // Incrémenter le nombre de vues
        $postModel->incrementViews($post['id']);

        // Enregistrer la visite
        $statModel = new Stat();
        $statModel->recordVisit('/actualites/' . $slug);

        // Charger les commentaires approuvés
        $commentModel = new Comment();
        $approvedComments = $commentModel->getApprovedByPost($post['id']);

        // Générer la question CAPTCHA mathématique
        $captcha = Security::generateCaptchaMath();

        $data = [
            'pageTitle'        => $post['title'] . ' — Commune de Tattaguine',
            'currentPage'      => 'actualites',
            'post'             => $post,
            'comments'         => $approvedComments,
            'captchaQuestion'  => $captcha['question'],
            'csrfToken'        => Security::generateCsrfToken(),
            'flashSuccess'     => $_SESSION['flash_success'] ?? null,
            'flashError'       => $_SESSION['flash_error'] ?? null
        ];

        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        $this->render('posts/show', $data);
    }
}
