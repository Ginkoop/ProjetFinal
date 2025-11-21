<?php
$pageTitle = "Commandes – Back-office";
require_once __DIR__ . '/../../src/Auth.php';
require_once __DIR__ . '/../../src/models/OrderModel.php';

Auth::requireAdmin();

$orderModel = new OrderModel();
$orders = $orderModel->getAllOrders();

require __DIR__ . '/../partials/admin-header.php'; ?>

<h1>Commandes</h1>
<p class="admin-subtitle">
    Liste des commandes enregistrées via la boutique.
</p>

<section class="card">
    <?php if (!$orders): ?>
        <p>Aucune commande pour le moment.</p>
    <?php else: ?>
        <table class="cart-table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Date</th>
                <th>Client</th>
                <th>Email</th>
                <th>Total</th>
                <th>Statut</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($orders as $order): ?>
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
                    <td>#<?= (int)$order['id'] ?></td>
                    <td><?= htmlspecialchars($formattedDate) ?></td>
                    <td><?= htmlspecialchars($order['prenom'] . ' ' . $order['nom']) ?></td>
                    <td><?= htmlspecialchars($order['email']) ?></td>
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
