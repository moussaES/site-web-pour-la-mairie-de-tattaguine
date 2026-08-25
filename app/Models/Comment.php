<?php
// ====================================================================
// MODÈLE COMMENT (GESTION ET MODÉRATION DES COMMENTAIRES CITOYENS)
// ====================================================================

require_once ROOT_PATH . '/core/Model.php';

class Comment extends Model {
    /**
     * Récupérer les commentaires approuvés d'un article
     */
    public function getApprovedByPost(int $postId): array {
        $sql = "SELECT * FROM comments 
                WHERE post_id = :post_id AND status = 'approved' 
                ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':post_id' => $postId]);
        return $stmt->fetchAll();
    }

    /**
     * Enregistrer un nouveau commentaire (en attente par défaut)
     */
    public function create(array $data): bool {
        $sql = "INSERT INTO comments (post_id, author_name, author_email, content, status) 
                VALUES (:post_id, :author_name, :author_email, :content, 'pending')";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':post_id'      => $data['post_id'],
            ':author_name'  => $data['author_name'],
            ':author_email' => $data['author_email'] ?? null,
            ':content'      => $data['content']
        ]);
    }

    /**
     * Récupérer tous les commentaires pour l'espace admin (avec filtre par statut)
     */
    public function getAllForAdmin(?string $status = null): array {
        $sql = "SELECT c.*, p.title AS post_title, p.slug AS post_slug 
                FROM comments c
                JOIN posts p ON c.post_id = p.id";
        
        if ($status) {
            $sql .= " WHERE c.status = :status";
        }
        
        $sql .= " ORDER BY c.created_at DESC";
        $stmt = $this->db->prepare($sql);
        if ($status) {
            $stmt->bindValue(':status', $status);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Compter le nombre de commentaires en attente de modération
     */
    public function countPending(): int {
        $sql = "SELECT COUNT(*) FROM comments WHERE status = 'pending'";
        return (int)$this->db->query($sql)->fetchColumn();
    }

    /**
     * Mettre à jour le statut d'un commentaire (approved, rejected)
     */
    public function updateStatus(int $id, string $status, ?string $adminResponse = null): bool {
        $sql = "UPDATE comments SET status = :status, admin_response = :admin_response WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':status'         => $status,
            ':admin_response' => $adminResponse,
            ':id'             => $id
        ]);
    }

    /**
     * Supprimer un commentaire
     */
    public function delete(int $id): bool {
        $sql = "DELETE FROM comments WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}
