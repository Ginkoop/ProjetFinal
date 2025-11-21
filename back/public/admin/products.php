<?php
$pageTitle = "Produits – Back-office";
require_once __DIR__ . '/../../src/Auth.php';
require_once __DIR__ . '/../../src/models/ProductModel.php';

Auth::requireAdmin();

$productModel = new ProductModel();
$products = $productModel->getAllProducts();

require __DIR__ . '/../partials/admin-header.php'; ?>

<h1>Produits</h1>
<p class="admin-subtitle">
    Gestion du catalogue (mugs, t-shirts, tote bags, stylos personnalisables).
</p>

<section class="card">
    <div class="card-header">
        <div>
            <div class="card-header-title">Liste des produits</div>
            <div class="card-header-subtitle">Création, modification et gestion des stocks.</div>
        </div>
        <a class="btn btn-primary" href="/admin/product-edit.php">Ajouter un produit</a>
    </div>

    <?php if (!$products): ?>
        <p>Aucun produit pour le moment.</p>
    <?php else: ?>
        <table class="cart-table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Catégorie</th>
                <th>Prix</th>
                <th>Stock</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($products as $p): ?>
                <tr>
                    <td><?= (int)$p['id'] ?></td>
                    <td><?= htmlspecialchars($p['nom']) ?></td>
                    <td><?= htmlspecialchars($p['category_name']) ?></td>
                    <td><?= number_format($p['prix'], 2, ',', ' ') ?> €</td>
                    <td><?= (int)$p['stock'] ?></td>
                    <td>
                        <a class="btn" href="/admin/product-edit.php?id=<?= (int)$p['id'] ?>">Modifier</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/../partials/admin-footer.php'; ?>
