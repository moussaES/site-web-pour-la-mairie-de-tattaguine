<?php
// ====================================================================
// MODÈLE USER (UTILISATEURS ET AGENTS DE LA MAIRIE)
// ====================================================================

require_once ROOT_PATH . '/core/Model.php';

class User extends Model {
    /**
     * Trouver un utilisateur par son identifiant ou son email
     */
    public function findByUsernameOrEmail(string $identifier): ?array {
        $sql = "SELECT u.*, r.name AS role_name, r.label AS role_label 
                FROM users u
                JOIN roles r ON u.role_id = r.id
                WHERE u.username = :username OR u.email = :email
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':username' => $identifier,
            ':email'    => $identifier
        ]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Trouver un utilisateur par son Google ID
     */
    public function findByGoogleId(string $googleId): ?array {
        $sql = "SELECT u.*, r.name AS role_name, r.label AS role_label 
                FROM users u
                JOIN roles r ON u.role_id = r.id
                WHERE u.google_id = :google_id
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':google_id' => $googleId]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Mettre à jour le Google ID d'un utilisateur existant
     */
    public function updateGoogleId(int $id, string $googleId): bool {
        $sql = "UPDATE users SET google_id = :google_id WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':google_id' => $googleId, ':id' => $id]);
    }

    /**
     * Récupérer tous les agents municipaux avec leurs rôles et statuts
     */
    public function getAll(): array {
        $sql = "SELECT u.*, r.label AS role_label 
                FROM users u
                JOIN roles r ON u.role_id = r.id
                ORDER BY u.created_at DESC";
        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Récupérer un agent municipal par son ID
     */
    public function getById(int $id): ?array {
        $sql = "SELECT u.*, r.name AS role_name, r.label AS role_label 
                FROM users u
                JOIN roles r ON u.role_id = r.id
                WHERE u.id = :id
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Créer un nouvel utilisateur/agent/citoyen avec mot de passe haché ou google_id et rôle
     */
    public function create(array $data): bool {
        $sql = "INSERT INTO users (role_id, is_active, username, email, google_id, password_hash, full_name) 
                VALUES (:role_id, :is_active, :username, :email, :google_id, :password_hash, :full_name)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':role_id'       => $data['role_id'] ?? 3, // Rôle 3 = citoyen par défaut
            ':is_active'     => $data['is_active'] ?? 1,
            ':username'      => $data['username'],
            ':email'         => $data['email'],
            ':google_id'     => $data['google_id'] ?? null,
            ':password_hash' => $data['password_hash'] ?? null,
            ':full_name'     => $data['full_name']
        ]);
    }

    /**
     * Récupérer l'historique des commentaires soumis par un citoyen connecté
     */
    public function getUserComments(int $userId): array {
        $sql = "SELECT c.*, p.title AS post_title, p.slug AS post_slug 
                FROM comments c
                JOIN posts p ON c.post_id = p.id
                WHERE c.user_id = :user_id
                ORDER BY c.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }

    /**
     * Récupérer l'historique des messages de contact transmis par un citoyen connecté
     */
    public function getUserContactMessages(int $userId): array {
        $sql = "SELECT * FROM contact_messages 
                WHERE user_id = :user_id
                ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }

    /**
     * Obtenir le nombre de nouvelles réponses de la Mairie non lues par le citoyen
     */
    public function getUnreadNotificationCount(int $userId): int {
        $sqlComment = "SELECT COUNT(*) FROM comments WHERE user_id = :user_id AND admin_response IS NOT NULL AND is_read_by_user = 0";
        $stmt1 = $this->db->prepare($sqlComment);
        $stmt1->execute([':user_id' => $userId]);
        $commentCount = (int)$stmt1->fetchColumn();

        $sqlMessage = "SELECT COUNT(*) FROM contact_messages WHERE user_id = :user_id AND admin_reply IS NOT NULL AND is_read_by_user = 0";
        $stmt2 = $this->db->prepare($sqlMessage);
        $stmt2->execute([':user_id' => $userId]);
        $messageCount = (int)$stmt2->fetchColumn();

        return $commentCount + $messageCount;
    }

    /**
     * Marquer toutes les réponses Mairie comme lues par le citoyen lorsqu'il visite son espace
     */
    public function markNotificationsAsRead(int $userId): void {
        $sql1 = "UPDATE comments SET is_read_by_user = 1 WHERE user_id = :user_id AND admin_response IS NOT NULL";
        $stmt1 = $this->db->prepare($sql1);
        $stmt1->execute([':user_id' => $userId]);

        $sql2 = "UPDATE contact_messages SET is_read_by_user = 1 WHERE user_id = :user_id AND admin_reply IS NOT NULL";
        $stmt2 = $this->db->prepare($sql2);
        $stmt2->execute([':user_id' => $userId]);
    }

    /**
     * Mettre à jour les informations d'un agent municipal
     */
    public function update(int $id, array $data): bool {
        $fields = [
            'role_id = :role_id',
            'is_active = :is_active',
            'username = :username',
            'email = :email',
            'full_name = :full_name'
        ];

        $params = [
            ':role_id'   => $data['role_id'],
            ':is_active' => $data['is_active'],
            ':username'  => $data['username'],
            ':email'     => $data['email'],
            ':full_name' => $data['full_name'],
            ':id'        => $id
        ];

        // Si un nouveau mot de passe a été fourni
        if (!empty($data['password_hash'])) {
            $fields[] = 'password_hash = :password_hash';
            $params[':password_hash'] = $data['password_hash'];
        }

        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Supprimer un agent municipal
     */
    public function delete(int $id): bool {
        $sql = "DELETE FROM users WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}
