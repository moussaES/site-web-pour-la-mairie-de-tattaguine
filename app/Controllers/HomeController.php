<?php
// ====================================================================
// CONTRÔLEUR HOMECONTROLLER (PAGE D'ACCUEIL PUBLIQUE)
// ====================================================================

require_once ROOT_PATH . '/core/Controller.php';
require_once APP_PATH . '/Models/Post.php';
require_once APP_PATH . '/Models/Stat.php';

class HomeController extends Controller {
    public function index(): void {
        // Enregistrer la visite
        $statModel = new Stat();
        $statModel->recordVisit('/');

        // Charger les 6 dernières actualités
        $postModel = new Post();
        $latestPosts = $postModel->getLatest(6);

        $data = [
            'pageTitle'   => 'Accueil — Sunu Tattaguine',
            'latestPosts' => $latestPosts
        ];

        $this->render('home/index', $data);
    }
}
