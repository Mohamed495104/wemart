<?php
require_once 'config.php';
?>
<header>
    <nav class="nav-left">
        <a href="<?php echo BASE_URL; ?>"><img src="assets/images/wemart.png" alt="Wemart Logo" style="height: 100px;"></a>
        <a href="<?php echo BASE_URL; ?>pages/index.php">Home</a>
    </nav>
    <nav class="nav-right">
        <a href="<?php echo BASE_URL; ?>cart.php">Cart (<?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?>)</a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a href="<?php echo BASE_URL; ?>pages/admin/dashboard.php">Admin Panel</a>
            <?php endif; ?>
            <span class="username">Hello <?php echo htmlspecialchars($_SESSION['name'] ?? 'User', ENT_QUOTES); ?></span>
            <a href="<?php echo BASE_URL; ?>pages/logout.php" class="logout">Logout</a>
        <?php else: ?>
            <a href="<?php echo BASE_URL; ?>pages/login.php">Login</a>
            <a href="<?php echo BASE_URL; ?>pages/register.php">Register</a>
        <?php endif; ?>
    </nav>
</header>