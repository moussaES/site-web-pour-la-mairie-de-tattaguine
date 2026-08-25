<?php
// ====================================================================
// MODÈLE CATEGORY (CATÉGORIES DE PUBLICATIONS)
// ====================================================================

require_once ROOT_PATH . '/core/Model.php';

class Category extends Model {
    public function getAll(): array {
        $sql = "SELECT * FROM categories ORDER BY name ASC";
        return $this->db->query($sql)->fetchAll();
    }

    public function getBySlug(string $slug): ?array {
        $sql = "SELECT * FROM categories WHERE slug = :slug LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':slug' => $slug]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
