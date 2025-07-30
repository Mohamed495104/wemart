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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wemart - Your Everyday Shopping Destination</title>
<link rel="stylesheet" href="../assets/css/styles.css?v=<?php echo time(); ?>">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <main>
        <section class="interactive-banner" style="background-image: url('../assets/images/banner-bg.jpg');">
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

        <section class="deals-carousel">
    <h2>Daily Deals <span class="countdown" id="deal-countdown"></span></h2>
    <div class="carousel-container">
        <button class="carousel-prev">❮</button>
        <div class="carousel">
            <?php foreach ($deals as $deal): ?>
                <div class="deal-item">
                    <img src="<?php echo htmlspecialchars($deal['image']); ?>" alt="<?php echo htmlspecialchars($deal['name']); ?> deal">
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
                <?php if (empty($customer_favorites)): ?>
                    <p>No customer favorites available at the moment.</p>
                <?php else: ?>
                    <?php foreach ($customer_favorites as $cf): ?>
                        <div class="product-card favorite">
                            <img src="<?php echo htmlspecialchars($cf['image']); ?>" alt="<?php echo htmlspecialchars($cf['name']); ?> product image">
                            <h3><?php echo htmlspecialchars($cf['name']); ?></h3>
                            <p><?php echo htmlspecialchars($cf['category_name']); ?></p>
                            <p class="price">$<?php echo number_format($cf['price'], 2); ?></p>
                            <a href="product_details.php?id=<?php echo $cf['product_id']; ?>" class="view-details">View Details</a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <section class="featured-products">
            <h2>Featured Products</h2>
            <div class="products-grid">
                <?php foreach ($featured_products as $fp): ?>
                    <div class="product-card featured">
                        <img src="<?php echo htmlspecialchars($fp['image']); ?>" alt="<?php echo htmlspecialchars($fp['name']); ?> product image">
                        <h3><?php echo htmlspecialchars($fp['name']); ?></h3>
                        <p><?php echo htmlspecialchars($fp['category_name']); ?></p>
                        <p class="price">$<?php echo number_format($fp['price'], 2); ?></p>
                        <a href="product_details.php?id=<?php echo $fp['product_id']; ?>" class="view-details">View Details</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
        <section class="why-shop">
            <h2>Why Shop with Wemart?</h2>
            <div class="why-shop-grid">
                <div class="why-shop-item">
                    <i class="fas fa-shipping-fast"></i>
                    <h3>Fast Shipping</h3>
                    <p>Nationwide delivery in 2-5 days!</p>
                </div>
                <div class="why-shop-item">
                    <i class="fas fa-tags"></i>
                    <h3>Great Deals</h3>
                    <p>Low prices every day!</p>
                </div>
                <div class="why-shop-item">
                    <i class="fas fa-headset"></i>
                    <h3>24/7 Support</h3>
                    <p>Our team is here to help!</p>
                </div>
            </div>
        </section>
        <section class="trust-badges">
            <h2>Shop with Confidence</h2>
            <div class="badges-grid">
                <div class="badge">
                    <i class="fas fa-lock"></i>
                    <p>Secure Payments</p>
                </div>
                <div class="badge">
                    <i class="fas fa-undo"></i>
                    <p>Easy Returns</p>
                </div>
                <div class="badge">
                    <i class="fas fa-shield-alt"></i>
                    <p>Trusted Quality</p>
                </div>
            </div>
        </section>
        <section class="testimonials">
            <h2>What Our Customers Say</h2>
            <div class="testimonials-grid">
                <div class="testimonial">
                    <p>"Wemart has the best selection and prices! My order arrived in just 3 days!"</p>
                    <h4>– Sarah M.</h4>
                </div>
                <div class="testimonial">
                    <p>"The customer service is amazing. They helped me with my order instantly."</p>
                    <h4>– John D.</h4>
                </div>
                <div class="testimonial">
                    <p>"I love the variety of products and how easy it is to shop here!"</p>
                    <h4>– Emily R.</h4>
                </div>
            </div>
        </section>
        <section class="blog-posts">
            <h2>Latest from Our Blog</h2>
            <div class="blog-grid">
                <?php if (empty($blog_posts)): ?>
                    <p>No blog posts available at the moment.</p>
                <?php else: ?>
                    <?php foreach ($blog_posts as $post): ?>
                        <div class="blog-item">
                            <img src="<?php echo htmlspecialchars($post['image'] ?? 'placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>">
                            <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                            <p class="blog-meta">
                                <?php echo date('M d, Y', strtotime($post['created_at'])); ?> | 
                                <?php echo htmlspecialchars($post['category_name']); ?>
                            </p>
                            <p><?php echo htmlspecialchars(substr($post['content'], 0, 100)) . '...'; ?></p>
                            <a href="blog.php?id=<?php echo $post['post_id']; ?>" class="blog-link">Read More</a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
        <section class="social-feed">
            <h2>Follow Us on Social Media</h2>
            <div class="social-grid">
                <div class="social-post">
                    <img src="../assets/images/social1.jpg" alt="Social post 1">
                    <p>Check out our latest arrivals! #Wemart #NewProducts</p>
                </div>
                <div class="social-post">
                    <img src="../assets/images/social2.jpg" alt="Social post 2">
                    <p>Join our summer sale now! #WemartSale</p>
                </div>
                <div class="social-post">
                    <img src="../assets/images/social3.jpg" alt="Social post 3">
                    <p>Share your Wemart finds with us! #ShopWemart</p>
                </div>
            </div>
        </section>
    
    </main>
    <?php include '../includes/footer.php'; ?>
    <script src="../assets/js/script.js"></script>
    <script src="https://kit.fontawesome.com/your-fontawesome-kit.js"></script>
    <script>
        // Carousel navigation
        document.querySelectorAll('.carousel-container').forEach(container => {
            const carousel = container.querySelector('.carousel');
            const prevBtn = container.querySelector('.carousel-prev');
            const nextBtn = container.querySelector('.carousel-next');

            prevBtn.addEventListener('click', () => {
                carousel.scrollBy({ left: -300, behavior: 'smooth' });
            });

            nextBtn.addEventListener('click', () => {
                carousel.scrollBy({ left: 300, behavior: 'smooth' });
            });
        });

        // Countdown timer for daily deals (resets at midnight)
        function startCountdown() {
            const countdownEl = document.getElementById('deal-countdown');
            const now = new Date();
            const midnight = new Date(now);
            midnight.setHours(24, 0, 0, 0);
            const timeLeft = midnight - now;

            const hours = Math.floor((timeLeft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);

            countdownEl.textContent = `Ends in ${hours}h ${minutes}m ${seconds}s`;
        }

        setInterval(startCountdown, 1000);
        startCountdown();
    </script>
</body>
</html>