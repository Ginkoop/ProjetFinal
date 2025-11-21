<?php
require_once __DIR__ . '/../../config/database.php';

class UserModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function findByEmail(string $email): ?array
    {
        $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function createClient(string $nom, string $prenom, string $email): int
    {
        // Pour un vrai projet : mot de passe aléatoire + hash
        $password = password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (nom, prenom, email, password, role)
                VALUES (:nom, :prenom, :email, :password, 'client')";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'nom'      => $nom,
            'prenom'   => $prenom,
            'email'    => $email,
            'password' => $password,
        ]);

        return (int)$this->db->lastInsertId();
    }
}
