<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../config/database.php';

// Get product ID from URL
$productID = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get product details
$product = null;
if ($productID > 0) {
    $stmt = $conn->prepare("
        SELECT p.*, b.brandName, c.categoryName 
        FROM products p 
        LEFT JOIN brands b ON p.brandID = b.brandID 
        LEFT JOIN categories c ON p.categoryID = c.categoryID 
        WHERE p.productID = ?
    ");
    $stmt->bind_param("i", $productID);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
}

// If product not found, redirect to products page
if (!$product) {
    header('Location: products.php');
    exit();
}

// Get related products (same category)
$relatedProducts = $conn->query("
    SELECT p.*, b.brandName 
    FROM products p 
    LEFT JOIN brands b ON p.brandID = b.brandID 
    WHERE p.categoryID = (SELECT categoryID FROM products WHERE productID = $productID) 
    AND p.productID != $productID 
    LIMIT 4
");
?>
<?php include 'includes/header.php'; ?>

<!-- Product Detail Section -->
<div class="product-detail-section">
    <div class="product-detail-container">
        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <a href="index.php">Home</a> / 
            <a href="products.php">Products</a> / 
            <span><?php echo htmlspecialchars($product['productName']); ?></span>
        </div>

        <div class="product-detail-wrapper">
            <!-- Product Image -->
            <div class="product-detail-image">
                <img src="../../<?php echo htmlspecialchars($product['image'] ?? 'public/assets/images/placeholder.jpg'); ?>" 
                     alt="<?php echo htmlspecialchars($product['productName']); ?>">
            </div>

            <!-- Product Info -->
            <div class="product-detail-info">
                <h1><?php echo htmlspecialchars($product['productName']); ?></h1>
                
                <div class="product-brand">
                    <span>Brand:</span> 
                    <strong><?php echo htmlspecialchars($product['brandName'] ?? 'No Brand'); ?></strong>
                </div>
                
                <div class="product-category">
                    <span>Category:</span> 
                    <strong><?php echo htmlspecialchars($product['categoryName'] ?? 'Uncategorized'); ?></strong>
                </div>
                
                <div class="product-price">
                    <?php echo number_format($product['price']); ?> MMK
                </div>
                
                <div class="product-stock">
                    <?php if ($product['stock'] > 0): ?>
                        <span class="in-stock">✅ In Stock (<?php echo $product['stock']; ?> available)</span>
                    <?php else: ?>
                        <span class="out-of-stock">❌ Out of Stock</span>
                    <?php endif; ?>
                </div>

                <div class="product-description">
                    <h3>Description</h3>
                    <p><?php echo nl2br(htmlspecialchars($product['description'] ?? 'No description available.')); ?></p>
                </div>

                <!-- Quantity and Add to Cart -->
                <div class="product-actions">
                    <div class="quantity-selector">
                        <label>Quantity:</label>
                        <div class="qty-control">
                            <button class="qty-btn" onclick="changeQuantity(-1)">-</button>
                            <input type="number" id="qtyInput" value="1" min="1" max="<?php echo $product['stock']; ?>">
                            <button class="qty-btn" onclick="changeQuantity(1)">+</button>
                        </div>
                    </div>
                    
                    <a href="add-to-cart.php?id=<?php echo $product['productID']; ?>&qty=" class="add-to-cart-btn" onclick="this.href=this.href+document.getElementById('qtyInput').value">
                        🛒 Add to Cart
                    </a>
                </div>
            </div>
        </div>

        <!-- Related Products -->
        <?php if ($relatedProducts && $relatedProducts->num_rows > 0): ?>
        <div class="related-products">
            <h2 class="section-title">Related Products</h2>
            <div class="products-grid">
                <?php while ($related = $relatedProducts->fetch_assoc()): ?>
                    <div class="product-card" onclick="location.href='product-detail.php?id=<?php echo $related['productID']; ?>'">
                        <img src="../../<?php echo htmlspecialchars($related['image'] ?? 'public/assets/images/placeholder.jpg'); ?>" 
                             alt="<?php echo htmlspecialchars($related['productName']); ?>">
                        <h4><?php echo htmlspecialchars($related['productName']); ?></h4>
                        <p style="color: #FF69B4; font-size: 12px;">🏷️ <?php echo htmlspecialchars($related['brandName'] ?? 'No Brand'); ?></p>
                        <p class="price"><?php echo number_format($related['price']); ?> MMK</p>
                        <button class="btn-add" onclick="event.stopPropagation(); addToCart(<?php echo $related['productID']; ?>)">Add to Cart 🛒</button>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
    .product-detail-section {
        padding: 40px 5%;
        background: #F8F9FA;
        min-height: 60vh;
    }

    .product-detail-container {
        max-width: 1200px;
        margin: 0 auto;
        background: white;
        padding: 40px;
        border-radius: 24px;
        box-shadow: 0 5px 30px rgba(0,0,0,0.05);
    }

    .breadcrumb {
        margin-bottom: 30px;
        color: #888;
        font-size: 14px;
    }

    .breadcrumb a {
        color: #FF1493;
        text-decoration: none;
    }

    .breadcrumb a:hover {
        text-decoration: underline;
    }

    .breadcrumb span {
        color: #333;
    }

    .product-detail-wrapper {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 50px;
    }

    .product-detail-image img {
        width: 100%;
        height: auto;
        border-radius: 20px;
        object-fit: cover;
        background: #FFF0F5;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    }

    .product-detail-info h1 {
        font-size: 32px;
        color: #1A1A1A;
        margin-bottom: 15px;
    }

    .product-detail-info .product-brand,
    .product-detail-info .product-category {
        font-size: 15px;
        color: #666;
        margin-bottom: 10px;
    }

    .product-detail-info .product-brand strong,
    .product-detail-info .product-category strong {
        color: #1A1A1A;
    }

    .product-detail-info .product-price {
        font-size: 32px;
        font-weight: bold;
        color: #FF1493;
        margin: 20px 0;
    }

    .product-detail-info .product-stock {
        margin-bottom: 20px;
    }

    .in-stock {
        color: #28a745;
        font-weight: 600;
    }

    .out-of-stock {
        color: #dc3545;
        font-weight: 600;
    }

    .product-description {
        margin: 20px 0;
        padding-top: 20px;
        border-top: 1px solid #eee;
    }

    .product-description h3 {
        color: #FF1493;
        margin-bottom: 10px;
        font-size: 18px;
    }

    .product-description p {
        color: #666;
        line-height: 1.7;
        font-size: 15px;
    }

    .product-actions {
        display: flex;
        align-items: center;
        gap: 20px;
        margin-top: 25px;
        flex-wrap: wrap;
    }

    .quantity-selector {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .quantity-selector label {
        font-weight: 500;
        color: #333;
    }

    .qty-control {
        display: flex;
        align-items: center;
        border: 1px solid #FFB6C1;
        border-radius: 12px;
        overflow: hidden;
    }

    .qty-btn {
        background: #FFF0F5;
        border: none;
        padding: 8px 16px;
        cursor: pointer;
        font-size: 18px;
        font-weight: bold;
        color: #FF1493;
        transition: 0.3s;
    }

    .qty-btn:hover {
        background: #FFB6C1;
    }

    .qty-control input {
        width: 60px;
        padding: 8px;
        text-align: center;
        border: none;
        border-left: 1px solid #FFB6C1;
        border-right: 1px solid #FFB6C1;
        font-size: 16px;
        font-family: 'Poppins', sans-serif;
    }

    .qty-control input:focus {
        outline: none;
    }

    .add-to-cart-btn {
        background: #FF1493;
        color: white;
        padding: 14px 40px;
        border: none;
        border-radius: 50px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        font-family: 'Poppins', sans-serif;
    }

    .add-to-cart-btn:hover {
        background: #FF69B4;
        transform: scale(1.02);
    }

    .related-products {
        margin-top: 50px;
        padding-top: 30px;
        border-top: 1px solid #eee;
    }

    .related-products .section-title {
        text-align: left;
        font-size: 24px;
        margin-bottom: 25px;
        color: #FF1493;
    }

    .product-card {
        cursor: pointer;
    }

    @media (max-width: 768px) {
        .product-detail-wrapper {
            grid-template-columns: 1fr;
        }
        .product-detail-info h1 {
            font-size: 26px;
        }
        .product-detail-container {
            padding: 20px;
        }
        .product-actions {
            flex-direction: column;
            align-items: stretch;
        }
        .add-to-cart-btn {
            width: 100%;
            text-align: center;
        }
    }
</style>

<script>
    function changeQuantity(change) {
        const input = document.getElementById('qtyInput');
        let value = parseInt(input.value) + change;
        if (value < 1) value = 1;
        if (value > <?php echo $product['stock']; ?>) value = <?php echo $product['stock']; ?>;
        input.value = value;
    }

    function addToCartDetail(productId) {
        const qty = parseInt(document.getElementById('qtyInput').value);
        if (typeof addToCart === 'function') {
            addToCart(productId);
        } else {
            alert('Product added to cart!');
        }
    }
</script>

<?php include 'includes/footer.php'; ?>