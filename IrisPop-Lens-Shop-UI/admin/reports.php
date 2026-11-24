<?php
session_start();
require_once '../config/database.php';
require_once 'crud.php';

// ========== CHECK ADMIN LOGIN ==========
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../src/Views/login.php');
    exit();
}

// ========== SALES REPORT ==========
$totalSales = $conn->query("SELECT SUM(totalAmount) as total FROM orders WHERE orderStatus != 'cancelled'")->fetch_assoc()['total'] ?? 0;
$totalOrders = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
$pendingOrders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE orderStatus = 'pending'")->fetch_assoc()['count'];
$deliveredOrders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE orderStatus = 'delivered'")->fetch_assoc()['count'];

// ========== SALES BY TIME PERIOD ==========
// Today's Sales
$todaySales = $conn->query("SELECT SUM(totalAmount) as total FROM orders WHERE DATE(orderDate) = CURDATE() AND orderStatus != 'cancelled'")->fetch_assoc()['total'] ?? 0;

// This Week's Sales
$weekSales = $conn->query("SELECT SUM(totalAmount) as total FROM orders WHERE YEARWEEK(orderDate, 1) = YEARWEEK(CURDATE(), 1) AND orderStatus != 'cancelled'")->fetch_assoc()['total'] ?? 0;

// This Month's Sales
$monthSales = $conn->query("SELECT SUM(totalAmount) as total FROM orders WHERE MONTH(orderDate) = MONTH(CURDATE()) AND YEAR(orderDate) = YEAR(CURDATE()) AND orderStatus != 'cancelled'")->fetch_assoc()['total'] ?? 0;

// This Year's Sales
$yearSales = $conn->query("SELECT SUM(totalAmount) as total FROM orders WHERE YEAR(orderDate) = YEAR(CURDATE()) AND orderStatus != 'cancelled'")->fetch_assoc()['total'] ?? 0;

// ========== BEST SELLING PRODUCTS ==========
$bestSellers = $conn->query("
    SELECT p.productName, p.image, 
           SUM(oi.quantity) as total_sold, 
           SUM(oi.subTotal) as total_revenue
    FROM orderitems oi
    JOIN products p ON oi.productID = p.productID
    JOIN orders o ON oi.orderID = o.orderID
    WHERE o.orderStatus != 'cancelled'
    GROUP BY p.productID
    ORDER BY total_sold DESC
    LIMIT 5
");

// ========== STOCK REPORT ==========
$lowStock = $conn->query("SELECT * FROM products WHERE stock <= 30 ORDER BY stock ASC");
$outOfStock = $conn->query("SELECT * FROM products WHERE stock <= 0");
$totalProducts = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];

// ========== CUSTOMER REPORT ==========
$totalCustomers = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'customer'")->fetch_assoc()['count'];
$totalAdmins = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'")->fetch_assoc()['count'];
$recentCustomers = $conn->query("SELECT * FROM users WHERE role = 'customer' ORDER BY created_at DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Admin</title>
    <link rel="stylesheet" href="../public/assets/css/style.css">
    <link rel="stylesheet" href="../public/assets/css/category.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<!-- Hamburger button (only shows on mobile) -->
    <button class="admin-menu-toggle" onclick="toggleSidebar()">☰</button>

    <div class="admin-container">
        <div class="admin-sidebar">
            <h2>IrisPop Admin</h2>
            <a href="dashboard.php">📊 Dashboard</a>
            <a href="adminproducts.php">👁️ Manage Products</a>
            <a href="category.php">📂 Manage Categories</a>
            <a href="orders.php">📦 Manage Orders</a>
            <a href="users.php">👤 Manage Users</a>
            <a href="reports.php" class="active">📈 Reports</a>
            <a href="../src/Views/logout.php">🚪 Logout</a>
            <a href="../src/Views/index.php">🚪 Back to Shop</a>
        </div>

        <div class="admin-content">
            <div class="page-header">
                <h1>📈 Reports</h1>
                <button class="export-btn" onclick="window.print()">🖨️ Export as PDF</button>
            </div>

            <!-- ========== SALES REPORT ========== -->
            <h2 class="report-section-title">💰 Sales Report</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo number_format($totalSales); ?></div>
                    <div class="stat-label">Total Sales (MMK)</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $totalOrders; ?></div>
                    <div class="stat-label">Total Orders</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $pendingOrders; ?></div>
                    <div class="stat-label">Pending Orders</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $deliveredOrders; ?></div>
                    <div class="stat-label">Delivered Orders</div>
                </div>
            </div>

            <!-- ========== SALES BY TIME PERIOD ========== -->
            <h3 style="color: #FF1493; margin-top: 30px; margin-bottom: 15px;">📅 Sales by Time Period</h3>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo number_format($todaySales); ?></div>
                    <div class="stat-label">Today's Sales (MMK)</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo number_format($weekSales); ?></div>
                    <div class="stat-label">This Week (MMK)</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo number_format($monthSales); ?></div>
                    <div class="stat-label">This Month (MMK)</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo number_format($yearSales); ?></div>
                    <div class="stat-label">This Year (MMK)</div>
                </div>
            </div>

            <!-- ========== BEST SELLING PRODUCTS ========== -->
            <h3 style="color: #FF1493; margin-top: 30px; margin-bottom: 15px;">🏆 Best Selling Products</h3>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Product</th>
                        <th>Total Sold</th>
                        <th>Total Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($bestSellers && $bestSellers->num_rows > 0): ?>
                        <?php $rank = 1; while ($row = $bestSellers->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <?php if ($rank == 1): ?>
                                        🥇
                                    <?php elseif ($rank == 2): ?>
                                        🥈
                                    <?php elseif ($rank == 3): ?>
                                        🥉
                                    <?php else: ?>
                                        #<?php echo $rank; ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <img src="../<?php echo htmlspecialchars($row['image']); ?>" 
                                             alt="<?php echo htmlspecialchars($row['productName']); ?>" 
                                             style="width: 40px; height: 40px; border-radius: 8px; object-fit: cover;">
                                        <strong><?php echo htmlspecialchars($row['productName']); ?></strong>
                                    </div>
                                </td>
                                <td><strong><?php echo $row['total_sold']; ?> units</strong></td>
                                <td><strong><?php echo number_format($row['total_revenue']); ?> MMK</strong></td>
                            </tr>
                        <?php $rank++; endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="no-data">No sales data yet</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- ========== STOCK REPORT ========== -->
            <h2 class="report-section-title">📦 Stock Report</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $totalProducts; ?></div>
                    <div class="stat-label">Total Products</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $lowStock->num_rows; ?></div>
                    <div class="stat-label">Low Stock Items</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $outOfStock->num_rows; ?></div>
                    <div class="stat-label">Out of Stock</div>
                </div>
            </div>

            <?php if ($lowStock->num_rows > 0): ?>
                <table class="admin-table" style="margin-top: 20px;">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Stock</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $lowStock->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['productName']); ?></td>
                                <td><?php echo $row['stock']; ?></td>
                                <td>
                                    <?php if ($row['stock'] <= 0): ?>
                                        <span class="status-badge status-cancelled">Out of Stock</span>
                                    <?php else: ?>
                                        <span class="status-badge status-pending">Low Stock</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <!-- ========== CUSTOMER REPORT ========== -->
            <h2 class="report-section-title">👤 Customer Report</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $totalCustomers; ?></div>
                    <div class="stat-label">Total Customers</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $totalAdmins; ?></div>
                    <div class="stat-label">Total Admins</div>
                </div>
            </div>

            <h3 style="color: #FF1493; margin-top: 30px; margin-bottom: 15px;">Recent Customers</h3>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Joined</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($recentCustomers && $recentCustomers->num_rows > 0): ?>
                        <?php while ($row = $recentCustomers->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['phoneNumber'] ?? '-'); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($row['created_at'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="no-data">No customers found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <!-- Overlay (dark background when sidebar open) -->
    <div class="admin-sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <!-- Toggle script -->
    <script>
    function toggleSidebar() {
        document.querySelector('.admin-sidebar').classList.toggle('active');
        document.getElementById('sidebarOverlay').classList.toggle('active');
    }
    </script>

</body>
</html>