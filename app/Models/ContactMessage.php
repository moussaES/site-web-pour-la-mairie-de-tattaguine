<?php
// ====================================================================
// MODÈLE CONTACTMESSAGE (MESSAGES ET DEMANDES DES CITOYENS)
// ====================================================================

require_once ROOT_PATH . '/core/Model.php';

class ContactMessage extends Model {
    public function create(array $data): bool {
        $sql = "INSERT INTO contact_messages (full_name, email, phone, subject, message) 
                VALUES (:full_name, :email, :phone, :subject, :message)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':full_name' => $data['full_name'],
            ':email'     => $data['email'],
            ':phone'     => $data['phone'] ?? null,
            ':subject'   => $data['subject'],
            ':message'   => $data['message']
        ]);
    }

    public function getAll(): array {
        $sql = "SELECT * FROM contact_messages ORDER BY created_at DESC";
        return $this->db->query($sql)->fetchAll();
    }

    public function countUnread(): int {
        $sql = "SELECT COUNT(*) FROM contact_messages WHERE is_read = 0";
        return (int)$this->db->query($sql)->fetchColumn();
    }

    public function markAsRead(int $id): void {
        $sql = "UPDATE contact_messages SET is_read = 1 WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
    }
}
