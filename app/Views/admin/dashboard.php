<?php 
$activeTab = 'dashboard';
$pendingCommentsCount = $pendingComments;
require_once APP_PATH . '/Views/layouts/admin_header.php'; 
?>

<!-- Script CDN Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<h1 style="color:var(--admin-sidebar-bg); margin-bottom:25px;">Tableau de Bord Administrateur</h1>

<?php if (!empty($flashSuccess)): ?>
    <div class="alert alert-success"><?= Security::sanitize($flashSuccess) ?></div>
<?php endif; ?>

<?php if (!empty($flashError)): ?>
    <div class="alert alert-danger"><?= Security::sanitize($flashError) ?></div>
<?php endif; ?>

<!-- Stats Widgets -->
<div class="stats-row">
    <div class="stat-card">
        <div class="stat-icon">📄</div>
        <div>
            <div class="stat-number"><?= count($latestPosts) ?></div>
            <div class="stat-label">Actualités Récentes</div>
        </div>
    </div>
    <div class="stat-card" style="border-left: 4px solid var(--admin-danger);">
        <div class="stat-icon" style="background-color:#FFEBEE; color:var(--admin-danger);">💬</div>
        <div>
            <div class="stat-number" style="color:var(--admin-danger);"><?= $pendingComments ?></div>
            <div class="stat-label">Commentaires à Modérer</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background-color:#E3F2FD; color:#1976D2;">✉️</div>
        <div>
            <div class="stat-number"><?= $unreadMessages ?></div>
            <div class="stat-label">Messages Non Lus</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background-color:#FFF8E1; color:#F57F17;">👥</div>
        <div>
            <div class="stat-number"><?= $totalVisits ?></div>
            <div class="stat-label">Visiteurs Uniques</div>
        </div>
    </div>
</div>

<!-- GRAPHISME DE FRÉQUENTATION / TRAFIC (Avant les tableaux) -->
<div class="table-card" style="padding:25px; margin-bottom:30px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h3 style="margin:0; color:var(--admin-sidebar-bg);">📊 Évolution de la Fréquentation (Visiteurs Uniques)</h3>
        <span style="font-size:0.85rem; color:var(--admin-text-muted);">Mise à jour en temps réel</span>
    </div>
    <div style="height:280px; position:relative;">
        <canvas id="trafficChart"></canvas>
    </div>
</div>

<!-- Section Commentaires en Attente -->
<div class="table-card">
    <div class="table-header">
        <h3 style="margin:0; color:var(--admin-sidebar-bg);">Commentaires en Attente de Modération (Population)</h3>
        <a href="<?= BASE_URL ?>/admin/comments" class="btn-action btn-secondary">Voir tout</a>
    </div>
    <?php if (!empty($recentComments)): ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Auteur</th>
                    <th>Publication</th>
                    <th>Message</th>
                    <th>Date</th>
                    <th style="text-align:center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentComments as $comment): ?>
                    <tr>
                        <td><strong><?= Security::sanitize($comment['author_name']) ?></strong></td>
                        <td><?= Security::sanitize($comment['post_title']) ?></td>
                        <td><?= Security::sanitize(mb_strimwidth($comment['content'], 0, 60, '...')) ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($comment['created_at'])) ?></td>
                        <td style="text-align:center; white-space:nowrap;">
                            <!-- Icône Approuver -->
                            <a href="<?= BASE_URL ?>/admin/comments/approve/<?= $comment['id'] ?>" class="btn-action btn-success" title="Approuver le commentaire" style="padding:8px 12px; margin-right:4px;">✅</a>
                            <!-- Icône Rejeter -->
                            <a href="<?= BASE_URL ?>/admin/comments/reject/<?= $comment['id'] ?>" class="btn-action btn-secondary" title="Rejeter le commentaire" style="padding:8px 12px; margin-right:4px;">❌</a>
                            <!-- Icône Supprimer -->
                            <a href="<?= BASE_URL ?>/admin/comments/delete/<?= $comment['id'] ?>" class="btn-action btn-danger" title="Supprimer le commentaire" onclick="return confirmDelete('<?= BASE_URL ?>/admin/comments/delete/<?= $comment['id'] ?>', 'Commentaire de <?= Security::sanitize($comment['author_name']) ?>', 'Commentaire Citoyen');" style="padding:8px 12px;">🗑️</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="padding:20px; color:var(--admin-text-muted); margin:0;">Aucun commentaire en attente de modération pour le moment.</p>
    <?php endif; ?>
</div>

<!-- Section Publications Récentes -->
<div class="table-card">
    <div class="table-header">
        <h3 style="margin:0; color:var(--admin-sidebar-bg);">Dernières Publications & Vidéos</h3>
        <button type="button" onclick="openAddPostModal()" class="btn-action btn-success" style="border:none; cursor:pointer;">+ Nouvelle Publication</button>
    </div>
    <?php if (!empty($latestPosts)): ?>
        <table class="admin-table">
            <thead>
                <tr>
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
                <?php foreach ($latestPosts as $post): ?>
                    <tr>
                        <td><strong><?= Security::sanitize($post['title']) ?></strong></td>
                        <td><?= Security::sanitize($post['category_name']) ?></td>
                        <td><?= Security::sanitize($post['author_name']) ?></td>
                        <td>
                            <span class="badge-status badge-published"><?= ucfirst($post['status']) ?></span>
                        </td>
                        <td><?= $post['views_count'] ?></td>
                        <td><?= date('d/m/Y', strtotime($post['created_at'])) ?></td>
                        <td style="text-align:center; white-space:nowrap;">
                            <!-- Icône Visualiser -->
                            <a href="<?= BASE_URL ?>/actualites/<?= Security::sanitize($post['slug']) ?>" target="_blank" class="btn-action btn-info" title="Visualiser la publication" style="padding:8px 12px; margin-right:4px;">👁️</a>
                            <!-- Icône Modifier (Pop-up Modal) -->
                            <button type="button" onclick='openEditPostModal(<?= json_encode($post, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' class="btn-action btn-secondary" title="Modifier la publication" style="padding:8px 12px; margin-right:4px; border:none; cursor:pointer;">✏️</button>
                            <!-- Icône Supprimer -->
                            <a href="<?= BASE_URL ?>/admin/posts/delete/<?= $post['id'] ?>" class="btn-action btn-danger" title="Supprimer la publication" onclick="return confirmDelete('<?= BASE_URL ?>/admin/posts/delete/<?= $post['id'] ?>', '<?= Security::sanitize($post['title']) ?>', 'Publication / Vidéo');" style="padding:8px 12px;">🗑️</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- Fenêtre Modale d'AJOUT d'une Publication / Vidéo (Dashboard) -->
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

<!-- Fenêtre Modale de MODIFICATION d'une Publication / Vidéo (Dashboard) -->
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
    // --- Graphique Chart.js ---
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('trafficChart').getContext('2d');
        
        <?php 
            $labels = [];
            $counts = [];
            if (!empty($dailyVisits)) {
                foreach ($dailyVisits as $v) {
                    $labels[] = date('d/m', strtotime($v['visit_date']));
                    $counts[] = (int)$v['visits_count'];
                }
            } else {
                $labels[] = date('d/m');
                $counts[] = $totalVisits;
            }
        ?>

        const labels = <?= json_encode($labels) ?>;
        const dataVisits = <?= json_encode($counts) ?>;

        const gradient = ctx.createLinearGradient(0, 0, 0, 260);
        gradient.addColorStop(0, 'rgba(0, 133, 63, 0.4)');
        gradient.addColorStop(1, 'rgba(0, 133, 63, 0.02)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Visiteurs Uniques (Évolution)',
                    data: dataVisits,
                    borderColor: '#00853F',
                    backgroundColor: gradient,
                    borderWidth: 3.5,
                    fill: true,
                    tension: 0.45,
                    cubicInterpolationMode: 'monotone',
                    pointBackgroundColor: '#102C57',
                    pointBorderColor: '#FFF',
                    pointBorderWidth: 2,
                    pointRadius: 6,
                    pointHoverRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: true, position: 'top' },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: '#102C57',
                        titleColor: '#F7B500',
                        bodyColor: '#FFF',
                        padding: 12,
                        cornerRadius: 8
                    }
                },
                scales: {
                    x: { grid: { display: false } },
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 },
                        grid: { color: 'rgba(0,0,0,0.05)' }
                    }
                }
            }
        });
    });

    // --- Modal Ajout Publication ---
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

    // --- Modal Modifier Publication ---
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
</script>

<?php require_once APP_PATH . '/Views/layouts/admin_footer.php'; ?>
