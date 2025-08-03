<?php
require_once '../includes/config.php';
require_once '../classes/Database.php';
require_once '../classes/Cart.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$db = new Database();
$cart = new Cart($db);
$userId = $_SESSION['user_id'];

// --- Handle AJAX Requests ---
if (isset($_POST['ajax_action'])) {
    header('Content-Type: application/json');
    $productId = (int)($_POST['product_id'] ?? 0);
    $error = null;

    if ($_POST['ajax_action'] === 'update_quantity') {
        $quantity = (int)($_POST['quantity'] ?? 0);
        $success = $quantity > 0
            ? $cart->update($userId, $productId, $quantity)
            : $cart->remove($userId, $productId);

        if (!$success && isset($_SESSION['cart_error'])) {
            $error = $_SESSION['cart_error'];
            unset($_SESSION['cart_error']);
        }
    }

    if ($_POST['ajax_action'] === 'remove_item') {
        $cart->remove($userId, $productId);
    }

    $items = $cart->getCart($userId);
    $total = array_reduce($items, function ($sum, $item) {
        $price = $item['effective_price'] ?? $item['price'];
        return $sum + ($price * $item['quantity']);
    }, 0);

    echo json_encode([
        'success' => empty($error),
        'cart_count' => $cart->getCartCount($userId),
        'total' => number_format($total, 2),
        'error' => $error
    ]);
    exit;
}

// --- Handle Form Submissions (Non-AJAX) ---
if (isset($_POST['add_to_cart'])) {
    $cart->add($userId, $_POST['product_id'], $_POST['quantity']);
}

if (isset($_POST['update_cart'], $_POST['quantity'])) {
    foreach ($_POST['quantity'] as $pid => $qty) {
        $pid = (int)$pid;
        $qty = (int)$qty;
        $qty > 0 ? $cart->update($userId, $pid, $qty) : $cart->remove($userId, $pid);
    }
}

if (isset($_POST['remove_item'], $_POST['remove_product_id'])) {
    $cart->remove($userId, (int)$_POST['remove_product_id']);
}

$cartItems = $cart->getCart($userId);
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

        .cart-alert {
            background-color: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
            padding: 10px 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
    </style>
</head>

<body>
    <?php include '../includes/header.php'; ?>
    <main>
        <h1>Shopping Cart</h1>

        <?php if (!empty($_SESSION['cart_error'])): ?>
            <div class="cart-alert"><?php echo htmlspecialchars($_SESSION['cart_error']); ?></div>
            <?php unset($_SESSION['cart_error']); ?>
        <?php endif; ?>

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
                    <?php foreach ($cartItems as $item): ?>
                        <?php
                        $price = $item['effective_price'] ?? $item['price'];
                        $subtotal = $price * $item['quantity'];
                        $total += $subtotal;
                        ?>
                        <tr data-product-id="<?php echo $item['product_id']; ?>">
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <img src="../assets/images/products/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" style="width: 60px; height: 60px; object-fit: cover; border-radius: 6px;">
                                    <span><?php echo htmlspecialchars($item['name']); ?></span>
                                </div>
                            </td>
                            <td>$<?php echo number_format($price, 2); ?></td>
                            <td>
                                <input type="number" name="quantity[<?php echo $item['product_id']; ?>]" value="<?php echo $item['quantity']; ?>" min="1" class="quantity-input" data-product-id="<?php echo $item['product_id']; ?>" data-price="<?php echo $price; ?>" />
                            </td>
                            <td class="subtotal">$<?php echo number_format($subtotal, 2); ?></td>
                            <td>
                                <button type="button" class="remove-button ajax-remove" data-product-id="<?php echo $item['product_id']; ?>" title="Remove item">
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
        const updateCartItem = (productId, quantity, price, element) => {
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
                    if (!data.success) {
                        alert(data.error || 'Stock limit exceeded');
                        element.value = element.defaultValue; // revert to previous
                        return;
                    }

                    const countEl = document.getElementById('cart-count');
                    if (countEl) {
                        countEl.textContent = data.cart_count;
                        countEl.classList.add('cart-bump');
                        setTimeout(() => countEl.classList.remove('cart-bump'), 300);
                    }

                    const row = element.closest('tr');
                    const subtotalEl = row.querySelector('.subtotal');
                    if (subtotalEl) subtotalEl.textContent = '$' + (price * quantity).toFixed(2);

                    const totalEl = document.getElementById('cart-total');
                    if (totalEl) totalEl.textContent = data.total;

                    if (quantity === 0 && row) row.remove();
                })
                .catch(err => {
                    console.error(err);
                    alert('Something went wrong');
                });
        };

        const removeCartItem = (productId, button) => {
            const formData = new FormData();
            formData.append('ajax_action', 'remove_item');
            formData.append('product_id', productId);

            fetch('cart.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (!data.success) return alert('Error removing item');

                    const countEl = document.getElementById('cart-count');
                    if (countEl) {
                        countEl.textContent = data.cart_count;
                        countEl.classList.add('cart-bump');
                        setTimeout(() => countEl.classList.remove('cart-bump'), 300);
                    }

                    const row = button.closest('tr');
                    if (row) row.remove();

                    const totalEl = document.getElementById('cart-total');
                    if (totalEl) totalEl.textContent = data.total;
                })
                .catch(err => {
                    console.error(err);
                    alert('Something went wrong');
                });
        };

        document.querySelectorAll('.quantity-input').forEach(input => {
            input.addEventListener('change', function() {
                const productId = this.dataset.productId;
                const quantity = parseInt(this.value);
                const price = parseFloat(this.dataset.price);
                updateCartItem(productId, quantity, price, this);
            });
        });

        document.querySelectorAll('.ajax-remove').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.dataset.productId;
                removeCartItem(productId, this);
            });
        });
    </script>
</body>

</html>