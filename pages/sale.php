<?php
require_once '../includes/config.php';
require_once '../classes/Database.php';
require_once '../classes/Product.php';

$db = new Database();
$product = new Product($db);

// Fetch sale products (products with deal_price not null)
$products = isset($_GET['search']) ? $product->searchSale($_GET['search'], $_GET['category'] ?? null) : $product->getSaleProducts();
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
    <title>Seasonal Sale - Wemart</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <main>
        <section class="hero">
            <h1>Seasonal Sale</h1>
            <p>Save up to 50% on select items!</p>
        </section>
        <section class="promo-banner">
            <h2>Exclusive First Order Offer!</h2>
            <p>Get an additional 20% off with code <strong><?php echo htmlspecialchars($discount_code); ?></strong></p>
        </section>
        <form method="GET" class="search-filter">
            <input type="text" name="search" placeholder="Search sale products" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
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
            <h2>Sale Products</h2>
            <div class="products-grid">
                <?php if (empty($products)): ?>
                    <p>No sale products found.</p>
                <?php else: ?>
                    <?php foreach ($products as $p): ?>
                        <div class="product-card sale">
                            <img src="<?php echo htmlspecialchars($p['image']); ?>" alt="<?php echo htmlspecialchars($p['name']); ?> product image">
                            <h3><?php echo htmlspecialchars($p['name']); ?></h3>
                            <p><?php echo htmlspecialchars($p['category_name']); ?></p>
                            <?php
                            // Base price is deal_price (since this is a sale product)
                            $base_price = $p['deal_price'];
                            $original_price = $p['original_price'];
                            // Apply 20% first-order discount if applicable
                            $discounted_price = $is_first_order ? $base_price * (1 - $discount_percentage) : $base_price;
                            // Calculate sale discount percentage
                            $sale_discount = ($original_price > 0) ? round((($original_price - $base_price) / $original_price) * 100) : 0;
                            ?>
                            <p class="price">
                                <span class="original-price">$<?php echo number_format($original_price, 2); ?></span>
                                $<?php echo number_format($base_price, 2); ?>
                                <span class="sale-discount"><?php echo $sale_discount; ?>% off</span>
                                <?php if ($is_first_order && $base_price != $discounted_price): ?>
                                    <br>
                                    <span class="first-order-price">$<?php echo number_format($discounted_price, 2); ?></span>
                                    <span class="discount-note">(Additional 20% off with <?php echo htmlspecialchars($discount_code); ?>)</span>
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
                <?php endif; ?>
            </div>
        </section>
    </main>
    <?php include '../includes/footer.php'; ?>
    <script src="../assets/js/script.js"></script>
    <script src="https://kit.fontawesome.com/your-fontawesome-kit.js"></script>
</body>
</html>