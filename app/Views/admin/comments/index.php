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
                <th>E-mail</th>
                <th>Publication</th>
                <th>Commentaire</th>
                <th>Date</th>
                <th>Statut</th>
                <th style="text-align:center;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($comments)): ?>
                <?php foreach ($comments as $comment): ?>
                    <tr>
                        <td><strong><?= Security::sanitize($comment['author_name']) ?></strong></td>
                        <td><?= Security::sanitize($comment['author_email'] ?? 'Non fourni') ?></td>
                        <td>
                            <a href="<?= BASE_URL ?>/actualites/<?= Security::sanitize($comment['post_slug']) ?>" target="_blank" style="color:var(--admin-sidebar-bg); font-weight:600;">
                                <?= Security::sanitize($comment['post_title']) ?>
                            </a>
                        </td>
                        <td style="max-width:300px;"><?= nl2br(Security::sanitize($comment['content'])) ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($comment['created_at'])) ?></td>
                        <td>
                            <?php if ($comment['status'] === 'approved'): ?>
                                <span class="badge-status badge-approved">Approuvé</span>
                            <?php elseif ($comment['status'] === 'rejected'): ?>
                                <span class="badge-status badge-rejected">Rejeté</span>
                            <?php else: ?>
                                <span class="badge-status badge-pending">En attente</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align:center; white-space:nowrap;">
                            <?php if ($comment['status'] !== 'approved'): ?>
                                <a href="<?= BASE_URL ?>/admin/comments/approve/<?= $comment['id'] ?>" class="btn-action btn-success" title="Approuver le commentaire" style="padding:8px 12px; margin-right:4px;">✅</a>
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

<?php require_once APP_PATH . '/Views/layouts/admin_footer.php'; ?>
