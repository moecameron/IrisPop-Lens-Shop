<?php
session_start();
require_once '../config/database.php';
require_once 'crud.php';

// ========== CHECK ADMIN LOGIN ==========
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../src/Views/login.php');
    exit();
}

// ========== AJAX HANDLER TO GET ORDER ITEMS ==========
if (isset($_GET['ajax']) && $_GET['ajax'] == 'get_order_items' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Get order details
    $orderStmt = $conn->prepare("SELECT o.*, u.name as customerName FROM orders o JOIN users u ON o.userID = u.userID WHERE o.orderID = ?");
    $orderStmt->bind_param("i", $id);
    $orderStmt->execute();
    $orderData = $orderStmt->get_result()->fetch_assoc();

    // Get items in this order
    $itemsStmt = $conn->prepare("
        SELECT oi.*, p.productName 
        FROM orderitems oi 
        JOIN products p ON oi.productID = p.productID 
        WHERE oi.orderID = ?
    ");
    $itemsStmt->bind_param("i", $id);
    $itemsStmt->execute();
    $itemsResult = $itemsStmt->get_result();
    
    $items = [];
    while ($row = $itemsResult->fetch_assoc()) {
        $items[] = $row;
    }

    header('Content-Type: application/json');
    echo json_encode(['order' => $orderData, 'items' => $items]);
    exit();
}


// ========== HANDLE UPDATE ORDER STATUS ==========
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $orderID = intval($_POST['orderID']);
    $status = $_POST['orderStatus'];
    
    $stmt = $conn->prepare("UPDATE orders SET orderStatus = ? WHERE orderID = ?");
    $stmt->bind_param("si", $status, $orderID);
    
    if ($stmt->execute()) {
        header("Location: orders.php?msg=Order status updated successfully");
    } else {
        header("Location: orders.php?error=Failed to update order status");
    }
    exit();
}

// ========== HANDLE DELETE ORDER ==========
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM orders WHERE orderID = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        header("Location: orders.php?msg=Order deleted successfully");
    } else {
        header("Location: orders.php?error=Failed to delete order");
    }
    exit();
}

// ========== GET ALL ORDERS (WITH SEARCH FILTER) ==========
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$sql = "
    SELECT o.*, u.name as customerName, u.email, u.phoneNumber 
    FROM orders o 
    JOIN users u ON o.userID = u.userID 
    WHERE 1=1
";

// If the user typed something in the search bar
if (!empty($search)) {
    // Sanitize the input to prevent SQL injection
    $searchSafe = $conn->real_escape_string($search);
    
    // Check if the search is a number (Order ID) or text (Name/Status)
    if (is_numeric($searchSafe)) {
        $sql .= " AND o.orderID = '$searchSafe'";
    } else {
        $sql .= " AND (u.name LIKE '%$searchSafe%' 
                       OR o.orderStatus LIKE '%$searchSafe%'
                       OR u.email LIKE '%$searchSafe%')";
    }
}

$sql .= " ORDER BY o.orderDate DESC";
$orders = $conn->query($sql);
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - Admin</title>
    <link rel="stylesheet" href="../public/assets/css/style.css">
    <link rel="stylesheet" href="../public/assets/css/category.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        
        .admin-sidebar {
        width: 280px;    /* slightly wider */
        min-width: 280px; /* prevent shrinking */
        background: #FF1493;
        padding: 30px 20px;
    }
    </style>

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
            <a href="orders.php" class="active">📦 Manage Orders</a>
            <a href="users.php">👤 Manage Users</a>
            <a href="reports.php">📈 Reports</a>
            <a href="../src/Views/logout.php">🚪 Logout</a>
            <a href="../src/Views/index.php">🚪 Back to Shop</a>
        </div>

        <div class="admin-content">
            <div class="page-header">
                <h1>📦 Manage Orders</h1>
                <span class="badge"><?php echo $orders->num_rows; ?> Orders</span>
            </div>

            <!-- ========== ORDER SEARCH BAR ========== -->
            <div class="search-container" style="margin-bottom: 20px;">
                <form method="GET" action="" style="display: flex; gap: 10px;">
                    <input type="text" name="search" placeholder="🔍︎ Search by Order ID, Customer Name, or Status..." 
                        value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" 
                        style="flex: 1; padding: 12px 20px; border: 2px solid #FFB6C1; border-radius: 50px; font-family: 'Poppins', sans-serif; font-size: 14px;">
                    <button type="submit" class="search-btn" style="background: #FF1493; color: white; padding: 12px 25px; border: none; border-radius: 50px; font-weight: 600; cursor: pointer;">Search</button>
                    <?php if (isset($_GET['search']) && !empty($_GET['search'])): ?>
                        <a href="orders.php" class="clear-btn" style="background: #eee; color: #333; padding: 12px 25px; border-radius: 50px; text-decoration: none; font-weight: 500;">✕ Clear</a>
                    <?php endif; ?>
                </form>
            </div>

            <?php if (isset($_GET['msg'])): ?>
                <div class="message success" id="successMessage">
                    ✅ <?php echo htmlspecialchars($_GET['msg']); ?>
                </div>
                <script>
                    setTimeout(function() {
                        var msg = document.getElementById('successMessage');
                        if (msg) msg.style.display = 'none';
                    }, 3000);
                </script>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
                <div class="message error" id="errorMessage">
                    ❌ <?php echo htmlspecialchars($_GET['error']); ?>
                </div>
                <script>
                    setTimeout(function() {
                        var msg = document.getElementById('errorMessage');
                        if (msg) msg.style.display = 'none';
                    }, 4000);
                </script>
            <?php endif; ?>

            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Address</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($orders && $orders->num_rows > 0): ?>
                        <?php while ($row = $orders->fetch_assoc()): 
                            $statusClass = 'status-' . strtolower($row['orderStatus']);
                        ?>
                            <tr>
                                <td><strong>#<?php echo $row['orderID']; ?></strong></td>
                                <td><strong><?php echo htmlspecialchars($row['customerName']); ?></strong></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['phoneNumber'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($row['shipping_address'] ?? '-'); ?></td>
                                <td><strong><?php echo number_format($row['totalAmount']); ?> MMK</strong></td>
                                <td>
                                    <span class="status-badge <?php echo $statusClass; ?>">
                                        <?php echo ucfirst($row['orderStatus']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($row['orderDate'])); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="edit-btn" style="background: #FF1493;" onclick="openOrderModal(<?php echo $row['orderID']; ?>)">👁️ View</button>
                                        <button class="edit-btn" onclick="openModal(<?php echo $row['orderID']; ?>, '<?php echo $row['orderStatus']; ?>')">✏️ Edit</button>
                                        <a href="orders.php?delete=<?php echo $row['orderID']; ?>" class="delete-btn" onclick="return confirm('Are you sure?')">🗑️ Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="no-data">No orders found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Update Status Modal -->
    <div class="modal-overlay" id="statusModal">
        <div class="modal">
            <h2>✏️ Update Order Status</h2>
            <form method="POST" action="">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="orderID" id="orderID" value="">

                <div class="input-group">
                    <label>Order Status</label>
                    <select name="orderStatus" id="orderStatus">
                        <option value="pending">Pending</option>
                        <option value="processing">Processing</option>
                        <option value="shipped">Shipped</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>

                <div class="btn-group">
                    <button type="submit" class="btn-save">💾 Update Status</button>
                    <button type="button" class="btn-cancel" onclick="closeModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    <!-- Overlay (dark background when sidebar open) -->
    <div class="admin-sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

        <!-- View Order Items Modal -->
    <div class="modal-overlay" id="orderItemsModal">
        <div class="modal" style="max-width: 600px;">
            <h2>🛍️ Order Details</h2>
            <p style="margin-bottom: 15px; color: #888;">
                Customer: <strong id="modalCustomerName" style="color: #333;"></strong>
            </p>
            
            <table class="admin-table" style="margin-bottom: 20px;">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody id="modalOrderItems">
                    <!-- Items will be inserted here by JavaScript -->
                </tbody>
            </table>

            <div class="btn-group">
                <button type="button" class="btn-cancel" onclick="closeOrderModal()">Close</button>
            </div>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            document.querySelector('.admin-sidebar').classList.toggle('active');
            document.getElementById('sidebarOverlay').classList.toggle('active');
        }

        function openModal(orderID, currentStatus) {
            var modal = document.getElementById('statusModal');
            document.getElementById('orderID').value = orderID;
            document.getElementById('orderStatus').value = currentStatus;
            modal.classList.add('active');
        }

        function closeModal() {
            document.getElementById('statusModal').classList.remove('active');
        }

        document.getElementById('statusModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });


                // Function to open the Order Items Modal
        function openOrderModal(orderID) {
            var modal = document.getElementById('orderItemsModal');
            var itemsBody = document.getElementById('modalOrderItems');
            var customerNameSpan = document.getElementById('modalCustomerName');

            // Show loading state
            itemsBody.innerHTML = '<tr><td colspan="3" style="text-align:center;">Loading...</td></tr>';
            customerNameSpan.textContent = 'Loading...';
            
            // Open the modal
            modal.classList.add('active');

            // Fetch data from the server
            fetch('orders.php?ajax=get_order_items&id=' + orderID)
                .then(response => response.json())
                .then(data => {
                    customerNameSpan.textContent = data.order.customerName;
                    
                    let html = '';
                    data.items.forEach(item => {
                        html += `
                            <tr>
                                <td>${item.productName}</td>
                                <td>${item.quantity}</td>
                                <td>${parseFloat(item.subTotal).toLocaleString()} MMK</td>
                            </tr>
                        `;
                    });
                    
                    if (data.items.length === 0) {
                        html = '<tr><td colspan="3" style="text-align:center;">No items found.</td></tr>';
                    }
                    
                    itemsBody.innerHTML = html;
                })
                .catch(error => {
                    console.error('Error:', error);
                    itemsBody.innerHTML = '<tr><td colspan="3" style="text-align:center; color:red;">Error loading items.</td></tr>';
                });
        }

        // Function to close the modal
        function closeOrderModal() {
            document.getElementById('orderItemsModal').classList.remove('active');
        }

        // Close modal if clicking outside
        document.getElementById('orderItemsModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeOrderModal();
            }
        });
    </script>

</body>
</html>