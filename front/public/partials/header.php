<?php
if (!isset($pageTitle)) {
    $pageTitle = "Awesome Ride - Boutique";
}
if (!isset($pageId)) {
    $pageId = "";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- chemins absolus depuis la racine du domaine front -->
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body data-page="<?= htmlspecialchars($pageId) ?>">
<a class="skip-link" href="#main">Aller au contenu principal</a>

<header class="site-header">
    <div class="container header-inner">
        <a class="logo" href="/index.php">
            <span class="logo-mark">AO</span>
            <span class="logo-text">La Boîte à Objets</span>
        </a>

        <nav class="main-nav" aria-label="Menu principal">
            <ul>
                <li><a href="/index.php">Boutique</a></li>
                <li><a href="/panier.php">Panier</a></li>
            </ul>
        </nav>
    </div>
</header>

<main id="main" class="site-main">
