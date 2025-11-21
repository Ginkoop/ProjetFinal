<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/ProductModel.php';
require_once __DIR__ . '/UserModel.php';

class OrderModel
{
    private PDO $db;
    private ProductModel $productModel;
    private UserModel $userModel;

    public function __construct()
    {
        $this->db = Database::getConnection();
        $this->productModel = new ProductModel();
        $this->userModel = new UserModel();
    }

    /**
     * Création d'une commande depuis le payload JSON (API front).
     */
    public function createOrder(array $payload): int
    {
        if (
            empty($payload['nom']) ||
            empty($payload['prenom']) ||
            empty($payload['email']) ||
            empty($payload['adresse']) ||
            empty($payload['items']) ||
            !is_array($payload['items'])
        ) {
            throw new InvalidArgumentException("Données de commande incomplètes.");
        }

        $total = 0;
        $itemsValidated = [];

        foreach ($payload['items'] as $item) {
            $productId = (int)($item['id'] ?? 0);
            $qty = (int)($item['quantite'] ?? $item['qty'] ?? 0);

            if ($productId <= 0 || $qty <= 0) {
                continue;
            }

            $product = $this->productModel->getProductById($productId);
            if (!$product) {
                continue;
            }

            $lineTotal = $product['prix'] * $qty;
            $total += $lineTotal;

            $itemsValidated[] = [
                'product_id' => $productId,
                'nom'        => $product['nom'],
                'prix'       => $product['prix'],
                'quantite'   => $qty,
                // on garde les options envoyées par le front (stylo perso)
                'options'    => $item['options'] ?? null,
            ];
        }

        if (empty($itemsValidated)) {
            throw new InvalidArgumentException("Aucun article valide dans la commande.");
        }

        // Récupérer ou créer l'utilisateur
        $user = $this->userModel->findByEmail($payload['email']);
        if ($user) {
            $userId = (int)$user['id'];
        } else {
            $userId = $this->userModel->createClient(
                $payload['nom'],
                $payload['prenom'],
                $payload['email']
            );
        }

        $this->db->beginTransaction();

        try {
            $sqlOrder = "INSERT INTO orders (date, total, statut, user_id)
                     VALUES (NOW(), :total, 'nouvelle', :user_id)";
            $stmtOrder = $this->db->prepare($sqlOrder);
            $stmtOrder->execute([
                'total'   => $total,
                'user_id' => $userId,
            ]);

            $orderId = (int)$this->db->lastInsertId();

            $sqlItem = "INSERT INTO order_items (order_id, product_id, quantite, prix_achat, options)
                    VALUES (:order_id, :product_id, :quantite, :prix_achat, :options)";
            $stmtItem = $this->db->prepare($sqlItem);

            $sqlUpdateStock = "UPDATE products SET stock = stock - :quantite
                           WHERE id = :product_id AND stock >= :quantite";
            $stmtStock = $this->db->prepare($sqlUpdateStock);

            foreach ($itemsValidated as $line) {
                $optionsJson = null;
                if (!empty($line['options'])) {
                    $optionsJson = json_encode($line['options'], JSON_UNESCAPED_UNICODE);
                }

                $stmtItem->execute([
                    'order_id'   => $orderId,
                    'product_id' => $line['product_id'],
                    'quantite'   => $line['quantite'],
                    'prix_achat' => $line['prix'],
                    'options'    => $optionsJson,
                ]);

                $stmtStock->execute([
                    'product_id' => $line['product_id'],
                    'quantite'   => $line['quantite'],
                ]);

                if ($stmtStock->rowCount() === 0) {
                    throw new RuntimeException("Stock insuffisant pour le produit ID " . $line['product_id']);
                }
            }

            $this->db->commit();
            return $orderId;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Liste des commandes pour le back-office.
     */
    public function getAllOrders(): array
    {
        $sql = "SELECT o.id, o.date, o.total, o.statut,
                       u.email, u.nom, u.prenom
                FROM orders o
                JOIN users u ON u.id = o.user_id
                ORDER BY o.date DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function getOrderById(int $id): ?array
    {
        $sql = "SELECT o.id, o.date, o.total, o.statut,
                       u.email, u.nom, u.prenom
                FROM orders o
                JOIN users u ON u.id = o.user_id
                WHERE o.id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $order = $stmt->fetch();
        return $order ?: null;
    }

    public function getOrderItems(int $orderId): array
    {
        $sql = "SELECT oi.id,
                   oi.product_id,
                   oi.quantite,
                   oi.prix_achat,
                   oi.options,
                   p.nom AS product_name
            FROM order_items oi
            JOIN products p ON p.id = oi.product_id
            WHERE oi.order_id = :order_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['order_id' => $orderId]);
        return $stmt->fetchAll();
    }
    public function updateStatus(int $id, string $statut): bool
    {
        $sql = "UPDATE orders SET statut = :statut WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id'     => $id,
            'statut' => $statut,
        ]);
    }

}
