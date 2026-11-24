<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../config/database.php';

// Get filter category from URL
$categoryFilter = isset($_GET['category']) ? $_GET['category'] : 'all';

// Get search term from URL
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build SQL query with filters
$sql = "
    SELECT p.*, b.brandName, c.categoryName 
    FROM products p 
    LEFT JOIN brands b ON p.brandID = b.brandID 
    LEFT JOIN categories c ON p.categoryID = c.categoryID 
    WHERE 1=1
";

// Sanitise inputs (prevent SQL injection)
$categoryFilter = $conn->real_escape_string($categoryFilter);
$searchTerm = $conn->real_escape_string($searchTerm);

// Add category filter
if ($categoryFilter !== 'all' && !empty($categoryFilter)) {
    $sql .= " AND c.categoryName = '$categoryFilter'";
}

// Add search filter (checks product name, category name, AND brand name)
if (!empty($searchTerm)) {
    $sql .= " AND (p.productName LIKE '%$searchTerm%' 
                   OR c.categoryName LIKE '%$searchTerm%' 
                   OR b.brandName LIKE '%$searchTerm%')";
}

$sql .= " ORDER BY p.productID";

$products = $conn->query($sql);

// ========== FETCH DYNAMIC CATEGORIES FOR FILTERS ==========
// This fetches all categories you created in the Admin panel
$catQuery = $conn->query("SELECT * FROM categories ORDER BY categoryID ASC");
$allCategories = $catQuery->fetch_all(MYSQLI_ASSOC);
?>
<?php include 'includes/header.php'; ?>

<div class="section">
    <h2 class="section-title">All Products🩷</h2>
    <p class="section-subtitle">Browse our complete collection</p>
    
    <!-- Search Bar -->
    <div class="search-container">
        <form method="GET" action="">
            <input type="text" name="search" placeholder="🔍︎Search products (name, brand, or category) - e.g. brown, OLENS, colored" 
            value="<?php echo htmlspecialchars($searchTerm); ?>">
            <button type="submit" class="search-btn">🔍︎ Search</button>
            <?php if (!empty($searchTerm) || $categoryFilter !== 'all'): ?>
                <a href="products.php" class="clear-btn">✕ Clear</a>
            <?php endif; ?>
        </form>
    </div>
    
    <!-- Category Filters  -->
    <div class="categories">
        
        <!-- "All" Button -->
        <a href="products.php?category=all<?php echo !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : ''; ?>" 
           class="category-card <?php echo $categoryFilter == 'all' ? 'active' : ''; ?>">
            <div class="icon">📍</div>
            <h3>All</h3>
            <p>View all products</p>
        </a>

        <!-- Loop through Database Categories -->
        <?php foreach ($allCategories as $cat): ?>
            
            <?php 
                // Default icon and description for new categories
                $icon = '📦'; 
                $desc = 'Explore products'; 
                $catNameLower = strtolower($cat['categoryName']);

                // Check if it's one of your original 3 to keep the special icons
                if ($catNameLower == 'clear') {
                    $icon = '🫧'; $desc = 'Daily vision correction';
                } elseif ($catNameLower == 'colored') {
                    $icon = '💗'; $desc = 'Express your style';
                } elseif ($catNameLower == 'accessories') {
                    $icon = '🎀'; $desc = 'Cases & solutions';
                }
            ?>

            <a href="products.php?category=<?php echo urlencode($cat['categoryName']); ?><?php echo !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : ''; ?>" 
               class="category-card <?php echo $categoryFilter == $cat['categoryName'] ? 'active' : ''; ?>">
                <div class="icon"><?php echo $icon; ?></div>
                <h3><?php echo ucfirst(htmlspecialchars($cat['categoryName'])); ?></h3>
                <p><?php echo $desc; ?></p>
            </a>

        <?php endforeach; ?>
        
    </div>
    
    <!-- Show filter info -->
    <?php if (!empty($searchTerm)): ?>
        <p style="text-align:center; color:#888; margin-bottom:20px;">
            Showing results for: <strong>"<?php echo htmlspecialchars($searchTerm); ?>"</strong>
            <?php if ($categoryFilter !== 'all'): ?>
                in category: <strong><?php echo ucfirst($categoryFilter); ?></strong>
            <?php endif; ?>
            (<?php echo $products->num_rows; ?> products found)
        </p>
    <?php elseif ($categoryFilter !== 'all'): ?>
        <p style="text-align:center; color:#888; margin-bottom:20px;">
            Showing category: <strong><?php echo ucfirst($categoryFilter); ?></strong>
            (<?php echo $products->num_rows; ?> products found)
        </p>
    <?php endif; ?>
    
    <div class="products-grid" id="all-products">
        <?php if ($products && $products->num_rows > 0): ?>
            <?php while ($product = $products->fetch_assoc()): ?>
                <div class="product-card" onclick="location.href='product-detail.php?id=<?php echo $product['productID']; ?>'" data-category="<?php echo htmlspecialchars($product['categoryName'] ?? 'all'); ?>">
                    <img src="../../<?php echo htmlspecialchars($product['image'] ?? 'public/assets/images/placeholder.jpg'); ?>" 
                         alt="<?php echo htmlspecialchars($product['productName']); ?>">
                    <h4><?php echo htmlspecialchars($product['productName']); ?></h4>
                    <p style="color: #FF69B4; font-size: 12px; margin: 5px 0;">🏷️ <?php echo htmlspecialchars($product['brandName'] ?? 'No Brand'); ?></p>
                    <p class="price"><?php echo number_format($product['price']); ?> MMK</p>
                    <a href="add-to-cart.php?id=<?php echo $product['productID']; ?>" class="btn-add" onclick="event.stopPropagation();">Add to Cart 🛒</a>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="text-align:center; grid-column: 1 / -1; padding: 40px; color: #888;">
                No products found 
                <?php if (!empty($searchTerm)): ?>
                    matching "<strong><?php echo htmlspecialchars($searchTerm); ?></strong>"
                <?php endif; ?>
                <?php if ($categoryFilter !== 'all'): ?>
                    in category "<strong><?php echo ucfirst($categoryFilter); ?></strong>"
                <?php endif; ?>
            </p>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>