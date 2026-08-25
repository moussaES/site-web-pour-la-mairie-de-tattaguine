<?php
// ====================================================================
// CLASSE DE BASE DES MODÈLES (MVC CORE)
// ====================================================================

require_once __DIR__ . '/../config/database.php';

abstract class Model {
    protected PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }
}
