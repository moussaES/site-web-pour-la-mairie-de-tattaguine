<?php
// ====================================================================
// ROUTEUR D'URLS CLEAN MVC (FRONT CONTROLLER ROUTER)
// ====================================================================

class Router {
    private array $routes = [];

    public function get(string $path, string $handler): void {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, string $handler): void {
        $this->addRoute('POST', $path, $handler);
    }

    private function addRoute(string $method, string $path, string $handler): void {
        $this->routes[] = [
            'method'  => $method,
            'path'    => $path,
            'handler' => $handler
        ];
    }

    public function dispatch(): void {
        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        
        // 1. Détermination du chemin relatif (REQUEST_URI en priorité absolue pour Vercel/Cloud)
        $rawPath = $_SERVER['REQUEST_URI'] ?? $_SERVER['PATH_INFO'] ?? '/';
        $path = strtok($rawPath, '?');
        $path = urldecode($path);

        // 2. Supprimer les préfixes d'entrée Vercel / Cloud (/api/index.php, /public/index.php, /index.php, /api, /public)
        $path = preg_replace('#^/(api/index\.php|public/index\.php|index\.php)#i', '', $path);

        // 3. Supprimer le sous-dossier local XAMPP s'il est présent au début du chemin
        if (!empty($_SERVER['SCRIPT_NAME'])) {
            $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
            if ($scriptDir !== '/' && $scriptDir !== '.' && !empty($scriptDir) && $scriptDir !== '/api' && str_starts_with($path, $scriptDir)) {
                $path = substr($path, strlen($scriptDir));
            }
        }

        if (empty($path)) $path = '/';
        if ($path !== '/' && str_ends_with($path, '/')) {
            $path = rtrim($path, '/');
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $requestMethod) continue;

            // Conversion du chemin route en Pattern Regex (ex: /actualites/{slug} -> /actualites/([^/]+))
            $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^/]+)', $route['path']);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $path, $matches)) {
                array_shift($matches); // Supprimer le match complet

                [$controllerName, $methodName] = explode('@', $route['handler']);

                // Vérifier si contrôleur Admin ou normal
                if (str_contains($controllerName, 'Admin\\')) {
                    $controllerFile = APP_PATH . '/Controllers/' . str_replace('\\', '/', $controllerName) . '.php';
                } else {
                    $controllerFile = APP_PATH . '/Controllers/' . $controllerName . '.php';
                }

                if (file_exists($controllerFile)) {
                    require_once $controllerFile;
                    
                    $fullControllerName = str_replace('/', '\\', $controllerName);
                    if (class_exists($fullControllerName)) {
                        $controller = new $fullControllerName();
                        if (method_exists($controller, $methodName)) {
                            call_user_func_array([$controller, $methodName], $matches);
                            return;
                        }
                    }
                }
            }
        }

        // Page non trouvée 404
        http_response_code(404);
        echo "<div style='font-family:sans-serif; text-align:center; padding:50px;'><h1>404 - Page Non Trouvée</h1><p>La page demandée n'existe pas sur le site de la Mairie de Tattaguine.</p><a href='" . BASE_URL . "'>Retourner à l'accueil</a></div>";
    }
}
