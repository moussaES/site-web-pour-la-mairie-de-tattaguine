<?php 
$activeTab = 'posts';
require_once APP_PATH . '/Views/layouts/admin_header.php'; 
?>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:25px; flex-wrap:wrap; gap:15px;">
    <div>
        <h1 style="color:var(--admin-sidebar-bg); margin:0;">Gestion des Actualités & Vidéos</h1>
        <p style="color:var(--admin-text-muted); margin:5px 0 0; font-size:0.95rem;">Rédaction, publication et mise à jour des reportages vidéos et articles</p>
    </div>
    <button type="button" onclick="openAddPostModal()" class="btn-action btn-success" style="padding:12px 24px; font-size:0.95rem; border:none; cursor:pointer; display:flex; align-items:center; gap:8px;">
        <span style="font-size:1.1rem; font-weight:bold;">+</span> Nouvelle Publication
    </button>
</div>

<?php if (!empty($flashSuccess)): ?>
    <div class="alert alert-success"><?= Security::sanitize($flashSuccess) ?></div>
<?php endif; ?>

<?php if (!empty($flashError)): ?>
    <div class="alert alert-danger"><?= Security::sanitize($flashError) ?></div>
<?php endif; ?>

<!-- Tableau des Publications -->
<div class="table-card">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Média</th>
                <th>Titre</th>
                <th>Catégorie</th>
                <th>Auteur</th>
                <th>Statut</th>
                <th>Vues</th>
                <th>Date</th>
                <th style="text-align:center;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($posts)): ?>
                <?php foreach ($posts as $post): ?>
                    <tr>
                        <td>
                            <?php if (!empty($post['video_url'])): ?>
                                <span class="badge-status" style="background-color:#E1F5FE; color:#0288D1;">📹 Vidéo</span>
                            <?php elseif (!empty($post['image_path'])): ?>
                                <span class="badge-status" style="background-color:#E8F5E9; color:#2E7D32;">📷 Image</span>
                            <?php else: ?>
                                <span class="badge-status" style="background-color:#EEE; color:#666;">Texte</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong style="color:var(--admin-sidebar-bg);"><?= Security::sanitize($post['title']) ?></strong>
                        </td>
                        <td><?= Security::sanitize($post['category_name']) ?></td>
                        <td><?= Security::sanitize($post['author_name']) ?></td>
                        <td>
                            <span class="badge-status badge-published"><?= ucfirst($post['status']) ?></span>
                        </td>
                        <td><?= $post['views_count'] ?></td>
                        <td><?= date('d/m/Y', strtotime($post['created_at'])) ?></td>
                        <td style="text-align:center; white-space:nowrap;">
                            <!-- Visualiser sur le site -->
                            <a href="<?= BASE_URL ?>/actualites/<?= Security::sanitize($post['slug']) ?>" target="_blank" class="btn-action btn-info" title="Visualiser sur le site" style="padding:8px 12px; margin-right:4px;">
                                👁️
                            </a>

                            <!-- Modifier l'article en Modale Pop-up -->
                            <button type="button" onclick='openEditPostModal(<?= json_encode($post, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' class="btn-action btn-secondary" title="Modifier cette publication" style="padding:8px 12px; margin-right:4px; border:none; cursor:pointer;">
                                ✏️
                            </button>

                            <!-- Supprimer -->
                            <a href="<?= BASE_URL ?>/admin/posts/delete/<?= $post['id'] ?>" class="btn-action btn-danger" title="Supprimer la publication" onclick="return confirmDelete('<?= BASE_URL ?>/admin/posts/delete/<?= $post['id'] ?>', '<?= Security::sanitize($post['title']) ?>', 'Publication / Vidéo');" style="padding:8px 12px;">
                                🗑️
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" style="text-align:center; padding:30px; color:var(--admin-text-muted);">
                        Aucune publication disponible. Cliquez sur <strong>+ Nouvelle Publication</strong> pour ajouter votre premier article.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Fenêtre Modale d'AJOUT d'une Nouvelle Publication / Vidéo -->
<div id="addPostModal" class="modal-overlay" onclick="handleAddPostModalOutsideClick(event)">
    <div class="modal-container" style="max-width:800px;">
        
        <div class="modal-header">
            <h3>+ Nouvelle Publication / Reportage Vidéo</h3>
            <button type="button" class="modal-close" onclick="closeAddPostModal()">&times;</button>
        </div>

        <form action="<?= BASE_URL ?>/admin/posts/create" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

            <div class="modal-body">
                
                <div class="form-field" style="margin-bottom:18px;">
                    <label for="add_title">Titre de la publication *</label>
                    <input type="text" id="add_title" name="title" required placeholder="ex: Lancement du projet de réhabilitation de la voirie">
                </div>

                <div class="form-group-grid">
                    <div class="form-field">
                        <label for="add_category_id">Catégorie *</label>
                        <select id="add_category_id" name="category_id" required>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= Security::sanitize($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-field">
                        <label for="add_status">Statut de publication *</label>
                        <select id="add_status" name="status" required>
                            <option value="published">Publié (Visible immédiatement)</option>
                            <option value="draft">Brouillon (Non visible)</option>
                        </select>
                    </div>
                </div>

                <div class="form-field" style="margin-bottom:18px;">
                    <label for="add_excerpt">Résumé de l'article (Accroche)</label>
                    <textarea id="add_excerpt" name="excerpt" rows="2" placeholder="Brève introduction résumant l'information principale..." style="padding:10px; border:1px solid #CCC; border-radius:6px; font-family:inherit;"></textarea>
                </div>

                <div class="form-field" style="margin-bottom:18px;">
                    <label for="add_content">Contenu détaillé de la publication *</label>
                    <textarea id="add_content" name="content" rows="6" required placeholder="Rédigez ici l'intégralité de l'article..." style="padding:10px; border:1px solid #CCC; border-radius:6px; font-family:inherit;"></textarea>
                </div>

                <div class="form-group-grid">
                    <div class="form-field">
                        <label for="add_image">Image d'illustration (Optionnel)</label>
                        <input type="file" id="add_image" name="image" accept="image/*" style="padding:8px; background:#F8F9FA;">
                    </div>
                    <div class="form-field">
                        <label for="add_video_url">Lien ou URL Vidéo (YouTube / Shorts / Vimeo / MP4)</label>
                        <input type="text" id="add_video_url" name="video_url" placeholder="https://www.youtube.com/watch?v=... ou Shorts">
                    </div>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn-action btn-secondary" onclick="closeAddPostModal()" style="padding:10px 20px;">Annuler</button>
                <button type="submit" class="btn-action btn-success" style="padding:10px 25px; border:none; cursor:pointer;">Publier l'Article</button>
            </div>
        </form>

    </div>
</div>

<!-- Fenêtre Modale de MODIFICATION d'une Publication / Vidéo -->
<div id="editPostModal" class="modal-overlay" onclick="handleEditPostModalOutsideClick(event)">
    <div class="modal-container" style="max-width:800px;">
        
        <div class="modal-header" style="background-color:#1D437A;">
            <h3>✏️ Modifier la Publication / Vidéo</h3>
            <button type="button" class="modal-close" onclick="closeEditPostModal()">&times;</button>
        </div>

        <form id="editPostForm" action="" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

            <div class="modal-body">
                
                <div class="form-field" style="margin-bottom:18px;">
                    <label for="edit_title">Titre de la publication *</label>
                    <input type="text" id="edit_title" name="title" required>
                </div>

                <div class="form-group-grid">
                    <div class="form-field">
                        <label for="edit_category_id">Catégorie *</label>
                        <select id="edit_category_id" name="category_id" required>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= Security::sanitize($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-field">
                        <label for="edit_status">Statut de publication *</label>
                        <select id="edit_status" name="status" required>
                            <option value="published">Publié (Visible immédiatement)</option>
                            <option value="draft">Brouillon (Non visible)</option>
                        </select>
                    </div>
                </div>

                <div class="form-field" style="margin-bottom:18px;">
                    <label for="edit_excerpt">Résumé de l'article (Accroche)</label>
                    <textarea id="edit_excerpt" name="excerpt" rows="2" style="padding:10px; border:1px solid #CCC; border-radius:6px; font-family:inherit;"></textarea>
                </div>

                <div class="form-field" style="margin-bottom:18px;">
                    <label for="edit_content">Contenu détaillé de la publication *</label>
                    <textarea id="edit_content" name="content" rows="6" required style="padding:10px; border:1px solid #CCC; border-radius:6px; font-family:inherit;"></textarea>
                </div>

                <div class="form-group-grid">
                    <div class="form-field">
                        <label for="edit_image">Remplacer l'image d'illustration (Optionnel)</label>
                        <input type="file" id="edit_image" name="image" accept="image/*" style="padding:8px; background:#F8F9FA;">
                    </div>
                    <div class="form-field">
                        <label for="edit_video_url">Lien ou URL Vidéo (YouTube / Vimeo / MP4)</label>
                        <input type="text" id="edit_video_url" name="video_url" placeholder="https://www.youtube.com/watch?v=...">
                    </div>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn-action btn-secondary" onclick="closeEditPostModal()" style="padding:10px 20px;">Annuler</button>
                <button type="submit" class="btn-action btn-success" style="padding:10px 25px; border:none; cursor:pointer;">Enregistrer les Modifications</button>
            </div>
        </form>

    </div>
</div>

<script>
    // --- Modale Ajout Publication ---
    function openAddPostModal() {
        const modal = document.getElementById('addPostModal');
        if (modal) {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
            const firstInput = modal.querySelector('input[name="title"]');
            if (firstInput) firstInput.focus();
        }
    }

    function closeAddPostModal() {
        const modal = document.getElementById('addPostModal');
        if (modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    function handleAddPostModalOutsideClick(event) {
        if (event.target.id === 'addPostModal') {
            closeAddPostModal();
        }
    }

    // --- Modale Modification Publication ---
    function openEditPostModal(post) {
        const modal = document.getElementById('editPostModal');
        const form = document.getElementById('editPostForm');
        
        if (modal && form) {
            form.action = '<?= BASE_URL ?>/admin/posts/update/' + post.id;
            document.getElementById('edit_title').value = post.title || '';
            document.getElementById('edit_category_id').value = post.category_id || 1;
            document.getElementById('edit_status').value = post.status || 'published';
            document.getElementById('edit_excerpt').value = post.excerpt || '';
            document.getElementById('edit_content').value = post.content || '';
            document.getElementById('edit_video_url').value = post.video_url || '';
            
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeEditPostModal() {
        const modal = document.getElementById('editPostModal');
        if (modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    function handleEditPostModalOutsideClick(event) {
        if (event.target.id === 'editPostModal') {
            closeEditPostModal();
        }
    }

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeAddPostModal();
            closeEditPostModal();
        }
    });

    <?php if (!empty($flashError)): ?>
        document.addEventListener('DOMContentLoaded', function() {
            openAddPostModal();
        });
    <?php endif; ?>
</script>

<?php require_once APP_PATH . '/Views/layouts/admin_footer.php'; ?>
