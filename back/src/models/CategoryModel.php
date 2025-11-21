<?php
require_once __DIR__ . '/../../config/database.php';

class CategoryModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getAllCategories(): array
    {
        $sql = "SELECT id, nom FROM categories ORDER BY nom ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
}
