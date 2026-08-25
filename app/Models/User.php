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
        $sql = "SELECT u.*, r.label AS role_label 
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
     * Créer un nouvel utilisateur/agent avec mot de passe haché et état d'activation
     */
    public function create(array $data): bool {
        $sql = "INSERT INTO users (role_id, is_active, username, email, password_hash, full_name) 
                VALUES (:role_id, :is_active, :username, :email, :password_hash, :full_name)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':role_id'       => $data['role_id'],
            ':is_active'     => $data['is_active'] ?? 1,
            ':username'      => $data['username'],
            ':email'         => $data['email'],
            ':password_hash' => $data['password_hash'],
            ':full_name'     => $data['full_name']
        ]);
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
