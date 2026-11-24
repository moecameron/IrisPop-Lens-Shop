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

// Get cart items from session
$cartItems = $_SESSION['cart'] ?? [];
$total = 0;
foreach ($cartItems as $item) {
    $total += $item['price'] * $item['quantity'];
}

// If cart is empty, redirect to products
if (empty($cartItems)) {
    header('Location: products.php');
    exit();
}
?>
<?php include 'includes/header.php'; ?>

<div class="checkout-container">
    <div class="checkout-section">
        <h3>Shipping Information</h3>
        <form method="POST" action="place-order.php">
            <div class="form-row">
                <label>Full Name</label>
                <input type="text" name="fullname" required value="<?php echo htmlspecialchars($_SESSION['name'] ?? ''); ?>">
            </div>
            <div class="form-row">
                <label>Email Address</label>
                <input type="email" name="email" required value="<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>">
            </div>
            <div class="form-row">
                <label>Phone Number</label>
                <input type="tel" name="phone" required pattern="[0-9]{7,15}" title="Please enter a valid phone number (7 to 15 digits only)">
            </div>
            <div class="form-row">
                <label>Delivery Address</label>
                <textarea name="address" rows="3" required></textarea>
            </div>
            <div class="form-row">
                <label>Special Notes (Optional)</label>
                <textarea name="notes" rows="2" placeholder="Any special instructions..."></textarea>
            </div>
            <button type="submit" class="place-order-btn">Place Order →</button>
        </form>
    </div>

    <div class="checkout-section">
        <h3>Order Summary</h3>
        <div class="order-summary">
            <?php foreach ($cartItems as $item): ?>
                <div class="order-item">
                    <span><?php echo htmlspecialchars($item['name']); ?> x <?php echo $item['quantity']; ?></span>
                    <span><?php echo number_format($item['price'] * $item['quantity']); ?> MMK</span>
                </div>
            <?php endforeach; ?>
            <div class="order-total">
                <span>Total:</span>
                <span><?php echo number_format($total); ?> MMK</span>
            </div>
            <p style="margin-top: 15px; font-size: 12px; color: #888;">* Free shipping on all orders</p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>