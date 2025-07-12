
<?php
require_once 'config.php';
?>
<header>
    <nav>
        <a href="<?php echo BASE_URL; ?>"><img src="assets/images/static/logo.png" alt="Wemart Logo" style="height: 40px;"></a>
        <a href="<?php echo BASE_URL; ?>">Home</a>
        <a href="<?php echo BASE_URL; ?>cart.php">Cart (<?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?>)</a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="<?php echo BASE_URL; ?>pages/logout.php">Logout</a>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a href="<?php echo BASE_URL; ?>pages/admin/dashboard.php">Admin Panel</a>
            <?php endif; ?>
        <?php else: ?>
            <a href="<?php echo BASE_URL; ?>pages/login.php">Login</a>
            <a href="<?php echo BASE_URL; ?>pages/register.php">Register</a>
        <?php endif; ?>
    </nav>
</header>
