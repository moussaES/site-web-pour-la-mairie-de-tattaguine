<?php require_once APP_PATH . '/Views/layouts/header.php'; ?>

<div class="container" style="max-width:520px; margin-top:40px; margin-bottom:60px;">
    <div style="background:#FFF; padding:35px; border-radius:10px; box-shadow:0 4px 20px rgba(0,0,0,0.08); text-align:center;">
        
        <h2 style="color:var(--primary-color); margin-bottom:10px; font-size:1.8rem;">Sunu Tattaguine</h2>
        <p style="color:var(--text-muted); margin-bottom:25px; font-size:0.95rem;">Créer votre compte personnel</p>

        <?php if (!empty($flashError)): ?>
            <div style="background-color:#F8D7DA; color:#721C24; padding:12px; border-radius:6px; margin-bottom:20px; text-align:left;">
                <?= Security::sanitize($flashError) ?>
            </div>
        <?php endif; ?>

        <!-- Formulaire d'inscription classique (En Haut) -->
        <form action="<?= BASE_URL ?>/register" method="POST" style="text-align:left; margin-bottom:25px;">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

            <div style="margin-bottom:15px;">
                <label style="display:block; font-weight:bold; margin-bottom:5px; font-size:0.9rem;">Nom et Prénom *</label>
                <input type="text" name="full_name" required placeholder="ex: Mamadou Ndiaye" style="width:100%; padding:11px; border:1px solid #CCC; border-radius:6px; font-size:0.95rem;">
            </div>

            <div style="margin-bottom:15px;">
                <label style="display:block; font-weight:bold; margin-bottom:5px; font-size:0.9rem;">Adresse E-mail *</label>
                <input type="email" name="email" required placeholder="ex: mamadou@gmail.com" style="width:100%; padding:11px; border:1px solid #CCC; border-radius:6px; font-size:0.95rem;">
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px; margin-bottom:20px;">
                <div>
                    <label style="display:block; font-weight:bold; margin-bottom:5px; font-size:0.9rem;">Mot de passe *</label>
                    <input type="password" name="password" required placeholder="••••••••" style="width:100%; padding:11px; border:1px solid #CCC; border-radius:6px; font-size:0.95rem;">
                </div>
                <div>
                    <label style="display:block; font-weight:bold; margin-bottom:5px; font-size:0.9rem;">Confirmer *</label>
                    <input type="password" name="password_confirm" required placeholder="••••••••" style="width:100%; padding:11px; border:1px solid #CCC; border-radius:6px; font-size:0.95rem;">
                </div>
            </div>

            <button type="submit" class="btn-primary" style="width:100%; padding:12px; border:none; cursor:pointer; font-size:1rem; border-radius:6px;">Créer mon compte citoyen</button>
        </form>

        <div style="margin-bottom:20px; margin-top:20px; font-weight:bold; color:var(--text-muted); font-size:0.85rem; text-transform:uppercase; border-top:1px solid #EEE; padding-top:20px;">
            OU S'INSCRIRE AVEC VOTRE COMPTE GOOGLE
        </div>

        <!-- Inscription Rapide via Google (En Bas) -->
        <div style="margin-bottom:25px;">
            <div id="g_id_onload"
                 data-client_id="<?= htmlspecialchars($googleClientId) ?>"
                 data-context="signup"
                 data-ux_mode="popup"
                 data-callback="handleCredentialResponse"
                 data-auto_prompt="false">
            </div>

            <div class="g_id_signin"
                 data-type="standard"
                 data-shape="rectangular"
                 data-theme="outline"
                 data-text="signup_with"
                 data-size="large"
                 data-logo_alignment="left"
                 data-width="100%">
            </div>

            <button type="button" onclick="simulateOrTriggerGoogle()" style="width:100%; margin-top:10px; padding:12px; background:#FFF; border:1px solid #DADCE0; border-radius:6px; font-weight:600; color:#3C4043; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:10px; font-family:inherit;">
                <svg width="18" height="18" viewBox="0 0 18 18"><path fill="#4285F4" d="M17.64 9.2c0-.637-.057-1.251-.164-1.84H9v3.481h4.844c-.209 1.125-.843 2.078-1.796 2.717v2.258h2.908c1.702-1.567 2.684-3.874 2.684-6.616z"/><path fill="#34A853" d="M9 18c2.43 0 4.467-.806 5.956-2.18l-2.908-2.259c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332A8.997 8.997 0 0 0 9 18z"/><path fill="#FBBC05" d="M3.964 10.71A5.41 5.41 0 0 1 3.682 9c0-.593.102-1.17.282-1.71V4.958H.957A8.996 8.996 0 0 0 0 9c0 1.452.348 2.827.957 4.042l3.007-2.332z"/><path fill="#EA4335" d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0A8.997 8.997 0 0 0 .957 4.958L3.964 7.29C4.672 5.163 6.656 3.58 9 3.58z"/></svg>
                S'inscrire rapidement avec Google
            </button>
        </div>

        <p style="margin-top:25px; font-size:0.9rem; color:var(--text-muted); border-top:1px solid #EEE; padding-top:15px;">
            Vous possédez déjà un compte ? <a href="<?= BASE_URL ?>/login" style="color:var(--secondary-color); font-weight:bold; text-decoration:none;">Se connecter</a>
        </p>

    </div>
</div>

<script src="https://accounts.google.com/gsi/client" async defer></script>
<script>
    function handleCredentialResponse(response) {
        if (!response.credential) return;

        fetch('<?= BASE_URL ?>/auth/google', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ credential: response.credential })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                window.location.href = data.redirect || '<?= BASE_URL ?>/mon-espace';
            } else {
                alert(data.message || 'Erreur lors de la connexion Google.');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Une erreur réseau est survenue lors de la connexion Google.');
        });
    }

    function simulateOrTriggerGoogle() {
        if (window.google && google.accounts && google.accounts.id) {
            google.accounts.id.prompt();
        } else {
            alert("Pour vous inscrire via Google, veuillez renseigner votre 'GOOGLE_CLIENT_ID' dans config/config.php depuis votre console Google Cloud.");
        }
    }
</script>

<?php require_once APP_PATH . '/Views/layouts/footer.php'; ?>
