<?php 
$activeTab = 'messages';
$unreadMessagesCount = $unreadCount ?? 0;
require_once APP_PATH . '/Views/layouts/admin_header.php'; 
?>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:25px; flex-wrap:wrap; gap:15px;">
    <div>
        <h1 style="color:var(--admin-sidebar-bg); margin:0 0 5px 0;">Messages & Requêtes des Citoyens</h1>
        <p style="color:var(--admin-text-muted); margin:0;">Consultez et traitez les messages transmis par les habitants via le formulaire de contact</p>
    </div>
</div>

<?php if (!empty($flashSuccess)): ?>
    <div class="alert alert-success"><?= Security::sanitize($flashSuccess) ?></div>
<?php endif; ?>

<?php if (!empty($flashError)): ?>
    <div class="alert alert-danger"><?= Security::sanitize($flashError) ?></div>
<?php endif; ?>

<div class="table-card">
    <div class="table-header">
        <h3 style="margin:0; color:var(--admin-sidebar-bg);">Tous les Messages Reçus</h3>
        <?php if (!empty($unreadCount) && $unreadCount > 0): ?>
            <span class="badge-pending" style="font-size:0.9rem; padding:6px 14px;"><?= $unreadCount ?> message(s) non lu(s)</span>
        <?php endif; ?>
    </div>

    <?php if (!empty($messages)): ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Statut</th>
                    <th>Expéditeur</th>
                    <th>E-mail / Téléphone</th>
                    <th>Objet</th>
                    <th>Date</th>
                    <th style="text-align:center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($messages as $msg): ?>
                    <tr style="<?= empty($msg['is_read']) ? 'background-color:#FFFDE7;' : '' ?>">
                        <td>
                            <?php if (empty($msg['is_read'])): ?>
                                <span class="badge-status badge-draft" style="background:#FFF3CD; color:#856404; font-weight:bold;">Nouveau / Non lu</span>
                            <?php else: ?>
                                <span class="badge-status badge-published" style="background:#E8F5E9; color:#2E7D32;">Lu</span>
                            <?php endif; ?>
                        </td>
                        <td><strong><?= Security::sanitize($msg['full_name']) ?></strong></td>
                        <td>
                            <div><?= Security::sanitize($msg['email']) ?></div>
                            <?php if (!empty($msg['phone'])): ?>
                                <?php 
                                    $rawPhone = preg_replace('/[^0-9]/', '', $msg['phone']);
                                    if (strlen($rawPhone) === 9 && (str_starts_with($rawPhone, '7') || str_starts_with($rawPhone, '3'))) {
                                        $rawPhone = '221' . $rawPhone;
                                    }
                                    $waText = urlencode("Bonjour " . $msg['full_name'] . ",\n\nSuite à votre message concernant \"" . $msg['subject'] . "\" sur Sunu Tattaguine...");
                                ?>
                                <div style="margin-top:2px;">
                                    <small style="color:var(--admin-text-muted);">📞 <?= Security::sanitize($msg['phone']) ?></small>
                                    <?php if (!empty($rawPhone)): ?>
                                        <a href="https://wa.me/<?= $rawPhone ?>?text=<?= $waText ?>" target="_blank" style="color:#25D366; text-decoration:none; font-weight:bold; font-size:0.8rem; margin-left:6px;" title="Discuter sur WhatsApp">💬 WhatsApp</a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td><strong><?= Security::sanitize($msg['subject']) ?></strong></td>
                        <td><?= date('d/m/Y H:i', strtotime($msg['created_at'])) ?></td>
                        <td style="text-align:center; white-space:nowrap;">
                            <!-- Bouton Lire / Modal -->
                            <button type="button" onclick='openReadMessageModal(<?= json_encode($msg, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' class="btn-action btn-info" title="Consulter le message" style="padding:8px 12px; margin-right:4px; border:none; cursor:pointer;">
                                👁️ Lire
                            </button>

                            <?php if (empty($msg['is_read'])): ?>
                                <a href="<?= BASE_URL ?>/admin/messages/read/<?= $msg['id'] ?>" class="btn-action btn-success" title="Marquer comme lu" style="padding:8px 12px; margin-right:4px;">
                                    ✓ Marquer lu
                                </a>
                            <?php endif; ?>

                            <a href="<?= BASE_URL ?>/admin/messages/delete/<?= $msg['id'] ?>" class="btn-action btn-danger" title="Supprimer le message" onclick="return confirmDelete('<?= BASE_URL ?>/admin/messages/delete/<?= $msg['id'] ?>', 'Message de <?= Security::sanitize($msg['full_name']) ?>', 'Message Citoyen');" style="padding:8px 12px;">
                                🗑️
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="padding:25px; color:var(--admin-text-muted); text-align:center; margin:0;">Aucun message citoyen n'a été reçu pour le moment.</p>
    <?php endif; ?>
</div>

<!-- Modal Lecture de Message -->
<div id="readMessageModal" class="modal-overlay" onclick="handleReadMessageModalOutsideClick(event)">
    <div class="modal-container" style="max-width:650px;">
        <div class="modal-header" style="background-color:#102C57;">
            <h3 id="modal_msg_subject" style="margin:0;">Objet du Message</h3>
            <button type="button" class="modal-close" onclick="closeReadMessageModal()">&times;</button>
        </div>
        <div class="modal-body" style="padding:25px;">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px; background:#F8F9FA; padding:15px; border-radius:6px; margin-bottom:20px; border-left:4px solid var(--secondary-color);">
                <div>
                    <span style="font-size:0.8rem; color:var(--admin-text-muted); display:block;">Expéditeur</span>
                    <strong id="modal_msg_sender"></strong>
                </div>
                <div>
                    <span style="font-size:0.8rem; color:var(--admin-text-muted); display:block;">Adresse E-mail</span>
                    <a id="modal_msg_email_link" href="#" style="color:var(--admin-sidebar-bg); font-weight:bold;"></a>
                </div>
                <div>
                    <span style="font-size:0.8rem; color:var(--admin-text-muted); display:block;">Téléphone</span>
                    <strong id="modal_msg_phone"></strong>
                </div>
                <div>
                    <span style="font-size:0.8rem; color:var(--admin-text-muted); display:block;">Date d'envoi</span>
                    <strong id="modal_msg_date"></strong>
                </div>
            </div>

            <div style="margin-bottom:20px;">
                <label style="font-weight:bold; color:var(--admin-sidebar-bg); display:block; margin-bottom:8px;">Contenu du Message :</label>
                <div id="modal_msg_content" style="background:#FFF; border:1px solid #DDD; padding:15px; border-radius:6px; line-height:1.6; white-space:pre-wrap; min-height:100px;"></div>
            </div>

            <!-- Formulaire de Réponse Directe sur le Site (Espace Citoyen) -->
            <div style="background:#E8F5E9; border-left:4px solid #2E7D32; padding:18px; border-radius:6px; margin-top:20px;">
                <h4 style="margin:0 0 10px 0; color:#1B5E20; display:flex; align-items:center; gap:8px;">
                    🏛️ Répondre directement sur le site (Visible dans l'espace du citoyen)
                </h4>
                <form id="modal_reply_form" action="" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                    <div style="margin-bottom:12px;">
                        <textarea id="modal_admin_reply" name="admin_reply" rows="3" required placeholder="Rédigez ici votre réponse officielle..." style="width:100%; padding:10px; border:1px solid #A5D6A7; border-radius:6px; font-family:inherit; font-size:0.95rem;"></textarea>
                    </div>
                    <button type="submit" class="btn-action btn-success" style="padding:8px 18px; border:none; cursor:pointer; font-weight:bold;">
                        💾 Enregistrer & Transmettre au citoyen
                    </button>
                </form>
            </div>
        </div>
        <div class="modal-footer" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
            <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
                <!-- Réponse par E-mail via Gmail Web -->
                <a id="modal_msg_gmail_btn" href="#" target="_blank" class="btn-action" style="padding:10px 16px; background-color:#EA4335; color:#FFF; border-radius:6px; text-decoration:none; font-weight:bold;">✉️ Répondre via Gmail</a>
                <a id="modal_msg_whatsapp_btn" href="#" target="_blank" class="btn-action" style="padding:10px 16px; background-color:#25D366; color:#FFF; border-radius:6px; text-decoration:none; font-weight:bold; display:none;">💬 Répondre sur WhatsApp</a>
            </div>
            <button type="button" class="btn-action btn-secondary" onclick="closeReadMessageModal()" style="padding:10px 20px;">Fermer</button>
        </div>
    </div>
</div>

<script>
    function openReadMessageModal(msg) {
        const modal = document.getElementById('readMessageModal');
        if (modal) {
            document.getElementById('modal_msg_subject').innerText = msg.subject || 'Message Citoyen';
            document.getElementById('modal_msg_sender').innerText = msg.full_name || 'Citoyen';
            
            const emailLink = document.getElementById('modal_msg_email_link');
            emailLink.innerText = msg.email || '';
            emailLink.href = 'https://mail.google.com/mail/?view=cm&fs=1&to=' + encodeURIComponent(msg.email || '') + '&su=RE: ' + encodeURIComponent(msg.subject || '');
            emailLink.target = '_blank';

            document.getElementById('modal_msg_phone').innerText = msg.phone || 'Non renseigné';
            document.getElementById('modal_msg_date').innerText = msg.created_at || '';
            document.getElementById('modal_msg_content').innerText = msg.message || '';
            
            // Gmail Webmail lien direct
            document.getElementById('modal_msg_gmail_btn').href = 'https://mail.google.com/mail/?view=cm&fs=1&to=' + encodeURIComponent(msg.email || '') + '&su=RE: ' + encodeURIComponent(msg.subject || '');

            // Formulaire de réponse sur le site
            const replyForm = document.getElementById('modal_reply_form');
            if (replyForm) {
                replyForm.action = '<?= BASE_URL ?>/admin/messages/reply/' + msg.id;
            }
            document.getElementById('modal_admin_reply').value = msg.admin_reply || '';

            // Bouton WhatsApp
            const waBtn = document.getElementById('modal_msg_whatsapp_btn');
            if (msg.phone && msg.phone.trim() !== '') {
                let cleanPhone = msg.phone.replace(/[^0-9]/g, '');
                if (cleanPhone.length === 9 && (cleanPhone.startsWith('7') || cleanPhone.startsWith('3'))) {
                    cleanPhone = '221' + cleanPhone;
                }
                if (cleanPhone.length >= 8) {
                    const text = encodeURIComponent('Bonjour ' + (msg.full_name || '') + ',\n\nSuite à votre message concernant "' + (msg.subject || '') + '" sur Sunu Tattaguine...');
                    waBtn.href = 'https://wa.me/' + cleanPhone + '?text=' + text;
                    waBtn.style.display = 'inline-flex';
                } else {
                    waBtn.style.display = 'none';
                }
            } else {
                waBtn.style.display = 'none';
            }

            modal.classList.add('active');
            document.body.style.overflow = 'hidden';

            // Si le message est non lu, marquer comme lu en tâche de fond
            if (!parseInt(msg.is_read)) {
                fetch('<?= BASE_URL ?>/admin/messages/read/' + msg.id);
            }
        }
    }

    function closeReadMessageModal() {
        const modal = document.getElementById('readMessageModal');
        if (modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    function handleReadMessageModalOutsideClick(event) {
        if (event.target.id === 'readMessageModal') {
            closeReadMessageModal();
        }
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeReadMessageModal();
    });
</script>

<?php require_once APP_PATH . '/Views/layouts/admin_footer.php'; ?>
