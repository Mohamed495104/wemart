

    <?php
    require_once '../includes/config.php';
    require_once '../classes/Database.php';
    require_once '../classes/Cart.php';

    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }

    $db = new Database();
    $cart = new Cart($db);

    if (isset($_POST['add_to_cart'])) {
        $cart->add($_SESSION['user_id'], $_POST['product_id'], $_POST['quantity']);
    }
    if (isset($_POST['update_cart'])) {
        foreach ($_POST['quantity'] as $product_id => $quantity) {
            if ($quantity > 0) {
                $cart->update($_SESSION['user_id'], $product_id, $quantity);
            } else {
                $cart->remove($_SESSION['user_id'], $product_id);
            }
        }
    }
    if (isset($_POST['remove_item'])) {
        $cart->remove($_SESSION['user_id'], $_POST['product_id']);
    }

    $cart_items = $cart->getCart($_SESSION['user_id']);
    $total = 0;
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Shopping Cart - Wemart</title>
        <link rel="stylesheet" href="../assets/css/styles.css">
    </head>
    <body>
        <?php include '../includes/header.php'; ?>
        <main>
            <h1>Shopping Cart</h1>
            <form method="POST">
                <table class="cart-table">
                    <tr><th>Product</th><th>Price</th><th>Quantity</th><th>Total</th><th>Action</th></tr>
                    <?php foreach ($cart_items as $item): ?>
                        <?php $subtotal = $item['price'] * $item['quantity']; $total += $subtotal; ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td>$<?php echo number_format($item['price'], 2); ?></td>
                            <td><input type="number" name="quantity[<?php echo $item['product_id']; ?>]" value="<?php echo $item['quantity']; ?>" min="0"></td>
                            <td>$<?php echo number_format($subtotal, 2); ?></td>
                            <td>
                                <form method="POST">
                                    <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                    <button type="submit" name="remove_item">Remove</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
                <p><strong>Total: $<?php echo number_format($total, 2); ?></strong></p>
                <button type="submit" name="update_cart">Update Cart</button>
                <a href="checkout.php" class="button">Proceed to Checkout</a>
            </form>
    </main>
    <?php include '../includes/footer.php'; ?>
</body>
</html>
