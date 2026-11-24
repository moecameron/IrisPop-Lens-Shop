<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../config/database.php';

// Get Best Selling Products
$featuredProducts = $conn->query("
    SELECT p.*, b.brandName, c.categoryName, 
           COALESCE(SUM(oi.quantity), 0) as total_sold
    FROM products p 
    LEFT JOIN brands b ON p.brandID = b.brandID 
    LEFT JOIN categories c ON p.categoryID = c.categoryID 
    LEFT JOIN orderitems oi ON p.productID = oi.productID
    GROUP BY p.productID
    ORDER BY total_sold DESC, p.productID ASC 
    LIMIT 4
");
?>
<?php include 'includes/header.php'; ?>

    <!-- Hero Section -->
    <div class="hero">
        <div class="hero-content">
            <h2>Make Your <span class="pop">Iris Pop</span>🩷</h2>
            <p>Discover our Korean colored lenses designed to make your eyes pop and highlight your unique beauty</p>
            <a href="products.php" class="btn">Shop Now →</a>
        </div>
        <div class="hero-image">
            <img src="../../public/assets/images/hero.jpg" alt="Model wearing contact lenses">
        </div>
    </div>

    <!-- Categories Section -->
    <div class="section">
        <h2 class="section-title">Shop by Category🩷</h2>
        <p class="section-subtitle">Find the perfect lenses for your beautiful eyes𐙚</p>
        <div class="categories">
            <div class="category-card" onclick="location.href='products.php'">
                <div class="icon">📍</div>
                <h3>All Products</h3>
                <p>View our complete collection</p>
            </div>
            <div class="category-card" onclick="location.href='products.php'">
                <div class="icon">🫧</div>
                <h3>Clear Lenses</h3>
                <p>Daily vision correction</p>
            </div>
            <div class="category-card" onclick="location.href='products.php'">
                <div class="icon">💗</div>
                <h3>Colored Lenses</h3>
                <p>Express your style</p>
            </div>
            <div class="category-card" onclick="location.href='products.php'">
                <div class="icon">🎀</div>
                <h3>Accessories</h3>
                <p>Cases & solutions</p>
            </div>
        </div>
    </div>

    <!-- Featured Products -->
    <div class="section">
        <h2 class="section-title">Featured Products🩷</h2>
        <p class="section-subtitle">"Our most popular lenses, loved by customers"</p>
        <div class="products-grid" id="featured-products">
            <?php if ($featuredProducts && $featuredProducts->num_rows > 0): ?>
                <?php while ($product = $featuredProducts->fetch_assoc()): ?>
                    <div class="product-card">
                        <img src="../../<?php echo $product['image']; ?>" alt="<?php echo htmlspecialchars($product['productName']); ?>">
                        <h4><?php echo htmlspecialchars($product['productName']); ?></h4>
                        <p style="color: #FF69B4; font-size: 12px; margin: 5px 0;">🏷️ <?php echo htmlspecialchars($product['brandName'] ?? 'No Brand'); ?></p>
                        <p class="price"><?php echo number_format($product['price']); ?> MMK</p>
                        <a href="add-to-cart.php?id=<?php echo $product['productID']; ?>" class="btn-add">Add to Cart 🛒</a>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </div>

<?php include 'includes/footer.php'; ?>