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

$order_placed = false;
$order_data = null;
$ordered_items = null;
$user_data = null;

// Handle invoice download
if (isset($_GET['download_invoice']) && isset($_GET['order_id'])) {
    $order_id = $_GET['order_id'];

    // Get order details - using correct column names from your database
    $stmt = $db->getConnection()->prepare("SELECT * FROM orders WHERE order_id = ? AND user_id = ?");
    $stmt->execute([$order_id, $_SESSION['user_id']]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($order) {
        // Get order items - using correct product_id column
        $stmt = $db->getConnection()->prepare("
            SELECT oi.*, p.name 
            FROM order_items oi 
            JOIN products p ON oi.product_id = p.product_id 
            WHERE oi.order_id = ?
        ");
        $stmt->execute([$order_id]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $user_info = $user->readById($_SESSION['user_id']);
        $order_info = [
            'order_id' => $order['order_id'],
            'total_amount' => $order['total_amount'],
            'order_date' => $order['order_date']
        ];

        $invoice->generatePDF($order_info, $items, $user_info);
    }
}

$cart_items = $cart->getCart($_SESSION['user_id']);
$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}

if (isset($_POST['place_order']) && !empty($cart_items)) {
    $stmt = $db->getConnection()->prepare("INSERT INTO orders (user_id, total_amount) VALUES (?, ?)");
    $stmt->execute([$_SESSION['user_id'], $total]);
    $order_id = $db->getConnection()->lastInsertId();

    foreach ($cart_items as $item) {
        $stmt = $db->getConnection()->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $stmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);
    }

    // Store order data for success display
    $order_data = [
        'order_id' => $order_id,
        'total_amount' => $total,
        'order_date' => date('Y-m-d H:i:s')
    ];
    $ordered_items = $cart_items;
    $user_data = $user->readById($_SESSION['user_id']);
    $order_placed = true;

    // Clear cart
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
        <?php if ($order_placed): ?>
            <!-- Order Success Card -->
            <div style="background: #d4edda; border: 1px solid #c3e6cb; border-radius: 8px; padding: 20px; margin: 20px 0; color: #155724;">
                <h1 style="color: #155724; margin-top: 0;">✅ Order Placed Successfully!</h1>
                <p><strong>Order ID:</strong> #<?php echo $order_data['order_id']; ?></p>
                <p><strong>Order Date:</strong> <?php echo $order_data['order_date']; ?></p>
                <p><strong>Customer:</strong> <?php echo htmlspecialchars($user_data['name']); ?></p>

                <h3>Order Details:</h3>
                <table class="cart-table" style="margin: 15px 0;">
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                    </tr>
                    <?php foreach ($ordered_items as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td>$<?php echo number_format($item['price'], 2); ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
                <p><strong>Total Amount: $<?php echo number_format($order_data['total_amount'], 2); ?></strong></p>

                <div style="margin-top: 20px;">
                    <a href="?download_invoice=1&order_id=<?php echo $order_data['order_id']; ?>"
                        style="background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;">
                        📄 Download Invoice
                    </a>
                    <a href="index.php"
                        style="background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
                        🛍️ Continue Shopping
                    </a>
                </div>
            </div>
        <?php else: ?>
            <h1>Checkout</h1>
            <?php if (empty($cart_items)): ?>
                <p>Your cart is empty. <a href="index.php">Continue shopping</a></p>
            <?php else: ?>
                <table class="cart-table">
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                    </tr>
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
            <?php endif; ?>
        <?php endif; ?>
    </main>
    <?php include '../includes/footer.php'; ?>
</body>

</html>