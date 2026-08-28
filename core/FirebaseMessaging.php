<?php
// ====================================================================
// SERVICE FIREBASEMESSAGING (NOTIFICATION WEB PUSH CITOYENNE VIA FCM)
// ====================================================================

class FirebaseMessaging {

    /**
     * Enregistre un jeton d'appareil FCM pour un utilisateur connecté
     */
    public static function saveToken(PDO $db, int $userId, string $token): bool {
        if (empty($token) || $userId <= 0) return false;

        // Vérifier si le jeton existe déjà pour cet utilisateur
        $checkSql = "SELECT COUNT(*) FROM user_fcm_tokens WHERE user_id = :user_id AND token = :token";
        $stmt = $db->prepare($checkSql);
        $stmt->execute([':user_id' => $userId, ':token' => $token]);

        if ((int)$stmt->fetchColumn() === 0) {
            $insertSql = "INSERT INTO user_fcm_tokens (user_id, token) VALUES (:user_id, :token)";
            $insertStmt = $db->prepare($insertSql);
            return $insertStmt->execute([':user_id' => $userId, ':token' => $token]);
        }

        return true;
    }

    /**
     * Envoie une notification Push Web Firebase (FCM) à tous les appareils enregistrés de l'utilisateur
     */
    public static function sendPushNotification(PDO $db, int $userId, string $title, string $body, string $clickUrl = ''): bool {
        $serverKey = defined('FIREBASE_SERVER_KEY') ? FIREBASE_SERVER_KEY : '';

        if (empty($serverKey) || $serverKey === 'VOTRE_SERVER_KEY_FIREBASE') {
            error_log("Firebase Messaging Note: FIREBASE_SERVER_KEY non configurée dans config/config.php");
            return false;
        }

        // Récupérer les jetons FCM de l'utilisateur
        $sql = "SELECT token FROM user_fcm_tokens WHERE user_id = :user_id";
        $stmt = $db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        $tokens = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($tokens)) {
            error_log("Firebase Messaging Note: Aucun jeton FCM trouvé pour l'utilisateur ID $userId");
            return false;
        }

        $fcmUrl = 'https://fcm.googleapis.com/fcm/send';

        $notificationData = [
            'title' => $title,
            'body'  => $body,
            'icon'  => BASE_URL . '/assets/img/icon-192.png',
            'click_action' => !empty($clickUrl) ? $clickUrl : BASE_URL . '/mon-espace'
        ];

        $payload = [
            'registration_ids' => array_values(array_unique($tokens)),
            'notification'     => $notificationData,
            'data'             => [
                'title' => $title,
                'body'  => $body,
                'url'   => !empty($clickUrl) ? $clickUrl : BASE_URL . '/mon-espace'
            ],
            'priority'         => 'high'
        ];

        $ch = curl_init($fcmUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'Authorization: key=' . trim($serverKey),
                'Content-Type: application/json'
            ],
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError || $httpCode < 200 || $httpCode >= 300) {
            error_log("Firebase Messaging Error (HTTP $httpCode): " . ($curlError ?: $response));
            return false;
        }

        return true;
    }
}
