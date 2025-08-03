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
    <link rel="stylesheet" href="../assets/css/styles.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .out-of-stock {
            color: red;
            font-weight: bold;
            margin: 10px 0;
        }

        .disabled-button {
            background-color: #ccc;
            color: #555;
            cursor: not-allowed;
        }
    </style>
</head>

<body>
    <?php include '../includes/header.php'; ?>
    <main>
        <section class="product-details">
            <img src="../assets/images/products/<?php echo htmlspecialchars($prod['image']); ?>" alt="<?php echo htmlspecialchars($prod['name']); ?> product image">
            <h1><?php echo htmlspecialchars($prod['name']); ?></h1>
            <p><strong>Category:</strong> <?php echo htmlspecialchars($prod['category_name']); ?></p>
            <p><strong>Price:</strong> $<?php echo number_format($prod['price'], 2); ?></p>
            <p><strong>Description:</strong> <?php echo htmlspecialchars($prod['description']); ?></p>
            <p><strong>Stock:</strong> <?php echo $prod['stock']; ?></p>

            <?php if ($prod['stock'] > 0): ?>
                <form method="POST" action="cart.php">
                    <input type="hidden" name="product_id" value="<?php echo $prod['product_id']; ?>">
                    <label for="quantity"><strong>Quantity:</strong></label>
                    <input
                        type="number"
                        name="quantity"
                        id="quantity"
                        value="1"
                        min="1"
                        max="<?php echo $prod['stock']; ?>"
                        required>
                    <button type="submit" name="add_to_cart">Add to Cart</button>
                </form>
            <?php else: ?>
                <p class="out-of-stock">Out of stock. This item cannot be added to your cart right now.</p>
                <button class="disabled-button" disabled>Out of Stock</button>
            <?php endif; ?>
        </section>
    </main>
    <?php include '../includes/footer.php'; ?>
</body>

</html>