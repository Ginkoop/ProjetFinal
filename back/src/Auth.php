<?php
require_once __DIR__ . '/models/UserModel.php';

class Auth
{
    public static function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function login(string $email, string $password): bool
    {
        self::startSession();

        $userModel = new UserModel();
        $user = $userModel->findByEmail($email);

        if (!$user) {
            return false;
        }

        $stored = $user['password'];

        // Cas 1 : mot de passe hashé
        $ok = password_verify($password, $stored);

        // Cas 2 : mot de passe en clair (ex: admin123 dans le seed)
        if (!$ok && $password === $stored) {
            $ok = true;
            // Pour un vrai projet : ici tu pourrais re-hasher et update le mot de passe
        }

        if (!$ok) {
            return false;
        }

        $_SESSION['user'] = [
            'id'     => (int)$user['id'],
            'nom'    => $user['nom'],
            'prenom' => $user['prenom'],
            'email'  => $user['email'],
            'role'   => $user['role'],
        ];

        return true;
    }

    public static function logout(): void
    {
        self::startSession();
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        session_destroy();
    }

    public static function user(): ?array
    {
        self::startSession();
        return $_SESSION['user'] ?? null;
    }

    public static function isAdmin(): bool
    {
        $user = self::user();
        return $user && isset($user['role']) && $user['role'] === 'admin';
    }

    public static function requireAdmin(): void
    {
        if (!self::isAdmin()) {
            header('Location: /login.php');
            exit;
        }
    }
}
