<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once '../../config/database.php';

$userID = $_SESSION['user_id'];

// Get user orders
$stmt = $conn->prepare("
    SELECT * FROM orders 
    WHERE userID = ? 
    ORDER BY orderDate DESC
");
$stmt->bind_param("i", $userID);
$stmt->execute();
$orders = $stmt->get_result();
?>
<?php include 'includes/header.php'; ?>

<div class="orders-container">
    <h2 class="cart-title">My Order History🩷</h2>
    
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
    
    <?php if ($orders && $orders->num_rows > 0): ?>
        <?php while ($order = $orders->fetch_assoc()): ?>
            <?php
            // Get order items
            $itemStmt = $conn->prepare("
                SELECT oi.*, p.productName, p.image 
                FROM orderitems oi 
                JOIN products p ON oi.productID = p.productID 
                WHERE oi.orderID = ?
            ");
            $itemStmt->bind_param("i", $order['orderID']);
            $itemStmt->execute();
            $orderItems = $itemStmt->get_result();
            ?>
            <div class="order-card">
                <div class="order-header">
                    <span class="order-id">Order #<?php echo $order['orderID']; ?></span>
                    <span class="order-date"><?php echo date('d/m/Y', strtotime($order['orderDate'])); ?></span>
                    <span class="order-status status-<?php echo strtolower($order['orderStatus']); ?>">
                        <?php echo ucfirst($order['orderStatus']); ?>
                    </span>
                </div>
                <div class="order-items">
                    <?php while ($item = $orderItems->fetch_assoc()): ?>
                        <div class="order-item-row">
                            <span><?php echo htmlspecialchars($item['productName']); ?> x <?php echo $item['quantity']; ?></span>
                            <span><?php echo number_format($item['subTotal']); ?> MMK</span>
                        </div>
                    <?php endwhile; ?>
                </div>
                <div class="order-total">
                    <span>Total:</span>
                    <span><?php echo number_format($order['totalAmount']); ?> MMK</span>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="empty-cart">
            <div class="empty-icon">📦</div>
            <p>No orders yet</p>
            <p class="empty-sub">Looks like you haven't placed any orders</p>
            <a href="products.php" class="checkout-btn">Start Shopping →</a>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>