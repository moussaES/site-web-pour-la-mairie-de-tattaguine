<?php
// ====================================================================
// POINT D'ENTRÉE UNIQUE - FRONT CONTROLLER (PUBLIC/INDEX.PHP)
// ====================================================================

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Router.php';
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Model.php';
require_once __DIR__ . '/../core/Security.php';

// Autoload rapide pour les modèles et contrôleurs
spl_autoload_register(function ($class) {
    $classFile = str_replace('\\', '/', $class);
    
    $fileApp = APP_PATH . '/' . $classFile . '.php';
    if (file_exists($fileApp)) {
        require_once $fileApp;
        return;
    }

    $fileController = APP_PATH . '/Controllers/' . $classFile . '.php';
    if (file_exists($fileController)) {
        require_once $fileController;
        return;
    }

    $fileCore = ROOT_PATH . '/core/' . $classFile . '.php';
    if (file_exists($fileCore)) {
        require_once $fileCore;
        return;
    }
});

// Instanciation du Routeur
$router = new Router();

// --------------------------------------------------------------------
// ROUTES PUBLIQUES (CLIENT / POPULATION)
// --------------------------------------------------------------------
$router->get('/', 'HomeController@index');
$router->get('/actualites', 'PostController@index');
$router->get('/actualites/{slug}', 'PostController@show');
$router->post('/actualites/{slug}/commentaire', 'CommentController@store');
$router->get('/documents', 'DocumentController@index');
$router->get('/documents/download/{id}', 'DocumentController@download');
$router->get('/contact', 'ContactController@index');
$router->post('/contact', 'ContactController@store');

// --------------------------------------------------------------------
// ROUTES ADMINISTRATION (MAIRIE)
// --------------------------------------------------------------------
$router->get('/admin/login', 'Admin\\AuthController@loginForm');
$router->post('/admin/login', 'Admin\\AuthController@login');
$router->get('/admin/logout', 'Admin\\AuthController@logout');
$router->get('/admin/dashboard', 'Admin\\DashboardController@index');

// Gestion des Actualités
$router->get('/admin/posts', 'Admin\\PostAdminController@index');
$router->get('/admin/posts/create', 'Admin\\PostAdminController@createForm');
$router->post('/admin/posts/create', 'Admin\\PostAdminController@store');
$router->post('/admin/posts/update/{id}', 'Admin\\PostAdminController@update');
$router->get('/admin/posts/delete/{id}', 'Admin\\PostAdminController@delete');

// Modération des Commentaires
$router->get('/admin/comments', 'Admin\\CommentAdminController@index');
$router->get('/admin/comments/approve/{id}', 'Admin\\CommentAdminController@approve');
$router->get('/admin/comments/reject/{id}', 'Admin\\CommentAdminController@reject');
$router->get('/admin/comments/delete/{id}', 'Admin\\CommentAdminController@delete');

// Gestion des Documents
$router->get('/admin/documents', 'Admin\\DocumentAdminController@index');
$router->post('/admin/documents/store', 'Admin\\DocumentAdminController@store');
$router->post('/admin/documents/update/{id}', 'Admin\\DocumentAdminController@update');
$router->get('/admin/documents/delete/{id}', 'Admin\\DocumentAdminController@delete');

// Gestion des Agents (Super-Admin)
$router->get('/admin/users', 'Admin\\UserAdminController@index');
$router->post('/admin/users/store', 'Admin\\UserAdminController@store');
$router->post('/admin/users/update/{id}', 'Admin\\UserAdminController@update');
$router->get('/admin/users/delete/{id}', 'Admin\\UserAdminController@delete');

// Exécution de la requête
$router->dispatch();
