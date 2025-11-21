<?php
$pageTitle = "Stocks – Back-office";
require_once __DIR__ . '/../../src/Auth.php';
require_once __DIR__ . '/../../src/models/ProductModel.php';

Auth::requireAdmin();

$productModel = new ProductModel();

$successMessage = null;
$errorMessage = null;

// Traitement du formulaire (mise à jour en masse)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stocks = $_POST['stock'] ?? [];

    if (!is_array($stocks)) {
        $errorMessage = "Données de stock invalides.";
    } else {
        foreach ($stocks as $id => $value) {
            $id = (int)$id;
            $stock = (int)$value;

            if ($id <= 0 || $stock < 0) {
                // on ignore les valeurs incorrectes
                continue;
            }

            $productModel->updateStock($id, $stock);
        }

        $successMessage = "Les stocks ont été mis à jour.";
    }
}

// On récupère la liste des produits après éventuelle mise à jour
$products = $productModel->getAllProducts();

require __DIR__ . '/../partials/admin-header.php';
?>

<h1>Stocks</h1>
<p class="admin-subtitle">
    Edition en masse des stocks de tous les produits.
</p>

<section class="card">
    <?php if ($errorMessage): ?>
        <p style="color:#b91c1c;"><?= htmlspecialchars($errorMessage) ?></p>
    <?php endif; ?>

    <?php if ($successMessage): ?>
        <p style="color:#166534;"><?= htmlspecialchars($successMessage) ?></p>
    <?php endif; ?>

    <?php if (!$products): ?>
        <p>Aucun produit pour le moment.</p>
    <?php else: ?>
        <form method="post">
            <table class="cart-table">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Produit</th>
                    <th>Catégorie</th>
                    <th>Stock actuel</th>
                    <th>Nouveau stock</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($products as $p): ?>
                    <tr>
                        <td><?= (int)$p['id'] ?></td>
                        <td><?= htmlspecialchars($p['nom']) ?></td>
                        <td><?= htmlspecialchars($p['category_name']) ?></td>
                        <td><?= (int)$p['stock'] ?></td>
                        <td>
                            <input
                                type="number"
                                name="stock[<?= (int)$p['id'] ?>]"
                                min="0"
                                value="<?= (int)$p['stock'] ?>"
                                style="width: 80px;"
                            >
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <div style="margin-top: 1rem; display:flex; justify-content:flex-end;">
                <button type="submit" class="btn btn-primary">
                    Enregistrer les stocks
                </button>
            </div>
        </form>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/../partials/admin-footer.php'; ?>
