<?php
// ====================================================================
// SCRIPT DE MIGRATION AUTOMATIQUE (DATABASE AUTO-MIGRATION)
// ====================================================================

class DatabaseMigrate {
    public static function run(PDO $db): void {
        try {
            // 1. S'assurer que le rôle 'citoyen' (ID 3) existe dans la table roles
            $stmt = $db->query("SELECT COUNT(*) FROM roles WHERE id = 3 OR name = 'citoyen'");
            if ((int)$stmt->fetchColumn() === 0) {
                $db->exec("INSERT INTO roles (id, name, label) VALUES (3, 'citoyen', 'Citoyen')");
            }

            // 2. Ajouter la colonne google_id dans la table users si elle n'existe pas
            $stmt = $db->query("SHOW COLUMNS FROM users LIKE 'google_id'");
            if ($stmt->rowCount() === 0) {
                $db->exec("ALTER TABLE users ADD COLUMN google_id VARCHAR(255) NULL UNIQUE AFTER email");
            }

            // 3. Modifier password_hash pour accepter des valeurs NULL (connexion Google)
            $db->exec("ALTER TABLE users MODIFY COLUMN password_hash VARCHAR(255) NULL");

            // 4. Ajouter user_id dans comments si absent
            $stmt = $db->query("SHOW COLUMNS FROM comments LIKE 'user_id'");
            if ($stmt->rowCount() === 0) {
                $db->exec("ALTER TABLE comments ADD COLUMN user_id INT NULL AFTER post_id");
            }

            // 5. Ajouter user_id dans contact_messages si absent
            $stmt = $db->query("SHOW COLUMNS FROM contact_messages LIKE 'user_id'");
            if ($stmt->rowCount() === 0) {
                $db->exec("ALTER TABLE contact_messages ADD COLUMN user_id INT NULL AFTER id");
            }

            // 6. Ajouter admin_response dans comments si absent
            $stmt = $db->query("SHOW COLUMNS FROM comments LIKE 'admin_response'");
            if ($stmt->rowCount() === 0) {
                $db->exec("ALTER TABLE comments ADD COLUMN admin_response TEXT NULL AFTER content");
            }

            // 7. Ajouter admin_reply et replied_at dans contact_messages si absent
            $stmt = $db->query("SHOW COLUMNS FROM contact_messages LIKE 'admin_reply'");
            if ($stmt->rowCount() === 0) {
                $db->exec("ALTER TABLE contact_messages ADD COLUMN admin_reply TEXT NULL AFTER message, ADD COLUMN replied_at DATETIME NULL AFTER admin_reply");
            }
            // 8. Ajouter is_read_by_user dans comments et contact_messages
            $stmt = $db->query("SHOW COLUMNS FROM comments LIKE 'is_read_by_user'");
            if ($stmt->rowCount() === 0) {
                $db->exec("ALTER TABLE comments ADD COLUMN is_read_by_user INT NOT NULL DEFAULT 0");
            }

            $stmt = $db->query("SHOW COLUMNS FROM contact_messages LIKE 'is_read_by_user'");
            if ($stmt->rowCount() === 0) {
                $db->exec("ALTER TABLE contact_messages ADD COLUMN is_read_by_user INT NOT NULL DEFAULT 0");
            }

            // 9. Créer la table user_fcm_tokens pour les jetons de notifications Firebase
            $db->exec("CREATE TABLE IF NOT EXISTS user_fcm_tokens (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                token TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX (user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } catch (Exception $e) {
            // Ne pas bloquer l'exécution si la migration a déjà été appliquée
            error_log("Database Migration Note: " . $e->getMessage());
        }
    }
}
