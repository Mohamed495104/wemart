<?php
require_once '../includes/config.php';
require_once '../classes/Database.php';
require_once '../classes/Cart.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$db = new Database();
$cart = new Cart($db);

// Handle AJAX requests for real-time updates
if (isset($_POST['ajax_action'])) {
    header('Content-Type: application/json');

    if ($_POST['ajax_action'] === 'update_quantity') {
        $product_id = (int)$_POST['product_id'];
        $quantity = (int)$_POST['quantity'];

        if ($quantity > 0) {
            $cart->update($_SESSION['user_id'], $product_id, $quantity);
        } else {
            $cart->remove($_SESSION['user_id'], $product_id);
        }

        // Get updated cart count and total
        $cart_count = $cart->getCartCount($_SESSION['user_id']);
        $cart_items = $cart->getCart($_SESSION['user_id']);
        $total = 0;
        foreach ($cart_items as $item) {
            $effective_price = isset($item['effective_price']) ? $item['effective_price'] : $item['price'];
            $total += $effective_price * $item['quantity'];
        }

        echo json_encode([
            'success' => true,
            'cart_count' => $cart_count,
            'total' => number_format($total, 2)
        ]);
        exit;
    }

    if ($_POST['ajax_action'] === 'remove_item') {
        $product_id = (int)$_POST['product_id'];
        $cart->remove($_SESSION['user_id'], $product_id);

        // Get updated cart count and total
        $cart_count = $cart->getCartCount($_SESSION['user_id']);
        $cart_items = $cart->getCart($_SESSION['user_id']);
        $total = 0;
        foreach ($cart_items as $item) {
            $effective_price = isset($item['effective_price']) ? $item['effective_price'] : $item['price'];
            $total += $effective_price * $item['quantity'];
        }

        echo json_encode([
            'success' => true,
            'cart_count' => $cart_count,
            'total' => number_format($total, 2)
        ]);
        exit;
    }
}

// Handle regular form submissions (fallback)
if (isset($_POST['add_to_cart'])) {
    $cart->add($_SESSION['user_id'], $_POST['product_id'], $_POST['quantity']);
}

if (isset($_POST['update_cart']) && isset($_POST['quantity'])) {
    foreach ($_POST['quantity'] as $product_id => $qty) {
        $qty = (int)$qty;
        $product_id = (int)$product_id;
        if ($qty > 0) {
            $cart->update($_SESSION['user_id'], $product_id, $qty);
        } else {
            $cart->remove($_SESSION['user_id'], $product_id);
        }
    }
}

if (isset($_POST['remove_item']) && isset($_POST['remove_product_id'])) {
    $cart->remove($_SESSION['user_id'], (int)$_POST['remove_product_id']);
}

$cart_items = $cart->getCart($_SESSION['user_id']);
$total = 0;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Shopping Cart - Wemart</title>
    <link rel="stylesheet" href="../assets/css/styles.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <style>
        .cart-bump {
            animation: bump 0.3s ease;
        }

        @keyframes bump {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.2);
            }

            100% {
                transform: scale(1);
            }
        }
    </style>
</head>

<body>
    <?php include '../includes/header.php'; ?>
    <main>
        <h1>Shopping Cart</h1>
        <form method="POST" class="cart-form">
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $item): ?>
                        <?php
                        $effective_price = isset($item['effective_price']) ? $item['effective_price'] : $item['price'];
                        $subtotal = $effective_price * $item['quantity'];
                        $total += $subtotal;
                        ?>
                        <tr data-product-id="<?php echo $item['product_id']; ?>">
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <img src="../assets/images/products/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?> image" style="width: 60px; height: 60px; object-fit: cover; border-radius: 6px;">
                                    <span><?php echo htmlspecialchars($item['name']); ?></span>
                                </div>
                            </td>
                            <td>$<?php echo number_format($effective_price, 2); ?></td>
                            <td>
                                <input type="number"
                                    name="quantity[<?php echo $item['product_id']; ?>]"
                                    value="<?php echo $item['quantity']; ?>"
                                    min="1"
                                    class="quantity-input"
                                    data-product-id="<?php echo $item['product_id']; ?>"
                                    data-price="<?php echo $effective_price; ?>" />
                            </td>
                            <td class="subtotal">$<?php echo number_format($subtotal, 2); ?></td>
                            <td>
                                <button type="button"
                                    class="remove-button ajax-remove"
                                    data-product-id="<?php echo $item['product_id']; ?>"
                                    title="Remove item">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <p><strong>Total: $<span id="cart-total"><?php echo number_format($total, 2); ?></span></strong></p>

            <button type="submit" name="update_cart">Update Cart</button>
            <a href="checkout.php" class="button">Proceed to Checkout</a>
        </form>
    </main>
    <?php include '../includes/footer.php'; ?>

    <script>
        // Real-time quantity update
        document.querySelectorAll('.quantity-input').forEach(input => {
            input.addEventListener('change', function() {
                const productId = this.dataset.productId;
                const quantity = parseInt(this.value);
                const price = parseFloat(this.dataset.price);

                updateCartItem(productId, quantity, price, this);
            });
        });

        // Real-time remove functionality
        document.querySelectorAll('.ajax-remove').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.dataset.productId;
                removeCartItem(productId, this);
            });
        });

        function updateCartItem(productId, quantity, price, element) {
            const formData = new FormData();
            formData.append('ajax_action', 'update_quantity');
            formData.append('product_id', productId);
            formData.append('quantity', quantity);

            fetch('cart.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // Update cart count in header
                        const countEl = document.getElementById('cart-count');
                        if (countEl) {
                            countEl.textContent = data.cart_count;
                            countEl.classList.add('cart-bump');
                            setTimeout(() => countEl.classList.remove('cart-bump'), 300);
                        }

                        // Update subtotal for this row
                        const row = element.closest('tr');
                        const subtotalEl = row.querySelector('.subtotal');
                        if (subtotalEl) {
                            subtotalEl.textContent = '$' + (price * quantity).toFixed(2);
                        }

                        // Update total
                        const totalEl = document.getElementById('cart-total');
                        if (totalEl) {
                            totalEl.textContent = data.total;
                        }

                        // If quantity is 0, remove the row
                        if (quantity === 0) {
                            row.remove();
                        }
                    } else {
                        alert('Error updating cart');
                    }
                })
                .catch(err => {
                    console.error('Error:', err);
                    alert('Something went wrong');
                });
        }

        function removeCartItem(productId, element) {
            const formData = new FormData();
            formData.append('ajax_action', 'remove_item');
            formData.append('product_id', productId);

            fetch('cart.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // Update cart count in header
                        const countEl = document.getElementById('cart-count');
                        if (countEl) {
                            countEl.textContent = data.cart_count;
                            countEl.classList.add('cart-bump');
                            setTimeout(() => countEl.classList.remove('cart-bump'), 300);
                        }

                        // Remove the row
                        const row = element.closest('tr');
                        row.remove();

                        // Update total
                        const totalEl = document.getElementById('cart-total');
                        if (totalEl) {
                            totalEl.textContent = data.total;
                        }
                    } else {
                        alert('Error removing item');
                    }
                })
                .catch(err => {
                    console.error('Error:', err);
                    alert('Something went wrong');
                });
        }
    </script>
</body>

</html>