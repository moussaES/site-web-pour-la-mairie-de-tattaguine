<?php require_once APP_PATH . '/Views/layouts/header.php'; ?>

<div class="container">
    <div style="margin-bottom:30px; display:flex; flex-wrap:wrap; justify-content:space-between; align-items:center; gap:20px;">
        <div>
            <h1 style="color:var(--primary-color); margin-bottom:5px;">Actualités & Reportages Vidéos</h1>
            <p style="color:var(--text-muted);">Toutes les publications officielles de la Mairie de Tattaguine</p>
        </div>

        <!-- Moteur de Recherche -->
        <form action="<?= BASE_URL ?>/actualites" method="GET" style="display:flex; gap:10px;">
            <input type="text" name="q" value="<?= Security::sanitize($searchQuery) ?>" placeholder="Rechercher un article ou vidéo..." style="padding:10px 15px; border:1px solid #CCC; border-radius:6px; min-width:250px;">
            <button type="submit" class="btn-primary" style="padding:10px 20px; border:none; cursor:pointer;">Rechercher</button>
        </form>
    </div>

    <!-- Filtre par Catégories -->
    <div style="margin-bottom:30px; display:flex; flex-wrap:wrap; gap:10px;">
        <a href="<?= BASE_URL ?>/actualites" class="post-category" style="background-color:<?= empty($categorySlug) ? 'var(--primary-color)' : '#EEE' ?>; color:<?= empty($categorySlug) ? '#FFF' : '#333' ?>; text-decoration:none; padding:8px 16px; border-radius:20px; font-size:0.9rem;">
            Toutes les catégories
        </a>
        <?php foreach ($categories as $cat): ?>
            <a href="<?= BASE_URL ?>/actualites?category=<?= Security::sanitize($cat['slug']) ?>" class="post-category" style="background-color:<?= $categorySlug === $cat['slug'] ? 'var(--primary-color)' : '#EEE' ?>; color:<?= $categorySlug === $cat['slug'] ? '#FFF' : '#333' ?>; text-decoration:none; padding:8px 16px; border-radius:20px; font-size:0.9rem;">
                <?= Security::sanitize($cat['name']) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Liste des Publications -->
    <?php if (!empty($posts)): ?>
        <div class="posts-grid">
            <?php foreach ($posts as $post): ?>
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
                            <a href="<?= BASE_URL ?>/actualites/<?= Security::sanitize($post['slug']) ?><?= !empty($post['video_url']) ? '#video-player' : '' ?>" class="btn-primary" style="padding:8px 16px; font-size:0.85rem; text-decoration:none;">
                                <?= !empty($post['video_url']) ? '▶️ Visionner la vidéo' : 'Lire la suite →' ?>
                            </a>
                        </div>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p style="text-align:center; padding:50px; color:var(--text-muted);">
            Aucun article ou vidéo ne correspond à votre recherche.
        </p>
    <?php endif; ?>
</div>

<?php require_once APP_PATH . '/Views/layouts/footer.php'; ?>
