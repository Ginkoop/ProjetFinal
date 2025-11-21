<?php
// back/public/login.php

require_once __DIR__ . '/../src/Auth.php';

Auth::startSession();

if (Auth::isAdmin()) {
    header('Location: /admin/dashboard.php');
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = "Merci de renseigner l'email et le mot de passe.";
    } else {
        if (Auth::login($email, $password)) {
            header('Location: /admin/dashboard.php');
            exit;
        } else {
            $error = "Identifiants invalides.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion admin – Awesome Ride</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- On réutilise le CSS global du front -->
    <link rel="stylesheet" href="/assets/css/styles.css">
    <style>
        body {
            margin: 0;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        .login-bg {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            background: radial-gradient(circle at top, #2563eb1a, transparent 55%),
            linear-gradient(180deg, #0f172a, #020617);
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            background: #ffffff;
            border-radius: 20px;
            padding: 1.75rem 1.75rem 1.5rem;
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.35);
            border: 1px solid #e5e7eb;
        }

        .login-logo {
            display: inline-flex;
            align-items: center;
            gap: .6rem;
            margin-bottom: 1rem;
        }

        .login-logo-mark {
            width: 36px;
            height: 36px;
            border-radius: 999px;
            background: var(--color-primary, #2563eb);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 700;
            font-size: 0.9rem;
        }

        .login-logo-text {
            font-weight: 600;
            font-size: 1rem;
        }

        .login-card h1 {
            margin: 0 0 .3rem;
            font-size: 1.4rem;
        }

        .login-subtitle {
            margin: 0 0 1.25rem;
            font-size: .9rem;
            color: var(--color-muted, #6b7280);
        }

        .login-error {
            color: #b91c1c;
            background: #fee2e2;
            border-radius: 10px;
            padding: .6rem .8rem;
            margin-bottom: .9rem;
            font-size: .9rem;
        }

        .login-form-group {
            margin-bottom: .9rem;
        }

        .login-form-group label {
            display: block;
            margin-bottom: .25rem;
            font-size: .9rem;
        }

        .login-input {
            width: 100%;
            padding: .45rem .6rem;
            border-radius: 10px;
            border: 1px solid #e5e7eb;
            font: inherit;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        .login-input:focus-visible {
            outline: 2px solid var(--color-primary, #2563eb);
            outline-offset: 2px;
            border-color: var(--color-primary, #2563eb);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        }

        .login-footer {
            margin-top: 1.25rem;
            font-size: .8rem;
            color: var(--color-muted, #6b7280);
        }

        .btn-login {
            width: 100%;
            margin-top: .5rem;
        }
    </style>
</head>
<body>
<div class="login-bg">
    <div class="login-card" role="form" aria-labelledby="login-title">
        <div class="login-logo">
            <span class="login-logo-mark">AO</span>
            <span class="login-logo-text">Back-office – La Boîte à Objets</span>
        </div>

        <h1 id="login-title">Connexion administrateur</h1>
        <p class="login-subtitle">
            Accès réservé au gestionnaire de la boutique.<br>
        </p>

        <?php if ($error): ?>
            <div class="login-error" role="alert">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="post" novalidate>
            <div class="login-form-group">
                <label for="email">Email</label>
                <input
                        id="email"
                        name="email"
                        type="email"
                        class="login-input"
                        required
                        autocomplete="username"
                >
            </div>

            <div class="login-form-group">
                <label for="password">Mot de passe</label>
                <input
                        id="password"
                        name="password"
                        type="password"
                        class="login-input"
                        required
                        autocomplete="current-password"
                >
            </div>

            <button type="submit" class="btn btn-primary btn-login">
                Se connecter
            </button>
        </form>

        <p class="login-footer">
            Projet pédagogique – aucun paiement réel n’est effectué. <br>
            Compte de test : <code>admin@laboiteaobjets.test</code> <br>
            MDP : <code>admin123</code>
        </p>
    </div>
</div>
</body>
</html>
