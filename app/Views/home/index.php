<?php require_once APP_PATH . '/Views/layouts/header.php'; ?>

<section class="hero">
    <h2>Bienvenue sur Sunu Tattaguine</h2>
    <p>Portail citoyen officiel : Information, transparence administrative et interaction directe avec la Mairie de Tattaguine.</p>
    <a href="<?= BASE_URL ?>/actualites" class="btn-primary">Consulter toutes les actualités</a>
</section>

<div class="container">
    <h2 class="section-title">Dernières Actualités & Reportages Vidéos</h2>

    <?php if (!empty($latestPosts)): ?>
        <div class="posts-grid">
            <?php foreach ($latestPosts as $post): ?>
                <div class="post-card">
                    
                    <!-- Aperçu Média (Image ou Bannière Vidéo) -->
                    <?php if (!empty($post['image_path'])): ?>
                        <div style="height:180px; overflow:hidden; background:#000; position:relative;">
                            <img src="<?= BASE_URL ?>/<?= Security::sanitize($post['image_path']) ?>" alt="<?= Security::sanitize($post['title']) ?>" style="width:100%; height:100%; object-fit:cover;">
                            <?php if (!empty($post['video_url'])): ?>
                                <span style="position:absolute; top:10px; right:10px; background:rgba(227, 27, 35, 0.9); color:#FFF; padding:4px 10px; border-radius:4px; font-weight:bold; font-size:0.8rem;">
                                    ▶️ Vidéo
                                </span>
                            <?php endif; ?>
                        </div>
                    <?php elseif (!empty($post['video_url'])): ?>
                        <div style="height:180px; background:linear-gradient(135deg, #102C57, #00853F); display:flex; align-items:center; justify-content:center; color:#FFF; position:relative;">
                            <span style="font-size:3rem;">▶️</span>
                            <span style="position:absolute; bottom:10px; right:10px; background:rgba(0,0,0,0.6); color:#FFF; padding:2px 8px; border-radius:4px; font-size:0.75rem;">Reportage Vidéo</span>
                        </div>
                    <?php endif; ?>

                    <div class="post-card-body">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                            <span class="post-category"><?= Security::sanitize($post['category_name']) ?></span>
                            <small style="color:var(--text-muted);"><?= date('d/m/Y', strtotime($post['created_at'])) ?></small>
                        </div>
                        
                        <h3 class="post-title">
                            <a href="<?= BASE_URL ?>/actualites/<?= Security::sanitize($post['slug']) ?>" style="text-decoration:none; color:inherit;">
                                <?= Security::sanitize($post['title']) ?>
                            </a>
                        </h3>
                        
                        <p class="post-excerpt"><?= Security::sanitize($post['excerpt']) ?></p>

                        <!-- Bouton direct de consultation -->
                        <div style="margin-top:15px; text-align:right;">
                            <a href="<?= BASE_URL ?>/actualites/<?= Security::sanitize($post['slug']) ?>" class="btn-primary" style="padding:8px 16px; font-size:0.85rem; text-decoration:none;">
                                <?= !empty($post['video_url']) ? '▶️ Visionner la vidéo' : 'Lire la suite →' ?>
                            </a>
                        </div>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p style="color:var(--text-muted);">Aucune actualité publiée pour le moment.</p>
    <?php endif; ?>
</div>

<?php require_once APP_PATH . '/Views/layouts/footer.php'; ?>
