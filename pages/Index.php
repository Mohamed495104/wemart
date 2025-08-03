<?php
require_once '../includes/config.php';
require_once '../classes/Database.php';
require_once '../classes/Product.php';

$db = new Database();
$product = new Product($db);
$products = isset($_GET['search']) ? $product->search($_GET['search'], $_GET['category'] ?? null) : $product->readAll();
$categories = $db->getConnection()->query("SELECT * FROM categories ORDER BY name LIMIT 4")->fetchAll(PDO::FETCH_ASSOC);
$featured_products = $product->getFeaturedProducts(4);
$deals = $product->getDailyDeals(3);
$brands = $db->getConnection()->query("SELECT * FROM brands ORDER BY name LIMIT 4")->fetchAll(PDO::FETCH_ASSOC);
$blog_posts = $db->getConnection()->query("SELECT bp.*, COALESCE(bc.name, 'General') as category_name 
                                           FROM blog_posts bp 
                                           LEFT JOIN blog_categories bc ON bp.category_id = bc.category_id 
                                           ORDER BY bp.created_at DESC LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
$customer_favorites = $product->getCustomerFavorites(4);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Wemart - Your Everyday Shopping Destination</title>
    <link rel="stylesheet" href="../assets/css/styles.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
    <?php include '../includes/header.php'; ?>
    <main>
        <section class="interactive-banner" style="background-image: url('<?php echo BASE_URL; ?>assets/images/products/banner.jpg');">

            <div class="banner-overlay">
                <h1>Discover Amazing Deals at Wemart!</h1>
                <p>Shop now and save big on your favorite products!</p>
                <a href="shop.php" class="cta-button">Start Shopping</a>
            </div>
        </section>

        <section class="hero">
            <h1>Welcome to Wemart</h1>
            <p>Shop everyday low prices!</p>
        </section>

        <section class="promo-banner">
            <h2>Exclusive Offer!</h2>
            <p>Get 20% off your first order with code <strong>WEMART20</strong></p>
            <a href="shop.php" class="cta-button">Shop Now</a>
        </section>

        <section class="deals-carousel">
            <h2>Daily Deals <span class="countdown" id="deal-countdown"></span></h2>
            <div class="carousel-container">
                <button class="carousel-prev">❮</button>
                <div class="carousel">
                    <?php foreach ($deals as $deal): ?>
                        <div class="deal-item">
                            <img src="../assets/images/products/<?php echo htmlspecialchars($deal['image']); ?>" alt="<?php echo htmlspecialchars($deal['name']); ?> deal">
                            <h3><?php echo htmlspecialchars($deal['name']); ?></h3>
                            <p class="original-price">$<?php echo number_format($deal['original_price'], 2); ?></p>
                            <p class="deal-price">$<?php echo number_format($deal['deal_price'], 2); ?></p>
                            <a href="product_details.php?id=<?php echo $deal['product_id']; ?>" class="view-deal">View Deal</a>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button class="carousel-next">❯</button>
            </div>
        </section>

        <section class="customer-favorites">
            <h2>Customer Favorites</h2>
            <div class="products-grid">
                <?php foreach ($customer_favorites as $cf): ?>
                    <div class="product-card favorite">
                        <a href="product_details.php?id=<?php echo $cf['product_id']; ?>" class="card-link">
                            <img src="../assets/images/products/<?php echo htmlspecialchars($cf['image']); ?>" alt="<?php echo htmlspecialchars($cf['name']); ?> product image">
                            <h3><?php echo htmlspecialchars($cf['name']); ?></h3>
                            <p><?php echo htmlspecialchars($cf['category_name']); ?></p>
                            <p class="price">$<?php echo number_format($cf['price'], 2); ?></p>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="featured-products">
            <h2>Featured Products</h2>
            <div class="products-grid">
                <?php foreach ($featured_products as $fp): ?>
                    <div class="product-card featured">
                        <a href="product_details.php?id=<?php echo $fp['product_id']; ?>" class="card-link">
                            <img src="../assets/images/products/<?php echo htmlspecialchars($fp['image']); ?>" alt="<?php echo htmlspecialchars($fp['name']); ?> product image">
                            <h3><?php echo htmlspecialchars($fp['name']); ?></h3>
                            <p><?php echo htmlspecialchars($fp['category_name']); ?></p>
                            <p class="price">$<?php echo number_format($fp['price'], 2); ?></p>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <?php include '../includes/footer.php'; ?>
    </main>

    <script src="../assets/js/script.js"></script>
    <script>
        document.querySelectorAll('.carousel-container').forEach(container => {
            const carousel = container.querySelector('.carousel');
            container.querySelector('.carousel-prev').onclick = () => carousel.scrollBy({
                left: -300,
                behavior: 'smooth'
            });
            container.querySelector('.carousel-next').onclick = () => carousel.scrollBy({
                left: 300,
                behavior: 'smooth'
            });
        });

        function startCountdown() {
            const el = document.getElementById('deal-countdown');
            const now = new Date();
            const midnight = new Date();
            midnight.setHours(24, 0, 0, 0);
            const timeLeft = midnight - now;
            const h = Math.floor((timeLeft / (1000 * 60 * 60)) % 24);
            const m = Math.floor((timeLeft / (1000 * 60)) % 60);
            const s = Math.floor((timeLeft / 1000) % 60);
            el.textContent = `Ends in ${h}h ${m}m ${s}s`;
        }
        setInterval(startCountdown, 1000);
        startCountdown();
    </script>
</body>

</html>