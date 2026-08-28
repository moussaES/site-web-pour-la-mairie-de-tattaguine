<?php
// ====================================================================
// CLASSE SINGLETON PDO DE CONNEXION À LA BASE DE DONNÉES
// ====================================================================

require_once __DIR__ . '/config.php';

class Database {
    private static ?PDO $instance = null;

    private function __construct() {}
    private function __clone() {}

    public static function getInstance(): PDO {
        if (self::$instance === null) {
            try {
                $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ];

                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);

                // Auto-migration pour les évolutions de schéma (Google ID, rôle Citoyen, user_id)
                require_once ROOT_PATH . '/database/Migrate.php';
                DatabaseMigrate::run(self::$instance);
            } catch (PDOException $e) {
                die("Erreur critique de connexion à la base de données : " . $e->getMessage());
            }
        }
        return self::$instance;
    }
}
