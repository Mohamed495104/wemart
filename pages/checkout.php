

<?php
require_once '../includes/config.php';
require_once '../classes/Database.php';
require_once '../classes/Cart.php';
require_once '../classes/User.php';
require_once '../classes/Invoice.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$db = new Database();
$cart = new Cart($db);
$user = new User($db);
$invoice = new Invoice();

$cart_items = $cart->getCart($_SESSION['user_id']);
$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}

if (isset($_POST['place_order'])) {
    $stmt = $db->getConnection()->prepare("INSERT INTO orders (user_id, total_amount) VALUES (?, ?)");
    $stmt->execute([$_SESSION['user_id'], $total]);
    $order_id = $db->getConnection()->lastInsertId();

    foreach ($cart_items as $item) {
        $stmt = $db->getConnection()->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $stmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);
    }

    $order = ['order_id' => $order_id, 'total_amount' => $total, 'order_date' => date('Y-m-d H:i:s')];
    $user_data = $user->readById($_SESSION['user_id']);
    $invoice->generatePDF($order, $cart_items, $user_data);

    $stmt = $db->getConnection()->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $_SESSION['cart'] = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Wemart</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <main>
        <h1>Checkout</h1>
        <table class="cart-table">
            <tr><th>Product</th><th>Price</th><th>Quantity</th><th>Total</th></tr>
            <?php foreach ($cart_items as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                    <td>$<?php echo number_format($item['price'], 2); ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
        <p><strong>Total: $<?php echo number_format($total, 2); ?></strong></p>
        <form method="POST">
            <button type="submit" name="place_order">Place Order</button>
        </form>
    </main>
    <?php include '../includes/footer.php'; ?>
</body>
</html>
