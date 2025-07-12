
<?php
require_once '../includes/config.php';
require_once '../classes/Database.php';
require_once '../classes/Product.php';

$db = new Database();
$product = new Product($db);
$products = isset($_GET['search']) ? $product->search($_GET['search'], $_GET['category'] ?? null) : $product->readAll();
$categories = $db->getConnection()->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wemart</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <main>
        <section class="hero">
            <h1>Welcome to Wemart</h1>
            <p>Shop everyday low prices!</p>
            <div id="weather-suggestions"></div>
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
            <?php foreach ($products as $p): ?>
                <div class="product-card">
                    <img src="<?php echo htmlspecialchars($p['image']); ?>" alt="<?php echo htmlspecialchars($p['name']); ?> product image">
                    <h2><?php echo htmlspecialchars($p['name']); ?></h2>
                    <p><?php echo htmlspecialchars($p['category_name']); ?></p>
                    <p>$<?php echo number_format($p['price'], 2); ?></p>
                    <a href="product_details.php?id=<?php echo $p['product_id']; ?>">View Details</a>
                    <form method="POST" action="cart.php">
                        <input type="hidden" name="product_id" value="<?php echo $p['product_id']; ?>">
                        <input type="number" name="quantity" value="1" min="1" max="<?php echo $p['stock']; ?>">
                        <button type="submit" name="add_to_cart">Add to Cart</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </section>
    </main>
    <?php include '../includes/footer.php'; ?>
    <script src="../assets/js/scripts.js"></script>
</body>
</html>
