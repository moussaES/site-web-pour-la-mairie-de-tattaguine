<?php
// ====================================================================
// MODÈLE DOCUMENT (GESTION DES DOCUMENTS ADMINISTRATIFS)
// ====================================================================

require_once ROOT_PATH . '/core/Model.php';

class Document extends Model {
    /**
     * Récupérer tous les documents avec le nom de l'agent l'ayant mis en ligne
     */
    public function getAll(?string $category = null): array {
        $sql = "SELECT d.*, u.full_name AS author_name 
                FROM documents d
                JOIN users u ON d.uploaded_by = u.id";
        
        if ($category) {
            $sql .= " WHERE d.category = :category";
        }
        
        $sql .= " ORDER BY d.created_at DESC";
        $stmt = $this->db->prepare($sql);
        if ($category) {
            $stmt->bindValue(':category', $category);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Enregistrer un nouveau document téléversé
     */
    public function create(array $data): bool {
        $sql = "INSERT INTO documents (title, description, category, file_path, file_size, uploaded_by) 
                VALUES (:title, :description, :category, :file_path, :file_size, :uploaded_by)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':title'       => $data['title'],
            ':description' => $data['description'] ?? null,
            ':category'    => $data['category'],
            ':file_path'   => $data['file_path'],
            ':file_size'   => $data['file_size'],
            ':uploaded_by' => $data['uploaded_by']
        ]);
    }

    /**
     * Incrémenter le nombre de téléchargements d'un document
     */
    public function incrementDownloads(int $id): void {
        $sql = "UPDATE documents SET downloads_count = downloads_count + 1 WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
    }

    /**
     * Supprimer un document
     */
    public function delete(int $id): bool {
        $sql = "DELETE FROM documents WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}
