<?php
require_once '../includes/config.php';
require_once '../classes/Database.php';
require_once '../classes/Cart.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$db = new Database();
$cart = new Cart($db);

$product_id = $_POST['product_id'] ?? null;
$quantity = $_POST['quantity'] ?? 1;

if ($product_id) {
    $cart->add($_SESSION['user_id'], $product_id, $quantity);

    // Get the correct cart count
    $cart_count = $cart->getCartCount($_SESSION['user_id']);

    echo json_encode(['success' => true, 'cart_count' => $cart_count]);
} else {
    echo json_encode(['error' => 'Missing product ID']);
}
