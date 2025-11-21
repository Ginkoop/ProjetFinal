<?php
// front/public/api-proxy.php

header('Content-Type: application/json; charset=utf-8');

// Backend (domaine du back)
$backendBaseUrl = 'https://awesome-ride.62-210-99-155.plesk.page/api';

// Endpoint demandé : products, orders, etc.
$endpoint = isset($_GET['endpoint']) ? $_GET['endpoint'] : '';

// On sécurise un peu
$allowedEndpoints = array('products', 'orders');
if (!in_array($endpoint, $allowedEndpoints, true)) {
    http_response_code(400);
    echo json_encode(array('error' => 'Endpoint invalide'));
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$targetUrl = $backendBaseUrl;

// Construction de l’URL cible
if ($endpoint === 'products') {
    if ($method === 'GET') {
        // Liste ou détail
        if (isset($_GET['id'])) {
            $id = (int)$_GET['id'];
            $targetUrl .= '/products/' . $id;
        } else {
            $targetUrl .= '/products';
        }
    } else {
        http_response_code(405);
        echo json_encode(array('error' => 'Méthode non autorisée pour /products'));
        exit;
    }
} elseif ($endpoint === 'orders') {
    if ($method === 'POST') {
        $targetUrl .= '/orders';
    } else {
        http_response_code(405);
        echo json_encode(array('error' => 'Méthode non autorisée pour /orders'));
        exit;
    }
}

// Préparation de la requête vers le back
$ch = curl_init($targetUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

// On relaie le body pour les POST (commande)
if ($method === 'POST') {
    $body = file_get_contents('php://input');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Accept: application/json',
    ));
} else {
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Accept: application/json',
    ));
}

// Exécution
$response = curl_exec($ch);

if ($response === false) {
    http_response_code(502);
    echo json_encode(array(
        'error'   => 'Erreur lors de l’appel au back-end',
        'details' => curl_error($ch),
    ));
    curl_close($ch);
    exit;
}

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// On renvoie le code HTTP du back et la réponse brute
http_response_code($httpCode);
echo $response;
