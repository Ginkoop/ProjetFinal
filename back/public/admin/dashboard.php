<?php
$pageTitle = "Tableau de bord – Back-office";
require_once __DIR__ . '/../../src/Auth.php';
require_once __DIR__ . '/../../src/models/ProductModel.php';
require_once __DIR__ . '/../../src/models/OrderModel.php';

Auth::requireAdmin();

$productModel = new ProductModel();
$orderModel   = new OrderModel();

$products = $productModel->getAllProducts();
$orders   = $orderModel->getAllOrders();

require __DIR__ . '/../partials/admin-header.php'; ?>

<h1>Tableau de bord</h1>
<p class="admin-subtitle">
    Vue d’ensemble de l’activité : produits, commandes et clients.
</p>

<section class="admin-kpi-grid">
    <div class="admin-kpi">
        <div class="admin-kpi-label">Produits</div>
        <div class="admin-kpi-value"><?= count($products) ?></div>
    </div>
    <div class="admin-kpi">
        <div class="admin-kpi-label">Commandes</div>
        <div class="admin-kpi-value"><?= count($orders) ?></div>
    </div>
</section>

<section class="card">
    <div class="card-header">
        <div>
            <div class="card-header-title">Dernières commandes</div>
            <div class="card-header-subtitle">Les 5 commandes les plus récentes.</div>
        </div>
    </div>

    <?php if (!$orders): ?>
        <p>Aucune commande pour le moment.</p>
    <?php else: ?>
        <table class="cart-table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Date</th>
                <th>Client</th>
                <th>Total</th>
                <th>Statut</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach (array_slice($orders, 0, 5) as $order): ?>
                <?php
                $formattedDate = $order['date'];
                try {
                    $dt = new DateTime($order['date']);
                    $formattedDate = $dt->format('d/m/Y H:i:s');
                } catch (Throwable $e) {
                    // Si ça plante, on garde la valeur brute
                }
                ?>
                <tr>
                    <td>#<?= htmlspecialchars($order['id']) ?></td>
                    <td><?= htmlspecialchars($formattedDate) ?></td>
                    <td><?= htmlspecialchars($order['prenom'] . ' ' . $order['nom']) ?></td>
                    <td><?= number_format($order['total'], 2, ',', ' ') ?> €</td>
                    <td>
                        <span class="badge badge-status--<?= htmlspecialchars($order['statut']) ?>">
                            <?= htmlspecialchars($order['statut']) ?>
                        </span>
                    </td>
                    <td>
                        <a class="btn" href="orders-view.php?id=<?= (int)$order['id'] ?>">Voir</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/../partials/admin-footer.php'; ?>
