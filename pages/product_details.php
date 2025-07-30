
<?php
require_once '../includes/config.php';
require_once '../classes/Database.php';
require_once '../classes/Product.php';

$db = new Database();
$product = new Product($db);
$product_id = $_GET['id'] ?? 0;
$prod = $product->readById($product_id);

if (!$prod) {
    die("Product not found.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($prod['name']); ?> - Wemart</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <main>
        <section class="product-details">
            <img src="<?php echo htmlspecialchars($prod['image']); ?>" alt="<?php echo htmlspecialchars($prod['name']); ?> product image">
            <h1><?php echo htmlspecialchars($prod['name']); ?></h1>
            <p><strong>Category:</strong> <?php echo htmlspecialchars($prod['category_name']); ?></p>
            <p><strong>Price:</strong> $<?php echo number_format($prod['price'], 2); ?></p>
            <p><strong>Description:</strong> <?php echo htmlspecialchars($prod['description']); ?></p>
            <p><strong>Stock:</strong> <?php echo $prod['stock']; ?></p>
            <form method="POST" action="cart.php">
                <input type="hidden" name="product_id" value="<?php echo $prod['product_id']; ?>">
                <input type="number" name="quantity" value="1" min="1" max="<?php echo $prod['stock']; ?>">
                <button type="submit" name="add_to_cart">Add to Cart</button>
            </form>
        </section>
    </main>
    <?php include '../includes/footer.php'; ?>
</body>
</html>
