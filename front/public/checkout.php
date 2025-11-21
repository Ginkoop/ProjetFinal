<?php
$pageTitle = "Validation de la commande – La Boîte à Objets";
$pageId = "checkout";
require __DIR__ . '/partials/header.php';
?>

<section class="checkout-page">
    <div class="container">
        <h1>Validation de la commande</h1>
        <p class="text-muted">Aucune transaction réelle n’est effectuée. Il s’agit d’un projet pédagogique.</p>

        <div id="checkout-messages" aria-live="polite"></div>

        <form id="checkout-form" class="checkout-form" novalidate>
            <fieldset>
                <legend>Vos informations</legend>

                <div class="form-group">
                    <label for="checkout-nom">Nom</label>
                    <input id="checkout-nom" name="nom" type="text" required>
                </div>

                <div class="form-group">
                    <label for="checkout-prenom">Prénom</label>
                    <input id="checkout-prenom" name="prenom" type="text" required>
                </div>

                <div class="form-group">
                    <label for="checkout-email">Email</label>
                    <input id="checkout-email" name="email" type="email" required>
                </div>

                <div class="form-group">
                    <label for="checkout-adresse">Adresse</label>
                    <textarea id="checkout-adresse" name="adresse" rows="3" required></textarea>
                </div>
            </fieldset>

            <button type="submit" class="btn btn-primary">Envoyer la commande</button>
        </form>
    </div>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>
