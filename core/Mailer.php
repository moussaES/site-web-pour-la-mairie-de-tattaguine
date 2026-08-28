<?php
// ====================================================================
// CLASSE MAILER (SERVICE D'ENVOI D'EMAILS VIA L'API RESEND.COM)
// ====================================================================

class Mailer {
    /**
     * Envoie un e-mail via l'API REST de Resend.com
     *
     * @param string $to Adresse e-mail du destinataire
     * @param string $subject Sujet du message
     * @param string $htmlContent Contenu HTML du message
     * @return bool True si envoyé avec succès, False sinon
     */
    public static function send(string $to, string $subject, string $htmlContent): bool {
        $apiKey = defined('RESEND_API_KEY') ? RESEND_API_KEY : '';
        $from   = defined('RESEND_FROM_EMAIL') ? RESEND_FROM_EMAIL : 'Sunu Tattaguine <onboarding@resend.dev>';

        if (empty($to) || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            error_log("Resend Mailer Warning: Adresse destinataire invalide ($to)");
            return false;
        }

        if (empty($apiKey) || $apiKey === 'VOTRE_CLE_API_RESEND') {
            error_log("Resend Mailer Note: Clé API Resend non configurée dans config/config.php");
            return false;
        }

        $url = 'https://api.resend.com/emails';
        $payload = [
            'from'    => $from,
            'to'      => [$to],
            'subject' => $subject,
            'html'    => $htmlContent
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . trim($apiKey),
                'Content-Type: application/json'
            ],
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_SSL_VERIFYPEER => false // Pour compatibilité environnements de dév locaux
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError || $httpCode < 200 || $httpCode >= 300) {
            error_log("Resend Email Error (HTTP $httpCode): " . ($curlError ?: $response));
            return false;
        }

        return true;
    }
}
