<?php
require_once 'config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get proper cart count using Cart class
$cart_count = 0;
if (isset($_SESSION['user_id'])) {
    require_once '../classes/Database.php';
    require_once '../classes/Cart.php';

    $db = new Database();
    $cart = new Cart($db);
    $cart_count = $cart->getCartCount($_SESSION['user_id']);
} else {
    // For non-logged-in users, use session cart count (fallback)
    $cart_count = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
}
?>
<header>
    <nav class="nav-left">
        <img src="<?php echo BASE_URL; ?>assets/images/wemart.png" alt="Wemart Logo" style="height: 30px;">
        <a href="<?php echo BASE_URL; ?>pages/index.php">Home</a>
    </nav>
    <nav class="nav-right">
        <a href="<?php echo BASE_URL; ?>pages/cart.php" class="cart-link" style="position: relative;">
            <i class="fas fa-shopping-cart"></i>
            <span id="cart-count" style="margin-left: 4px; font-weight: bold;"><?php echo $cart_count; ?></span>
        </a>

        <!-- ✅ Show Shop for everyone -->
        <a href="<?php echo BASE_URL; ?>pages/shop.php">Shop</a>

        <?php if (isset($_SESSION['user_id'])): ?>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a href="<?php echo BASE_URL; ?>pages/admin/dashboard.php">Admin Panel</a>
            <?php endif; ?>
            <span class="username">Hello <?php echo htmlspecialchars($_SESSION['name'] ?? 'User', ENT_QUOTES); ?></span>
            <a href="<?php echo BASE_URL; ?>pages/login.php?action=logout" class="logout">Logout</a>
        <?php else: ?>
            <a href="<?php echo BASE_URL; ?>pages/login.php">Login</a>
            <a href="<?php echo BASE_URL; ?>pages/register.php">Register</a>
        <?php endif; ?>
    </nav>
</header>