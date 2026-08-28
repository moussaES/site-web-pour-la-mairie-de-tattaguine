<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Security::sanitize($pageTitle ?? 'Connexion Admin — Mairie de Tattaguine') ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css">
    <style>
        body.login-body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #102C57 0%, #00853F 100%);
        }
        .login-card {
            background-color: #FFF;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
        }
        .login-card h2 {
            color: #102C57;
            text-align: center;
            margin-bottom: 5px;
        }
        .login-card p {
            text-align: center;
            color: #6C757D;
            font-size: 0.9rem;
            margin-bottom: 25px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }
        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #CCC;
            border-radius: 6px;
            font-size: 0.95rem;
        }
        .btn-block {
            width: 100%;
            padding: 12px;
            background-color: #00853F;
            color: #FFF;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn-block:hover {
            background-color: #006831;
        }
    </style>
</head>
<body class="login-body">

    <div class="login-card">
        <h2>SUNU TATTAGUINE</h2>
        <p>Espace d'Administration réservé aux agents</p>

        <?php if (!empty($flashError)): ?>
            <div class="alert alert-danger"><?= Security::sanitize($flashError) ?></div>
        <?php endif; ?>

        <form action="<?= BASE_URL ?>/admin/login" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

            <div class="form-group">
                <label for="identifier">Identifiant ou E-mail</label>
                <input type="text" id="identifier" name="identifier" class="form-control" placeholder="ex: admin" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn-block">Se Connecter</button>
        </form>

        <p style="margin-top:20px; text-align:center;">
            <a href="<?= BASE_URL ?>" style="color:#102C57; text-decoration:none; font-size:0.85rem;">← Retour au site public</a>
        </p>
    </div>

</body>
</html>
