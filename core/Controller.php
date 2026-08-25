<?php
// ====================================================================
// CLASSE DE BASE DES CONTRÔLEURS (MVC CORE)
// ====================================================================

abstract class Controller {
    /**
     * Rendre une vue avec injection de variables
     */
    protected function render(string $viewPath, array $data = []): void {
        extract($data);
        
        $file = APP_PATH . '/Views/' . $viewPath . '.php';
        if (file_exists($file)) {
            require_once $file;
        } else {
            die("Erreur : La vue '{$viewPath}' n'existe pas dans le dossier Views.");
        }
    }

    /**
     * Redirection d'URL
     */
    protected function redirect(string $url): void {
        if (!str_starts_with($url, 'http')) {
            $url = BASE_URL . $url;
        }
        header("Location: " . $url);
        exit;
    }

    /**
     * Réponse au format JSON
     */
    protected function json(array $data, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
