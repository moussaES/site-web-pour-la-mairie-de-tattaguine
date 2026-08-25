<?php 
$activeTab = 'posts';
require_once APP_PATH . '/Views/layouts/admin_header.php'; 
?>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:25px;">
    <h1 style="color:var(--admin-sidebar-bg); margin:0;">Créer une Nouvelle Publication</h1>
    <a href="<?= BASE_URL ?>/admin/posts" class="btn-action btn-secondary">← Retour à la liste</a>
</div>

<div class="table-card" style="padding:30px;">
    <form action="<?= BASE_URL ?>/admin/posts/create" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

        <div style="margin-bottom:20px;">
            <label style="display:block; font-weight:bold; margin-bottom:8px;">Titre de la publication *</label>
            <input type="text" name="title" required style="width:100%; padding:12px; border:1px solid #CCC; border-radius:6px; font-size:1rem;">
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:20px;">
            <div>
                <label style="display:block; font-weight:bold; margin-bottom:8px;">Catégorie *</label>
                <select name="category_id" required style="width:100%; padding:12px; border:1px solid #CCC; border-radius:6px;">
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= Security::sanitize($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label style="display:block; font-weight:bold; margin-bottom:8px;">Statut de publication</label>
                <select name="status" style="width:100%; padding:12px; border:1px solid #CCC; border-radius:6px;">
                    <option value="published">Publié (Visible immédiatement)</option>
                    <option value="draft">Brouillon (Non visible)</option>
                </select>
            </div>
        </div>

        <div style="margin-bottom:20px;">
            <label style="display:block; font-weight:bold; margin-bottom:8px;">Résumé de l'article (Accroche)</label>
            <textarea name="excerpt" rows="2" style="width:100%; padding:12px; border:1px solid #CCC; border-radius:6px; font-family:inherit;"></textarea>
        </div>

        <div style="margin-bottom:20px;">
            <label style="display:block; font-weight:bold; margin-bottom:8px;">Contenu détaillé de la publication *</label>
            <textarea name="content" rows="10" required style="width:100%; padding:12px; border:1px solid #CCC; border-radius:6px; font-family:inherit;"></textarea>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:30px;">
            <div>
                <label style="display:block; font-weight:bold; margin-bottom:8px;">Image d'illustration (JPG, PNG, WebP)</label>
                <input type="file" name="image" accept="image/*" style="width:100%; padding:10px; border:1px solid #CCC; border-radius:6px; background:#FFF;">
            </div>
            <div>
                <label style="display:block; font-weight:bold; margin-bottom:8px;">Lien ou URL Vidéo (YouTube / Vimeo / MP4)</label>
                <input type="text" name="video_url" placeholder="ex: https://www.youtube.com/watch?v=XXXXXX" style="width:100%; padding:12px; border:1px solid #CCC; border-radius:6px;">
                <small style="color:var(--admin-text-muted);">Intégration automatique du lecteur vidéo sur l'article.</small>
            </div>
        </div>

        <button type="submit" class="btn-action btn-success" style="padding:14px 30px; font-size:1rem; cursor:pointer;">
            Publier l'Article
        </button>
    </form>
</div>

<?php require_once APP_PATH . '/Views/layouts/admin_footer.php'; ?>
