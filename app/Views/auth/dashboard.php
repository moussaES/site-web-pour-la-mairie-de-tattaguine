<?php require_once APP_PATH . '/Views/layouts/header.php'; ?>

<div class="container">
    <div style="margin-bottom:30px;">
        <h1 style="color:var(--primary-color); margin-bottom:5px;">Sunu Tattaguine</h1>
        <p style="color:var(--text-muted);">Bienvenue <strong><?= Security::sanitize($user['full_name']) ?></strong> sur votre espace personnel.</p>
    </div>

    <?php if (!empty($flashSuccess)): ?>
        <div style="background-color:#D4EDDA; color:#155724; padding:15px; border-radius:6px; margin-bottom:25px;">
            <?= Security::sanitize($flashSuccess) ?>
        </div>
    <?php endif; ?>

    <!-- Carte Informations Personnelles -->
    <div style="background:#FFF; padding:25px; border-radius:8px; box-shadow:0 2px 10px rgba(0,0,0,0.05); margin-bottom:35px; border-left:5px solid var(--secondary-color);">
        <h3 style="color:var(--primary-color); margin-top:0; margin-bottom:15px;">Informations de Profil</h3>
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:20px;">
            <div>
                <span style="color:var(--text-muted); font-size:0.85rem; display:block;">Nom & Prénom</span>
                <strong><?= Security::sanitize($user['full_name']) ?></strong>
            </div>
            <div>
                <span style="color:var(--text-muted); font-size:0.85rem; display:block;">Adresse E-mail</span>
                <strong><?= Security::sanitize($user['email']) ?></strong>
            </div>
        </div>
    </div>

    <!-- Carte Activation & Test des Notifications Web Push FCM -->
    <div style="background:#FFF; padding:20px 25px; border-radius:8px; box-shadow:0 2px 10px rgba(0,0,0,0.05); margin-bottom:35px; border-left:5px solid #00853F; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px;">
        <div>
            <h4 style="margin:0 0 5px 0; color:#00853F; font-size:1.05rem;">🔔 Notifications Web Push de la Mairie</h4>
            <p style="margin:0; color:#555; font-size:0.9rem;">Recevez en temps réel les réponses et alertes sur votre téléphone ou ordinateur.</p>
        </div>
        <button type="button" onclick="testCitizenWebPushNotification()" style="background:#00853F; color:#FFF; border:none; padding:10px 20px; border-radius:6px; font-weight:bold; cursor:pointer; display:flex; align-items:center; gap:8px;">
            🔔 Activer & Tester mes Notifications
        </button>
    </div>

    <script>
    function testCitizenWebPushNotification() {
        if (!('Notification' in window)) {
            alert('Votre navigateur ne prend pas en charge les notifications Web Push.');
            return;
        }

        Notification.requestPermission().then(function(permission) {
            if (permission === 'granted') {
                if (typeof window.triggerNativeOSNotification === 'function') {
                    window.triggerNativeOSNotification(
                        '🏛️ Mairie de Tattaguine (Sunu Tattaguine)',
                        '🎉 Félicitations ! Votre ordinateur est désormais configuré pour recevoir les notifications système natives de la Mairie en temps réel.',
                        (window.FCM_CONFIG?.baseUrl || '') + '/mon-espace'
                    );
                }

                if (typeof firebase !== 'undefined' && firebase.messaging) {
                    const messaging = firebase.messaging();
                    navigator.serviceWorker.register(window.FCM_CONFIG?.swUrl || '/site%20web%20mairie/public/firebase-messaging-sw.js')
                        .then(function(registration) {
                            return messaging.getToken({
                                vapidKey: window.FCM_CONFIG?.vapidKey,
                                serviceWorkerRegistration: registration
                            });
                        }).then(function(token) {
                            if (token) {
                                fetch((window.FCM_CONFIG?.baseUrl || '') + '/auth/save-fcm-token', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify({ token: token })
                                });
                            }
                        });
                }
            } else if (permission === 'denied') {
                alert('❌ Les notifications sont actuellement bloquées dans votre navigateur.\n\nPour les activer : cliquez sur l\'icône du cadenas à gauche de l\'adresse URL de votre navigateur et autorisez les notifications.');
            }
        });
    }
    </script>

    <!-- Historique des Messages de Contact -->
    <div style="background:#FFF; padding:25px; border-radius:8px; box-shadow:0 2px 10px rgba(0,0,0,0.05); margin-bottom:35px;">
        <h3 style="color:var(--primary-color); margin-top:0; margin-bottom:20px;">Mes Messages Envoyés à la Mairie</h3>
        
        <?php if (!empty($messages)): ?>
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse; text-align:left;">
                    <thead>
                        <tr style="border-bottom:2px solid #EEE; color:var(--primary-color);">
                            <th style="padding:12px;">Date</th>
                            <th style="padding:12px;">Objet</th>
                            <th style="padding:12px;">Message</th>
                            <th style="padding:12px;">Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($messages as $msg): ?>
                            <tr style="border-bottom:1px solid #EEE;">
                                <td style="padding:12px; font-size:0.9rem; color:var(--text-muted);"><?= date('d/m/Y H:i', strtotime($msg['created_at'])) ?></td>
                                <td style="padding:12px; font-weight:bold;"><?= Security::sanitize($msg['subject']) ?></td>
                                <td style="padding:12px; font-size:0.9rem; max-width:320px;">
                                    <?= nl2br(Security::sanitize($msg['message'])) ?>

                                    <?php if (!empty($msg['admin_reply'])): ?>
                                        <div style="margin-top:10px; background:#E8F5E9; border-left:3px solid #2E7D32; padding:10px; border-radius:4px; font-size:0.85rem; color:#1B5E20;">
                                            <strong>🏛️ Réponse Mairie :</strong> <?= nl2br(Security::sanitize($msg['admin_reply'])) ?>
                                            <?php if (!empty($msg['replied_at'])): ?>
                                                <div style="font-size:0.75rem; color:#555; margin-top:4px;"><?= date('d/m/Y H:i', strtotime($msg['replied_at'])) ?></div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td style="padding:12px;">
                                    <?php if (!empty($msg['admin_reply'])): ?>
                                        <span style="background:#D4EDDA; color:#155724; padding:4px 10px; border-radius:4px; font-size:0.8rem; font-weight:bold;">Répondu par la Mairie</span>
                                    <?php elseif ($msg['is_read']): ?>
                                        <span style="background:#D1ECF1; color:#0C5460; padding:4px 10px; border-radius:4px; font-size:0.8rem; font-weight:bold;">Lu par la Mairie</span>
                                    <?php else: ?>
                                        <span style="background:#FFF3CD; color:#856404; padding:4px 10px; border-radius:4px; font-size:0.8rem; font-weight:bold;">En attente de lecture</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p style="color:var(--text-muted); margin:0;">Vous n'avez pas encore envoyé de message au service municipal. <a href="<?= BASE_URL ?>/contact" style="color:var(--secondary-color); font-weight:bold;">Contacter la Mairie</a></p>
        <?php endif; ?>
    </div>

    <!-- Historique des Commentaires -->
    <div style="background:#FFF; padding:25px; border-radius:8px; box-shadow:0 2px 10px rgba(0,0,0,0.05); margin-bottom:40px;">
        <h3 style="color:var(--primary-color); margin-top:0; margin-bottom:20px;">Mes Commentaires & Réactions</h3>

        <?php if (!empty($comments)): ?>
            <div style="display:flex; flex-direction:column; gap:15px;">
                <?php foreach ($comments as $com): ?>
                    <div style="border:1px solid #EEE; border-radius:6px; padding:15px;">
                        <div style="display:flex; justify-content:space-between; margin-bottom:8px; flex-wrap:wrap;">
                            <a href="<?= BASE_URL ?>/actualites/<?= htmlspecialchars($com['post_slug']) ?>#commentaires" style="font-weight:bold; color:var(--primary-color); text-decoration:none;">
                                📰 <?= Security::sanitize($com['post_title']) ?>
                            </a>
                            <small style="color:var(--text-muted);"><?= date('d/m/Y H:i', strtotime($com['created_at'])) ?></small>
                        </div>
                        <p style="margin:0 0 10px 0; color:#444; font-size:0.95rem;"><?= nl2br(Security::sanitize($com['content'])) ?></p>

                        <?php if (!empty($com['admin_response'])): ?>
                            <div style="margin-bottom:12px; background:#E8F5E9; border-left:4px solid #2E7D32; padding:12px 15px; border-radius:6px;">
                                <strong style="color:#1B5E20; font-size:0.9rem; display:flex; align-items:center; gap:6px;">
                                    🏛️ Réponse Officielle de la Mairie (Sunu Tattaguine)
                                </strong>
                                <p style="margin:6px 0 0 0; color:#222; font-size:0.95rem; line-height:1.5;"><?= nl2br(Security::sanitize($com['admin_response'])) ?></p>
                            </div>
                        <?php endif; ?>
                        
                        <div>
                            <?php if ($com['status'] === 'approved'): ?>
                                <span style="background:#D4EDDA; color:#155724; padding:3px 8px; border-radius:4px; font-size:0.75rem; font-weight:bold;">Approuvé & Publié</span>
                            <?php elseif ($com['status'] === 'rejected'): ?>
                                <span style="background:#F8D7DA; color:#721C24; padding:3px 8px; border-radius:4px; font-size:0.75rem; font-weight:bold;">Non retenu</span>
                            <?php else: ?>
                                <span style="background:#FFF3CD; color:#856404; padding:3px 8px; border-radius:4px; font-size:0.75rem; font-weight:bold;">En cours de modération</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p style="color:var(--text-muted); margin:0;">Vous n'avez pas encore posté de commentaire. <a href="<?= BASE_URL ?>/actualites" style="color:var(--secondary-color); font-weight:bold;">Consulter les actualités</a></p>
        <?php endif; ?>
    </div>

</div>

<?php require_once APP_PATH . '/Views/layouts/footer.php'; ?>
