<?php
// back/public/api/index.php

header('Content-Type: application/json; charset=utf-8');

// ----- CORS TRÈS SIMPLE -----
// On autorise directement le domaine front
header('Access-Control-Allow-Origin: https://ecom.awesome-ride.62-210-99-155.plesk.page');
header('Vary: Origin');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept');

// Préflight (OPTIONS) : on répond vide avec 204
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// ----- Chargement des modèles -----
require_once __DIR__ . '/../../src/models/ProductModel.php';
require_once __DIR__ . '/../../src/models/OrderModel.php';

// ----- Router -----
$method  = $_SERVER['REQUEST_METHOD'];
$uriPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); // ex: /api/products/1

$basePrefix = '/api';
if (strpos($uriPath, $basePrefix) === 0) {
    $relative = substr($uriPath, strlen($basePrefix)); // ex: /products/1
} else {
    $relative = $uriPath;
}

$relative = trim($relative, '/');               // "products/1" ou ""
$segments = ($relative === '') ? array() : explode('/', $relative);

try {
    // GET /api/
    if ($method === 'GET' && count($segments) === 0) {
        echo json_encode(array(
            'status'  => 'ok',
            'message' => 'API Awesome Ride',
        ));
        exit;
    }

    // GET /api/products
    if ($method === 'GET'
        && isset($segments[0]) && $segments[0] === 'products'
        && count($segments) === 1
    ) {
        $productModel = new ProductModel();
        $products = $productModel->getAllProducts();
        echo json_encode($products);
        exit;
    }

    // GET /api/products/{id}
    if ($method === 'GET'
        && isset($segments[0]) && $segments[0] === 'products'
        && count($segments) === 2
    ) {
        $id = (int)$segments[1];
        $productModel = new ProductModel();
        $product = $productModel->getProductById($id);

        if (!$product) {
            http_response_code(404);
            echo json_encode(array('error' => 'Produit introuvable'));
            exit;
        }

        echo json_encode($product);
        exit;
    }

    // POST /api/orders
    if ($method === 'POST'
        && isset($segments[0]) && $segments[0] === 'orders'
    ) {
        $bodyJson = file_get_contents('php://input');
        $body = json_decode($bodyJson, true);

        if (!is_array($body)) {
            http_response_code(400);
            echo json_encode(array('error' => 'JSON invalide'));
            exit;
        }

        $orderModel = new OrderModel();
        $orderId = $orderModel->createOrder($body);

        echo json_encode(array(
            'success'  => true,
            'order_id' => $orderId,
        ));
        exit;
    }

    // Route inconnue
    http_response_code(404);
    echo json_encode(array('error' => 'Route introuvable'));
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array(
        'error'   => 'Erreur serveur',
        'details' => $e->getMessage(),
    ));
}
