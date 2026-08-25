<?php
// ====================================================================
// COMPOSANT DE SÉCURITÉ ET PROTECTION (CSRF, XSS, HASHING, CAPTCHA)
// ====================================================================

class Security {
    /**
     * Génère un jeton CSRF et le stocke en session
     */
    public static function generateCsrfToken(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Vérifie la validité du jeton CSRF
     */
    public static function verifyCsrfToken(?string $token): bool {
        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Assainissement anti-XSS des données affichées
     */
    public static function sanitize(?string $data): string {
        if ($data === null) return '';
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Hachage sécurisé des mots de passe
     */
    public static function hashPassword(string $password): string {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
    }

    /**
     * Vérification du mot de passe
     */
    public static function verifyPassword(string $password, string $hash): bool {
        return password_verify($password, $hash);
    }

    /**
     * Simulation / Validation CAPTCHA simple (Calcul anti-bot)
     */
    public static function generateCaptchaMath(): array {
        $n1 = random_int(1, 9);
        $n2 = random_int(1, 9);
        $_SESSION['captcha_answer'] = $n1 + $n2;
        return [
            'question' => "Combien font {$n1} + {$n2} ?",
            'answer'   => $n1 + $n2
        ];
    }

    public static function verifyCaptchaMath(int $userAnswer): bool {
        if (!isset($_SESSION['captcha_answer'])) return false;
        $valid = (int)$userAnswer === (int)$_SESSION['captcha_answer'];
        unset($_SESSION['captcha_answer']);
        return $valid;
    }
}
