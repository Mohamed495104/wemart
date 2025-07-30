<?php
require_once '../includes/config.php';
require_once '../classes/Database.php';
require_once '../classes/Product.php';

$db = new Database();
$product = new Product($db);
$products = isset($_GET['search']) ? $product->search($_GET['search'], $_GET['category'] ?? null) : $product->readAll();
$categories = $db->getConnection()->query("SELECT * FROM categories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// Check if user qualifies for first-order discount (simplified: assume true for all users)
$is_first_order = true; // In a real app, check user login status or order history
$discount_code = 'WEMART20';
$discount_percentage = 0.20; // 20% off
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop - Wemart</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <main>
        <section class="hero">
            <h1>Shop All Products</h1>
            <p>Discover our full range of products with exclusive deals!</p>
        </section>
        <section class="promo-banner">
            <h2>First Order Discount!</h2>
            <p>Use code <strong><?php echo htmlspecialchars($discount_code); ?></strong> for 20% off your first order!</p>
        </section>
        <form method="GET" class="search-filter">
            <input type="text" name="search" placeholder="Search products" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
            <select name="category">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['category_id']; ?>" <?php echo (isset($_GET['category']) && $_GET['category'] == $cat['category_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Search</button>
        </form>
        <section class="products">
            <h2>All Products</h2>
            <div class="products-grid">
                <?php foreach ($products as $p): ?>
                    <div class="product-card">
                        <img src="<?php echo htmlspecialchars($p['image']); ?>" alt="<?php echo htmlspecialchars($p['name']); ?> product image">
                        <h3><?php echo htmlspecialchars($p['name']); ?></h3>
                        <p><?php echo htmlspecialchars($p['category_name']); ?></p>
                        <?php
                        // Determine base price (use deal_price if set, otherwise price)
                        $base_price = $p['deal_price'] !== null ? $p['deal_price'] : $p['price'];
                        // Apply 20% discount for first order
                        $discounted_price = $is_first_order ? $base_price * (1 - $discount_percentage) : $base_price;
                        ?>
                        <p class="price">
                            <?php if ($is_first_order && $base_price != $discounted_price): ?>
                                <span class="original-price">$<?php echo number_format($base_price, 2); ?></span>
                                $<?php echo number_format($discounted_price, 2); ?>
                                <span class="discount-note">(20% off with <?php echo htmlspecialchars($discount_code); ?>)</span>
                            <?php else: ?>
                                $<?php echo number_format($base_price, 2); ?>
                            <?php endif; ?>
                        </p>
                        <a href="product_details.php?id=<?php echo $p['product_id']; ?>" class="view-details">View Details</a>
                        <form method="POST" action="cart.php">
                            <input type="hidden" name="product_id" value="<?php echo $p['product_id']; ?>">
                            <input type="hidden" name="discount_code" value="<?php echo $is_first_order ? $discount_code : ''; ?>">
                            <input type="number" name="quantity" value="1" min="1" max="<?php echo $p['stock']; ?>">
                            <button type="submit" name="add_to_cart">Add to Cart</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>
    <?php include '../includes/footer.php'; ?>
    <script src="../assets/js/script.js"></script>
    <script src="https://kit.fontawesome.com/your-fontawesome-kit.js"></script>
</body>
</html>