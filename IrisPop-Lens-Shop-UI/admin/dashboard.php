<?php
session_start();
require_once '../config/database.php';
require_once 'crud.php';


// ========== CHECK ADMIN LOGIN ==========
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../src/Views/login.php');
    exit();
}

// ========== GET COUNTS FROM DATABASE ==========
$userCount = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$productCount = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
$orderCount = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
$categoryCount = $conn->query("SELECT COUNT(*) as count FROM categories")->fetch_assoc()['count'];

// ========== GET TOTAL SALES ==========
$totalSalesResult = $conn->query("SELECT SUM(totalAmount) as total FROM orders WHERE orderStatus != 'cancelled'");
$totalSales = $totalSalesResult->fetch_assoc()['total'] ?? 0;

// ========== GET RECENT ORDERS ==========
$recentOrders = $conn->query("
    SELECT o.*, u.name as customerName 
    FROM orders o 
    JOIN users u ON o.userID = u.userID 
    ORDER BY o.orderDate DESC 
    LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - IrisPop Lens Shop</title>
    <link rel="stylesheet" href="../public/assets/css/style.css">
    <link rel="stylesheet" href="../public/assets/css/category.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .admin-content {
            padding: 30px;
            background: #F8F9FA;
            flex: 1;
            min-height: 100vh;
        }

        .admin-content h1 {
            color: #FF1493;
            margin-bottom: 30px;
            font-size: 28px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 16px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card .stat-number {
            color: #FF1493;
            font-size: 32px;
            font-weight: 700;
        }

        .stat-card .stat-label {
            color: #888;
            font-size: 14px;
            margin-top: 5px;
        }

        .stat-card .stat-icon {
            font-size: 28px;
            display: block;
            margin-bottom: 8px;
        }

        .section-title {
            color: #FF1493;
            margin-bottom: 20px;
            font-size: 20px;
            font-weight: 600;
        }

        .admin-table {
            width: 100%;
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .admin-table th {
            background: #FF1493;
            color: white;
            padding: 14px 16px;
            text-align: left;
            font-weight: 600;
        }

        .admin-table td {
            padding: 14px 16px;
            border-bottom: 1px solid #eee;
            color: #333;
        }

        .admin-table tr:hover {
            background: #FFF5F8;
        }

        .admin-table tr:last-child td {
            border-bottom: none;
        }

        .status-badge {
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }

        .status-pending {
            background: #FFF3CD;
            color: #856404;
        }

        .status-processing {
            background: #CCE5FF;
            color: #004085;
        }

        .status-shipped {
            background: #D1ECF1;
            color: #0C5460;
        }

        .status-delivered {
            background: #D4EDDA;
            color: #155724;
        }

        .status-cancelled {
            background: #F8D7DA;
            color: #721C24;
        }

        .edit-btn {
            background: #FF69B4;
            color: white;
            border: none;
            padding: 6px 14px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            transition: 0.3s;
        }

        .edit-btn:hover {
            background: #FF1493;
        }

        .no-data {
            text-align: center;
            padding: 30px;
            color: #888;
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .admin-table {
                font-size: 12px;
            }
            .admin-table th,
            .admin-table td {
                padding: 10px 12px;
            }
        }
    </style>
</head>
<body>

<!-- Hamburger button (only shows on mobile) -->
    <button class="admin-menu-toggle" onclick="toggleSidebar()">☰</button>

    <div class="admin-container">
        <div class="admin-sidebar">
            <h2>IrisPop Admin</h2>
            <p style="color: #FFB6C1; font-size: 12px; margin-bottom: 20px;">
                <?php 
                if (isset($_SESSION['email'])) {
                    echo '👑 ' . htmlspecialchars($_SESSION['email']);
                }
                ?>
            </p>
            <a href="dashboard.php" class="active">📊 Dashboard</a>
            <a href="adminproducts.php">🫧 Manage Products</a>
            <a href="category.php">📂 Manage Categories</a>
            <a href="orders.php">📦 Manage Orders</a>
            <a href="users.php">👤 Manage Users</a>
            <a href="reports.php">📈 Reports</a>
            <a href="../src/Views/logout.php">🚪 Logout</a>
            <a href="../src/Views/index.php">🤍 Back to Shop</a>
        </div>
        
        <div class="admin-content">
            <h1>📊 Dashboard</h1>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $userCount; ?></div>
                    <div class="stat-label">Total Users</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $productCount; ?></div>
                    <div class="stat-label">Total Products</div>
                </div>
                <div class="stat-card">
                    
                    <div class="stat-number"><?php echo $orderCount; ?></div>
                    <div class="stat-label">Total Orders</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo number_format($totalSales); ?></div>
                    <div class="stat-label">Total Sales (MMK)</div>
                </div>
            </div>

    
            <!-- Recent Orders -->
            <h3 class="section-title">📋 Recent Orders</h3>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($recentOrders && $recentOrders->num_rows > 0): ?>
                        <?php while ($row = $recentOrders->fetch_assoc()): 
                            $statusClass = 'status-' . strtolower($row['orderStatus']);
                        ?>
                            <tr>
                                <td><strong>#<?php echo $row['orderID']; ?></strong></td>
                                <td><?php echo htmlspecialchars($row['customerName']); ?></td>
                                <td><strong><?php echo number_format($row['totalAmount']); ?> MMK</strong></td>
                                <td>
                                    <span class="status-badge <?php echo $statusClass; ?>">
                                        <?php echo ucfirst($row['orderStatus']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($row['orderDate'])); ?></td>
                                <td>
                                    <a href="orders.php" class="edit-btn">View</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="no-data">No orders yet</td>
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