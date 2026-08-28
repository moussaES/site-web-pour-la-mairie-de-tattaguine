    <footer>
        <div class="footer-container">
            <div class="footer-col">
                <h3>SUNU TATTAGUINE</h3>
                <p>Portail citoyen d'information, de transparence et d'interaction de la Commune de Tattaguine (Programme PATIP-JF).</p>
            </div>
            <div class="footer-col">
                <h3>Raccourcis Utiles</h3>
                <p><a href="<?= BASE_URL ?>/actualites" style="color:#FFF;">• Actualités locales</a></p>
                <p><a href="<?= BASE_URL ?>/documents" style="color:#FFF;">• Arrêtés & Délibérations</a></p>
                <p><a href="<?= BASE_URL ?>/contact" style="color:#FFF;">• Formulaire de contact</a></p>
                <p><a href="<?= BASE_URL ?>/admin/login" style="color:#FFF; opacity:0.8;">• Espace Administration</a></p>
            </div>
            <div class="footer-col">
                <h3>Mairie de Tattaguine</h3>
                <p>Adresse : Hôtel de Ville, Commune de Tattaguine</p>
                <p>Horaires : Lundi - Vendredi : 08h00 - 17h00</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> Sunu Tattaguine — Mairie de Tattaguine (PATIP-JF). Tous droits réservés. | <a href="<?= BASE_URL ?>/admin/login" style="color:#DDD; text-decoration:none;">Accès Agents Municipaux</a></p>
        </div>
    </footer>

    <?php if (!empty($_SESSION['user_id'])): ?>
        <script>
            window.FCM_CONFIG = {
                apiKey: <?= json_encode(defined('FIREBASE_API_KEY') ? FIREBASE_API_KEY : '') ?>,
                projectId: <?= json_encode(defined('FIREBASE_PROJECT_ID') ? FIREBASE_PROJECT_ID : '') ?>,
                senderId: <?= json_encode(defined('FIREBASE_MESSAGING_SENDER_ID') ? FIREBASE_MESSAGING_SENDER_ID : '') ?>,
                appId: <?= json_encode(defined('FIREBASE_APP_ID') ? FIREBASE_APP_ID : '') ?>,
                vapidKey: <?= json_encode(defined('FIREBASE_VAPID_KEY') ? FIREBASE_VAPID_KEY : '') ?>,
                baseUrl: <?= json_encode(BASE_URL) ?>,
                swUrl: <?= json_encode(parse_url(BASE_URL, PHP_URL_PATH) . '/firebase-messaging-sw.js') ?>
            };
        </script>
        <script src="<?= BASE_URL ?>/assets/js/fcm-notifications.js"></script>
    <?php endif; ?>

</body>
</html>
