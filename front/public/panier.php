<?php
$pageTitle = "Votre panier – La Boîte à Objets";
$pageId = "cart";
require __DIR__ . '/partials/header.php';
?>

<section class="cart-page">
    <div class="container">
        <h1>Votre panier</h1>
        <p id="cart-status" class="sr-only" aria-live="polite"></p>

        <div id="cart-empty" class="cart-empty">
            <p>Votre panier est vide.</p>
            <a class="btn" href="index.php">Retour à la boutique</a>
        </div>

        <div id="cart-content" class="cart-content" hidden>
            <table class="cart-table">
                <thead>
                <tr>
                    <th>Produit</th>
                    <th>Options</th>
                    <th>Quantité</th>
                    <th>Prix</th>
                    <th>Total</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody id="cart-items">
                <!-- lignes de panier générées en JS -->
                </tbody>
            </table>

            <div class="cart-summary">
                <p>Total : <span id="cart-total">0,00 €</span></p>
                <a class="btn btn-primary" href="checkout.php">Valider la commande</a>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>
