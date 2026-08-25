<?php
// ====================================================================
// MODÈLE STAT (SUIVI D'AUDIENCE ET STATISTIQUES EN OPTIMISÉ)
// ====================================================================

require_once ROOT_PATH . '/core/Model.php';

class Stat extends Model {
    /**
     * Enregistrer une visite unique par IP / Date / URL
     */
    public function recordVisit(string $pageUrl): void {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $today = date('Y-m-d');
        
        $visitorHash = hash('sha256', $ip . $userAgent . $today);

        $sql = "INSERT IGNORE INTO visit_stats (visit_date, visitor_hash, page_url) 
                VALUES (:visit_date, :visitor_hash, :page_url)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':visit_date'   => $today,
            ':visitor_hash' => $visitorHash,
            ':page_url'     => $pageUrl
        ]);
    }

    /**
     * Obtenir le nombre total de visiteurs uniques
     */
    public function getTotalVisits(): int {
        $sql = "SELECT COUNT(DISTINCT visitor_hash) AS total FROM visit_stats";
        $stmt = $this->db->query($sql);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Récupérer l'évolution quotidienne du nombre de visiteurs uniques sur les N derniers jours
     */
    public function getDailyVisits(int $days = 7): array {
        $sql = "SELECT visit_date, COUNT(DISTINCT visitor_hash) AS visits_count 
                FROM visit_stats 
                WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL :days DAY)
                GROUP BY visit_date 
                ORDER BY visit_date ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':days', $days, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
