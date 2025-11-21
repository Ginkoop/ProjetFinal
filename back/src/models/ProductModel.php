<?php
require_once __DIR__ . '/../../config/database.php';

class ProductModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getAllProducts(): array
    {
        $sql = "SELECT p.*, c.nom AS category_name
            FROM products p
            JOIN categories c ON c.id = p.category_id
            ORDER BY p.nom ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }


    public function getProductById(int $id): ?array
    {
        $sql = "SELECT p.*, c.nom AS category_name
            FROM products p
            JOIN categories c ON c.id = p.category_id
            WHERE p.id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }


    public function createProduct(array $data): int
    {
        $sql = "INSERT INTO products (nom, description, prix, stock, image, category_id, is_customizable, custom_config)
            VALUES (:nom, :description, :prix, :stock, :image, :category_id, :is_customizable, :custom_config)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'nom'             => $data['nom'],
            'description'     => $data['description'] ?? null,
            'prix'            => $data['prix'],
            'stock'           => $data['stock'],
            'image'           => $data['image'] ?? null,
            'category_id'     => $data['category_id'],
            'is_customizable' => $data['is_customizable'] ?? 0,
            'custom_config'   => $data['custom_config'] ?? null,
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function updateProduct(int $id, array $data): bool
    {
        $sql = "UPDATE products
            SET nom = :nom,
                description = :description,
                prix = :prix,
                stock = :stock,
                image = :image,
                category_id = :category_id,
                is_customizable = :is_customizable,
                custom_config = :custom_config
            WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id'             => $id,
            'nom'            => $data['nom'],
            'description'    => $data['description'] ?? null,
            'prix'           => $data['prix'],
            'stock'          => $data['stock'],
            'image'          => $data['image'] ?? null,
            'category_id'    => $data['category_id'],
            'is_customizable'=> $data['is_customizable'] ?? 0,
            'custom_config'  => $data['custom_config'] ?? null,
        ]);
    }

    public function deleteProduct(int $id): bool
    {
        $sql = "DELETE FROM products WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    public function updateStock(int $id, int $stock): bool
    {
        $sql = "UPDATE products SET stock = :stock WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id'    => $id,
            'stock' => $stock,
        ]);
    }

}
