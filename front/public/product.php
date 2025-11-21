<?php
$pageTitle = "Fiche produit – La Boîte à Objets";
$pageId = "product";
require __DIR__ . '/partials/header.php';

// On récupère l'id en GET (ex: product.php?id=7)
$productId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
?>

<section class="product-page">
    <div class="container">
        <div id="product-details"
             data-product-id="<?= htmlspecialchars($productId) ?>"
             class="product-details"
             aria-live="polite" aria-busy="true">
            <p>Chargement du produit…</p>
        </div>
    </div>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>
