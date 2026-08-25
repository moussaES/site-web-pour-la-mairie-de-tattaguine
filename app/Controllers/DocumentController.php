<?php
// ====================================================================
// CONTRÔLEUR DOCUMENTCONTROLLER (DOCUMENTS ADMINISTRATIFS)
// ====================================================================

require_once ROOT_PATH . '/core/Controller.php';
require_once APP_PATH . '/Models/Document.php';
require_once APP_PATH . '/Models/Stat.php';

class DocumentController extends Controller {
    public function index(): void {
        $statModel = new Stat();
        $statModel->recordVisit('/documents');

        $selectedCategory = Security::sanitize($_GET['category'] ?? '');

        $docModel = new Document();
        $documents = $docModel->getAll(!empty($selectedCategory) ? $selectedCategory : null);

        $data = [
            'pageTitle'        => 'Actes Administratifs & Formulaires — Commune de Tattaguine',
            'currentPage'      => 'documents',
            'documents'        => $documents,
            'selectedCategory' => $selectedCategory
        ];

        $this->render('documents/index', $data);
    }

    /**
     * Télécharger un document et incrémenter le compteur de téléchargements
     */
    public function download(int $id): void {
        $docModel = new Document();
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM documents WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $doc = $stmt->fetch();

        if (!$doc) {
            $this->redirect('/documents');
            return;
        }

        // Incrémenter le compteur de téléchargements en BDD
        $docModel->incrementDownloads($id);

        $filePath = ROOT_PATH . '/public/' . $doc['file_path'];

        if (file_exists($filePath)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . basename($doc['file_path']) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filePath));
            readfile($filePath);
            exit;
        } else {
            // Si le fichier physique est externe, rediriger vers l'URL
            $this->redirect('/' . $doc['file_path']);
        }
    }
}
