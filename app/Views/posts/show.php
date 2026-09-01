<?php require_once APP_PATH . '/Views/layouts/header.php'; ?>

<div class="container" style="max-width:900px;">
    
    <div style="margin-bottom:20px;">
        <a href="<?= BASE_URL ?>/actualites" style="text-decoration:none; color:var(--secondary-color); font-weight:bold;">← Retour aux actualités</a>
    </div>

    <span class="post-category" style="margin-bottom:15px; display:inline-block; font-size:0.9rem; padding:6px 14px;"><?= Security::sanitize($post['category_name']) ?></span>

    <h1 style="font-size:2.2rem; color:var(--primary-color); margin-bottom:15px; line-height:1.3;">
        <?= Security::sanitize($post['title']) ?>
    </h1>

    <div style="font-size:0.9rem; color:var(--text-muted); border-bottom:1px solid #DDD; padding-bottom:15px; margin-bottom:25px; display:flex; justify-content:space-between;">
        <span>Publié par <strong><?= Security::sanitize($post['author_name']) ?></strong> le <?= date('d/m/Y à H:i', strtotime($post['created_at'])) ?></span>
        <span>👁️ <?= $post['views_count'] ?> consultations</span>
    </div>

    <!-- Lecteur Vidéo si présent -->
    <?php if (!empty($post['video_url'])): ?>
        <div id="video-player" style="margin-bottom:30px; background:#000; border-radius:8px; overflow:hidden;">
            <?php 
                $vUrl = trim($post['video_url']);
                if (str_contains($vUrl, 'youtube.com') || str_contains($vUrl, 'youtu.be')) {
                    preg_match('/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|shorts\/|watch\?v=|watch\?.+&v=))([\w-]{11})/', $vUrl, $matches);
                    $youtubeId = $matches[1] ?? '';
                    if ($youtubeId) {
                        echo '<div style="position:relative; padding-bottom:56.25%; height:0;">';
                        echo '<iframe src="https://www.youtube.com/embed/' . $youtubeId . '" frameborder="0" allowfullscreen style="position:absolute; top:0; left:0; width:100%; height:100%;"></iframe>';
                        echo '</div>';
                    }
                } elseif (str_contains($vUrl, 'vimeo.com')) {
                    preg_match('/vimeo\.com\/(?:video\/)?(\d+)/', $vUrl, $matches);
                    $vimeoId = $matches[1] ?? '';
                    if ($vimeoId) {
                        echo '<div style="position:relative; padding-bottom:56.25%; height:0;">';
                        echo '<iframe src="https://player.vimeo.com/video/' . $vimeoId . '" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen style="position:absolute; top:0; left:0; width:100%; height:100%;"></iframe>';
                        echo '</div>';
                    }
                } else {
                    $cleanVUrl = ltrim($vUrl, '/');
                    if (str_starts_with($cleanVUrl, 'public/')) {
                        $cleanVUrl = substr($cleanVUrl, 7);
                    }
                    $videoSrc = (str_starts_with($vUrl, 'http://') || str_starts_with($vUrl, 'https://'))
                        ? $vUrl
                        : BASE_URL . '/' . ltrim($cleanVUrl, '/');
                    echo '<video controls style="width:100%; max-height:450px;">';
                    echo '<source src="' . Security::sanitize($videoSrc) . '">';
                    echo 'Votre navigateur ne supporte pas la lecture de cette vidéo.';
                    echo '</video>';
                }
            ?>
        </div>
    <?php endif; ?>

    <!-- Image si présente -->
    <?php if (!empty($post['image_path']) && empty($post['video_url'])): ?>
        <div style="margin-bottom:30px; border-radius:8px; overflow:hidden;">
            <img src="<?= BASE_URL ?>/<?= Security::sanitize($post['image_path']) ?>" alt="<?= Security::sanitize($post['title']) ?>" style="width:100%; max-height:500px; object-fit:cover;">
        </div>
    <?php endif; ?>

    <!-- Contenu de la publication -->
    <div style="font-size:1.1rem; line-height:1.8; color:#333; margin-bottom:50px;">
        <?= nl2br($post['content']) ?>
    </div>

    <hr style="border:0; border-top:1px solid #DDD; margin-bottom:40px;">

    <!-- Section Espace Commentaires Citoyens -->
    <h2 style="color:var(--primary-color); margin-bottom:20px;">Commentaires & Réactions des Citoyens</h2>

    <?php if (!empty($flashSuccess)): ?>
        <div style="background-color:#D4EDDA; color:#155724; padding:15px; border-radius:6px; margin-bottom:20px;">
            <?= Security::sanitize($flashSuccess) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($flashError)): ?>
        <div style="background-color:#F8D7DA; color:#721C24; padding:15px; border-radius:6px; margin-bottom:20px;">
            <?= Security::sanitize($flashError) ?>
        </div>
    <?php endif; ?>

    <!-- Formulaire de dépot de commentaire -->
    <div id="commentaires" style="background-color:#FFF; padding:25px; border-radius:8px; box-shadow:0 2px 10px rgba(0,0,0,0.05); margin-bottom:40px;">
        <h3 style="margin-top:0; color:var(--primary-color); margin-bottom:15px;">Laisser un commentaire</h3>
        
        <?php if (empty($_SESSION['user_id'])): ?>
            <?php $articleRedirect = urlencode('/actualites/' . Security::sanitize($post['slug']) . '#commentaires'); ?>
            <div style="background-color:#EBF3FA; border-left:4px solid var(--primary-color); padding:20px; border-radius:6px; text-align:center;">
                <p style="margin:0 0 15px 0; font-size:1.05rem; color:var(--primary-color); font-weight:600;">
                    🔒 Pour poster un commentaire et réagir à cet article, vous devez être connecté.
                </p>
                <div style="display:flex; justify-content:center; gap:15px; flex-wrap:wrap; align-items:center;">
                    <a href="<?= BASE_URL ?>/login?redirect=<?= $articleRedirect ?>" class="btn-login" style="padding:10px 22px; font-size:0.95rem; text-decoration:none;">Se connecter</a>
                    <a href="<?= BASE_URL ?>/register?redirect=<?= $articleRedirect ?>" class="btn-register" style="padding:10px 22px; font-size:0.95rem; text-decoration:none;">Créer un compte</a>
                </div>
            </div>
        <?php else: ?>
            <form action="<?= BASE_URL ?>/actualites/<?= Security::sanitize($post['slug']) ?>/commentaire" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px; margin-bottom:15px;">
                    <div>
                        <label style="display:block; font-weight:bold; margin-bottom:5px; font-size:0.9rem;">Expéditeur connecté</label>
                        <input type="text" value="<?= Security::sanitize($_SESSION['full_name'] ?? '') ?>" disabled style="width:100%; padding:10px; border:1px solid #CCC; border-radius:6px; background:#F4F6F9; color:#555;">
                    </div>
                    <div>
                        <label style="display:block; font-weight:bold; margin-bottom:5px; font-size:0.9rem;">Adresse E-mail</label>
                        <input type="email" value="<?= Security::sanitize($_SESSION['email'] ?? '') ?>" disabled style="width:100%; padding:10px; border:1px solid #CCC; border-radius:6px; background:#F4F6F9; color:#555;">
                    </div>
                </div>

                <div style="margin-bottom:15px;">
                    <label style="display:block; font-weight:bold; margin-bottom:5px;">Votre Commentaire *</label>
                    <textarea name="content" rows="4" required placeholder="Exprimez votre avis de manière courtoise..." style="width:100%; padding:10px; border:1px solid #CCC; border-radius:6px; font-family:inherit;"></textarea>
                </div>

                <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px;">
                    <div>
                        <label style="font-weight:bold; margin-right:10px; color:var(--primary-color);">Test anti-bot : <?= $captchaQuestion ?> *</label>
                        <input type="number" name="captcha_answer" required style="width:80px; padding:8px; border:1px solid #CCC; border-radius:6px;">
                    </div>
                    <button type="submit" class="btn-primary" style="padding:10px 25px; border:none; cursor:pointer;">Soumettre le commentaire</button>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <!-- Liste des commentaires approuvés -->
    <?php if (!empty($comments)): ?>
        <div style="display:flex; flex-direction:column; gap:20px;">
            <?php foreach ($comments as $comment): ?>
                <div style="background-color:#FFF; padding:20px; border-radius:8px; border-left:4px solid var(--secondary-color); box-shadow:0 2px 5px rgba(0,0,0,0.03);">
                    <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                        <strong style="color:var(--primary-color);"><?= Security::sanitize($comment['author_name']) ?></strong>
                        <small style="color:var(--text-muted);"><?= date('d/m/Y à H:i', strtotime($comment['created_at'])) ?></small>
                    </div>
                    <p style="margin:0; color:#444;"><?= nl2br(Security::sanitize($comment['content'])) ?></p>

                    <?php if (!empty($comment['admin_response'])): ?>
                        <div style="margin-top:15px; background:#F4F9F5; border-left:4px solid #00853F; padding:15px; border-radius:6px;">
                            <strong style="color:#00853F; font-size:0.9rem; display:block; margin-bottom:5px;">🏛️ Réponse Officielle — Mairie de Tattaguine</strong>
                            <p style="margin:0; color:#222; font-size:0.95rem;"><?= nl2br(Security::sanitize($comment['admin_response'])) ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p style="color:var(--text-muted);">Soyez le premier à commenter cette publication !</p>
    <?php endif; ?>

</div>

<?php require_once APP_PATH . '/Views/layouts/footer.php'; ?>
