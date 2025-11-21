<?php
$pageTitle = "Boutique – La Boîte à Objets";
$pageId = "home";
require __DIR__ . '/partials/header.php';
?>

<section class="hero">
    <div class="container">
        <h1>Découvrez nos objets du quotidien</h1>
        <p>Une sélection de mugs, t-shirts, tote bags et notre stylo personnalisable.</p>
    </div>
</section>

<section class="catalogue">
    <div class="container">
        <header class="section-header">
            <h2>Catalogue</h2>
            <p id="catalogue-status" aria-live="polite"></p>
        </header>

        <div id="products-grid"
             class="products-grid"
             aria-live="polite"
             aria-busy="true">
        </div>
    </div>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>
