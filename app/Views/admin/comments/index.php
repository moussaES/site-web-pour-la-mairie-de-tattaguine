<?php 
$activeTab = 'comments';
require_once APP_PATH . '/Views/layouts/admin_header.php'; 
?>

<h1 style="color:var(--admin-sidebar-bg); margin-bottom:20px;">Espace de Modération des Commentaires Citoyens</h1>

<?php if (!empty($flashSuccess)): ?>
    <div class="alert alert-success"><?= Security::sanitize($flashSuccess) ?></div>
<?php endif; ?>

<?php if (!empty($flashError)): ?>
    <div class="alert alert-danger"><?= Security::sanitize($flashError) ?></div>
<?php endif; ?>

<div style="margin-bottom:20px; display:flex; gap:10px;">
    <a href="<?= BASE_URL ?>/admin/comments?status=pending" class="btn-action <?= $statusFilter === 'pending' ? 'btn-danger' : 'btn-secondary' ?>">
        En attente de modération
    </a>
    <a href="<?= BASE_URL ?>/admin/comments?status=approved" class="btn-action <?= $statusFilter === 'approved' ? 'btn-success' : 'btn-secondary' ?>">
        Commentaires Approuvés
    </a>
    <a href="<?= BASE_URL ?>/admin/comments?status=rejected" class="btn-action <?= $statusFilter === 'rejected' ? 'btn-secondary' : 'btn-secondary' ?>">
        Commentaires Rejetés
    </a>
</div>

<div class="table-card">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Auteur</th>
                <th>Publication</th>
                <th>Commentaire Citoyen</th>
                <th>Réponse Mairie</th>
                <th>Date</th>
                <th>Statut</th>
                <th style="text-align:center;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($comments)): ?>
                <?php foreach ($comments as $comment): ?>
                    <tr>
                        <td>
                            <strong><?= Security::sanitize($comment['author_name']) ?></strong>
                            <div style="font-size:0.8rem; color:var(--admin-text-muted);"><?= Security::sanitize($comment['author_email'] ?? '') ?></div>
                        </td>
                        <td>
                            <a href="<?= BASE_URL ?>/actualites/<?= Security::sanitize($comment['post_slug']) ?>#commentaires" target="_blank" style="color:var(--admin-sidebar-bg); font-weight:600;">
                                <?= Security::sanitize($comment['post_title']) ?>
                            </a>
                        </td>
                        <td style="max-width:260px;"><?= nl2br(Security::sanitize($comment['content'])) ?></td>
                        <td style="max-width:240px;">
                            <?php if (!empty($comment['admin_response'])): ?>
                                <div style="background:#E8F5E9; border-left:3px solid #2E7D32; padding:8px 10px; border-radius:4px; font-size:0.85rem; color:#1B5E20;">
                                    <strong>🏛️ Mairie :</strong> <?= Security::sanitize(mb_strimwidth($comment['admin_response'], 0, 70, '...')) ?>
                                </div>
                            <?php else: ?>
                                <span style="color:var(--admin-text-muted); font-size:0.85rem; italic;">Aucune réponse</span>
                            <?php endif; ?>
                        </td>
                        <td><?= date('d/m/Y H:i', strtotime($comment['created_at'])) ?></td>
                        <td>
                            <?php if ($comment['status'] === 'approved'): ?>
                                <span class="badge-status badge-published">Approuvé</span>
                            <?php elseif ($comment['status'] === 'rejected'): ?>
                                <span class="badge-status badge-draft" style="background:#FFEBEE; color:#C62828;">Rejeté</span>
                            <?php else: ?>
                                <span class="badge-status badge-pending">En attente</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align:center; white-space:nowrap;">
                            <!-- Bouton Répondre / Publier une réponse -->
                            <button type="button" onclick='openReplyCommentModal(<?= json_encode($comment, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' class="btn-action btn-info" title="Répondre au commentaire sur le site" style="padding:8px 12px; margin-right:4px; border:none; cursor:pointer;">
                                💬 Répondre
                            </button>

                            <?php if ($comment['status'] !== 'approved'): ?>
                                <a href="<?= BASE_URL ?>/admin/comments/approve/<?= $comment['id'] ?>" class="btn-action btn-success" title="Approuver le commentaire sans réponse" style="padding:8px 12px; margin-right:4px;">✅</a>
                            <?php endif; ?>
                            <?php if ($comment['status'] !== 'rejected'): ?>
                                <a href="<?= BASE_URL ?>/admin/comments/reject/<?= $comment['id'] ?>" class="btn-action btn-secondary" title="Rejeter le commentaire" style="padding:8px 12px; margin-right:4px;">❌</a>
                            <?php endif; ?>
                            <a href="<?= BASE_URL ?>/admin/comments/delete/<?= $comment['id'] ?>" class="btn-action btn-danger" title="Supprimer le commentaire" onclick="return confirmDelete('<?= BASE_URL ?>/admin/comments/delete/<?= $comment['id'] ?>', 'Commentaire de <?= Security::sanitize($comment['author_name']) ?>', 'Commentaire Citoyen');" style="padding:8px 12px;">🗑️</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" style="text-align:center; padding:30px; color:var(--admin-text-muted);">
                        Aucun commentaire sous ce statut.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal Réponse Officielle de la Mairie sur un Commentaire -->
<div id="replyCommentModal" class="modal-overlay" onclick="handleReplyCommentModalOutsideClick(event)">
    <div class="modal-container" style="max-width:600px;">
        <div class="modal-header" style="background-color:#102C57;">
            <h3>💬 Réponse Officielle au Commentaire</h3>
            <button type="button" class="modal-close" onclick="closeReplyCommentModal()">&times;</button>
        </div>
        <form id="replyCommentForm" action="" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

            <div class="modal-body" style="padding:25px;">
                <div style="background:#F8F9FA; padding:15px; border-radius:6px; margin-bottom:20px; border-left:4px solid var(--admin-sidebar-bg);">
                    <span style="font-size:0.8rem; color:var(--admin-text-muted); display:block;">Commentaire de <strong id="modal_comment_author"></strong> sur <em id="modal_comment_post"></em> :</span>
                    <p id="modal_comment_content" style="margin:8px 0 0 0; color:#333; font-style:italic; font-size:0.95rem;"></p>
                </div>

                <div class="form-field">
                    <label for="admin_response" style="font-weight:bold; color:var(--admin-sidebar-bg); display:block; margin-bottom:8px;">
                        🏛️ Votre Réponse Officielle (Sunu Tattaguine) *
                    </label>
                    <textarea id="admin_response" name="admin_response" rows="5" required placeholder="Rédigez la réponse officielle de la Mairie qui apparaîtra publiquement sous le commentaire du citoyen..." style="width:100%; padding:12px; border:1px solid #CCC; border-radius:6px; font-family:inherit; font-size:0.95rem;"></textarea>
                    <small style="color:var(--admin-text-muted); display:block; margin-top:6px;">Remarque : L'enregistrement de votre réponse approuvera et publiera automatiquement le commentaire s'il était en attente.</small>
                </div>
            </div>

            <div class="modal-footer" style="display:flex; justify-content:space-between; align-items:center;">
                <button type="button" class="btn-action btn-secondary" onclick="closeReplyCommentModal()" style="padding:10px 20px;">Annuler</button>
                <button type="submit" class="btn-action btn-success" style="padding:10px 22px; border:none; cursor:pointer; font-weight:bold;">Publier la Réponse</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openReplyCommentModal(comment) {
        const modal = document.getElementById('replyCommentModal');
        const form = document.getElementById('replyCommentForm');
        if (modal && form) {
            form.action = '<?= BASE_URL ?>/admin/comments/reply/' + comment.id;
            document.getElementById('modal_comment_author').innerText = comment.author_name || 'Citoyen';
            document.getElementById('modal_comment_post').innerText = comment.post_title || '';
            document.getElementById('modal_comment_content').innerText = comment.content || '';
            document.getElementById('admin_response').value = comment.admin_response || '';

            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
            document.getElementById('admin_response').focus();
        }
    }

    function closeReplyCommentModal() {
        const modal = document.getElementById('replyCommentModal');
        if (modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    function handleReplyCommentModalOutsideClick(event) {
        if (event.target.id === 'replyCommentModal') {
            closeReplyCommentModal();
        }
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeReplyCommentModal();
    });
</script>

<?php require_once APP_PATH . '/Views/layouts/admin_footer.php'; ?>
