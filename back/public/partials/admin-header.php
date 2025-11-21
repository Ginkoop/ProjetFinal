<?php
// back/public/partials/admin-header.php

require_once __DIR__ . '/../../src/Auth.php';

Auth::requireAdmin();
$user = Auth::user();

if (!isset($pageTitle)) {
    $pageTitle = "Back-office – Awesome Ride";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body>
<div class="admin-layout">
    <header class="admin-header">
        <div class="container admin-header-inner">
            <div>
                <strong>Back-office – La Boîte à Objets</strong>
                <small>
                    Connecté en tant que
                    <?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?>
                    (<?= htmlspecialchars($user['email']) ?>)
                </small>
            </div>
            <nav class="admin-header-nav" aria-label="Navigation back-office">
                <a href="/admin/dashboard.php">Tableau de bord</a>
                <a href="/admin/products.php">Produits</a>
                <a href="/admin/stocks.php">Stocks</a>
                <a href="/admin/orders.php">Commandes</a>
                <a href="/logout.php">Déconnexion</a>
            </nav>

        </div>
    </header>

    <main class="admin-main">
        <div class="container">
