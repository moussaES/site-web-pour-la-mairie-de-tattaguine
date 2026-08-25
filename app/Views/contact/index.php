<?php require_once APP_PATH . '/Views/layouts/header.php'; ?>

<div class="container">
    <div style="margin-bottom:30px;">
        <h1 style="color:var(--primary-color); margin-bottom:5px;">Contactez la Mairie de Tattaguine</h1>
        <p style="color:var(--text-muted);">Envoyez vos questions, requêtes ou remarques aux services municipaux</p>
    </div>

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

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:40px;">
        
        <!-- Formulaire de contact -->
        <div style="background:#FFF; padding:30px; border-radius:8px; box-shadow:0 2px 10px rgba(0,0,0,0.05);">
            <h3 style="color:var(--primary-color); margin-top:0; margin-bottom:20px;">Formulaire de Message</h3>
            <form action="<?= BASE_URL ?>/contact" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

                <div style="margin-bottom:15px;">
                    <label style="display:block; font-weight:bold; margin-bottom:5px;">Nom et Prénom *</label>
                    <input type="text" name="full_name" required placeholder="ex: Fatou Diallo" style="width:100%; padding:10px; border:1px solid #CCC; border-radius:6px;">
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px; margin-bottom:15px;">
                    <div>
                        <label style="display:block; font-weight:bold; margin-bottom:5px;">Adresse E-mail *</label>
                        <input type="email" name="email" required placeholder="ex: fatou@gmail.com" style="width:100%; padding:10px; border:1px solid #CCC; border-radius:6px;">
                    </div>
                    <div>
                        <label style="display:block; font-weight:bold; margin-bottom:5px;">Téléphone</label>
                        <input type="text" name="phone" placeholder="ex: 77 XXX XX XX" style="width:100%; padding:10px; border:1px solid #CCC; border-radius:6px;">
                    </div>
                </div>

                <div style="margin-bottom:15px;">
                    <label style="display:block; font-weight:bold; margin-bottom:5px;">Objet de votre demande *</label>
                    <input type="text" name="subject" required placeholder="ex: Renseignement État Civil" style="width:100%; padding:10px; border:1px solid #CCC; border-radius:6px;">
                </div>

                <div style="margin-bottom:15px;">
                    <label style="display:block; font-weight:bold; margin-bottom:5px;">Message *</label>
                    <textarea name="message" rows="5" required placeholder="Détaillez votre demande..." style="width:100%; padding:10px; border:1px solid #CCC; border-radius:6px; font-family:inherit;"></textarea>
                </div>

                <div style="margin-bottom:20px;">
                    <label style="font-weight:bold; margin-right:10px; color:var(--primary-color);">Test anti-bot : <?= $captchaQuestion ?> *</label>
                    <input type="number" name="captcha_answer" required style="width:80px; padding:8px; border:1px solid #CCC; border-radius:6px;">
                </div>

                <button type="submit" class="btn-primary" style="width:100%; padding:12px; border:none; cursor:pointer; font-size:1rem;">Envoyer mon message</button>
            </form>
        </div>

        <!-- Informations de contact Mairie -->
        <div>
            <div style="background:#FFF; padding:30px; border-radius:8px; box-shadow:0 2px 10px rgba(0,0,0,0.05); margin-bottom:25px;">
                <h3 style="color:var(--primary-color); margin-top:0; margin-bottom:15px;">Hôtel de Ville de Tattaguine</h3>
                <p><strong>Adresse :</strong> Hôtel de Ville, Commune de Tattaguine, Région de Fatick, Sénégal</p>
                <p><strong>Téléphone :</strong> +221 33 XXX XX XX</p>
                <p><strong>E-mail Officiel :</strong> contact@tattaguine.gouv.sn</p>
                <p><strong>Horaires d'ouverture :</strong><br>Du Lundi au Vendredi : 08h00 – 17h00</p>
            </div>

            <div style="background:linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color:#FFF; padding:25px; border-radius:8px;">
                <h3 style="margin-top:0; color:var(--accent-color);">Service État Civil</h3>
                <p style="margin:0; opacity:0.9;">Pour toute demande relative aux actes de naissance, de mariage ou de décès, vous pouvez vous présenter munis de vos pièces d'identité durant les heures d'ouverture.</p>
            </div>
        </div>

    </div>
</div>

<?php require_once APP_PATH . '/Views/layouts/footer.php'; ?>
