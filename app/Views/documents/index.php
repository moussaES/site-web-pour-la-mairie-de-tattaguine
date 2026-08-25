<?php require_once APP_PATH . '/Views/layouts/header.php'; ?>

<div class="container">
    
    <div style="margin-bottom:30px;">
        <h1 style="color:var(--primary-color); margin-bottom:5px;">Actes Administratifs & Documents Téléchargeables</h1>
        <p style="color:var(--text-muted);">Consultez et téléchargez gratuitement les formulaires et actes officiels de la Mairie de Tattaguine</p>
    </div>

    <!-- Filtres par catégories -->
    <div style="margin-bottom:30px; display:flex; flex-wrap:wrap; gap:10px;">
        <a href="<?= BASE_URL ?>/documents" class="post-category" style="background-color:<?= empty($selectedCategory) ? 'var(--primary-color)' : '#EEE' ?>; color:<?= empty($selectedCategory) ? '#FFF' : '#333' ?>; text-decoration:none; padding:8px 16px; border-radius:20px; font-size:0.9rem;">
            Tous les documents
        </a>
        <?php 
            $cats = ['État Civil', 'Arrêtés Municipaux', 'Délibérations', 'Budgets & Comptes', 'Formulaires'];
            foreach ($cats as $cat): 
        ?>
            <a href="<?= BASE_URL ?>/documents?category=<?= urlencode($cat) ?>" class="post-category" style="background-color:<?= $selectedCategory === $cat ? 'var(--primary-color)' : '#EEE' ?>; color:<?= $selectedCategory === $cat ? '#FFF' : '#333' ?>; text-decoration:none; padding:8px 16px; border-radius:20px; font-size:0.9rem;">
                <?= Security::sanitize($cat) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Liste des Documents -->
    <?php if (!empty($documents)): ?>
        <div style="display:flex; flex-direction:column; gap:15px;">
            <?php foreach ($documents as $doc): ?>
                <div style="background:#FFF; padding:20px; border-radius:8px; box-shadow:0 2px 10px rgba(0,0,0,0.04); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px;">
                    <div>
                        <span class="post-category" style="margin-bottom:5px; font-size:0.75rem;"><?= Security::sanitize($doc['category']) ?></span>
                        <h3 style="color:var(--primary-color); margin:5px 0; font-size:1.1rem;"><?= Security::sanitize($doc['title']) ?></h3>
                        <?php if (!empty($doc['description'])): ?>
                            <p style="color:var(--text-muted); font-size:0.9rem; margin:0;"><?= Security::sanitize($doc['description']) ?></p>
                        <?php endif; ?>
                        <small style="color:var(--text-muted); display:block; margin-top:5px;">Taille : <?= Security::sanitize($doc['file_size']) ?> | Téléchargé <?= $doc['downloads_count'] ?> fois</small>
                    </div>
                    <div>
                        <a href="<?= BASE_URL ?>/documents/download/<?= $doc['id'] ?>" class="btn-primary" style="padding:10px 20px; font-size:0.9rem; text-decoration:none;">
                            📥 Télécharger PDF
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div style="background:#FFF; padding:40px; text-align:center; border-radius:8px; color:var(--text-muted);">
            Aucun document disponible dans cette catégorie pour le moment.
        </div>
    <?php endif; ?>

</div>

<?php require_once APP_PATH . '/Views/layouts/footer.php'; ?>
