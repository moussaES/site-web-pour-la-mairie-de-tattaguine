<?php
// ====================================================================
// MODÈLE POST (GESTION DES ACTUALITÉS ET PUBLICATIONS)
// ====================================================================

require_once ROOT_PATH . '/core/Model.php';

class Post extends Model {
    /**
     * Récupérer les dernières publications publiées
     */
    public function getLatest(int $limit = 6): array {
        $sql = "SELECT p.*, c.name AS category_name, c.slug AS category_slug, u.full_name AS author_name 
                FROM posts p
                JOIN categories c ON p.category_id = c.id
                JOIN users u ON p.author_id = u.id
                WHERE p.status = 'published'
                ORDER BY p.created_at DESC
                LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Récupérer un article par son slug
     */
    public function getBySlug(string $slug): ?array {
        $cleanSlug = trim(urldecode($slug));
        $sql = "SELECT p.*, c.name AS category_name, c.slug AS category_slug, u.full_name AS author_name 
                FROM posts p
                JOIN categories c ON p.category_id = c.id
                JOIN users u ON p.author_id = u.id
                WHERE (p.slug = :slug OR LOWER(p.slug) = LOWER(:clean_slug)) AND p.status = 'published'
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':slug' => $slug, ':clean_slug' => $cleanSlug]);
        $post = $stmt->fetch();
        return $post ?: null;
    }

    /**
     * Incrémenter le nombre de vues d'un article
     */
    public function incrementViews(int $id): void {
        $sql = "UPDATE posts SET views_count = views_count + 1 WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
    }
}
