<?php
session_start();
require_once '../../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// ========== HANDLE REMOVE FROM CART ==========
if (isset($_GET['remove'])) {
    $id = intval($_GET['remove']);
    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $key => $item) {
            if ($item['id'] == $id) {
                unset($_SESSION['cart'][$key]);
                break;
            }
        }
        $_SESSION['cart'] = array_values($_SESSION['cart']);
    }
    header('Location: cart.php');
    exit();
}

// ========== HANDLE UPDATE QUANTITY ==========
if (isset($_POST['update']) && isset($_POST['id']) && isset($_POST['quantity'])) {
    $id = intval($_POST['id']);
    $quantity = intval($_POST['quantity']);
    
    // Check database for real stock
    $stmt = $conn->prepare("SELECT stock FROM products WHERE productID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $max_stock = $product ? $product['stock'] : 0;
    
    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['id'] == $id) {
                if ($quantity <= 0) {
                    // Remove item
                    $item = null;
                } else {
                    // If requested quantity is greater than stock, set to max stock
                    if ($quantity > $max_stock) {
                        $item['quantity'] = $max_stock; 
                    } else {
                        $item['quantity'] = $quantity;
                    }
                }
                break;
            }
        }
        $_SESSION['cart'] = array_filter($_SESSION['cart']);
        $_SESSION['cart'] = array_values($_SESSION['cart']);
    }
    header('Location: cart.php');
    exit();
}

// ========== GET CART ITEMS ==========
$cartItems = $_SESSION['cart'] ?? [];
$total = 0;
foreach ($cartItems as $item) {
    $total += $item['price'] * $item['quantity'];
}
?>
<?php include 'includes/header.php'; ?>

<div class="cart-container">
    <h2 class="cart-title">Your Shopping Cart🩷</h2>
    
    <?php if (empty($cartItems)): ?>
        <div class="empty-cart">
            <div class="empty-icon">🛒</div>
            <p>Your cart is empty</p>
            <p class="empty-sub">Looks like you haven't added any items yet</p>
            <a href="products.php" class="checkout-btn">Start Shopping →</a>
        </div>
    <?php else: ?>
        <table class="cart-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Subtotal</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cartItems as $item): ?>
                    <tr>
                        <td>
                            <div class="cart-product-info">
                                <img src="../../<?php echo $item['image'] ?? 'public/assets/images/placeholder.jpg'; ?>" 
                                     alt="<?php echo $item['name']; ?>" class="cart-product-image">
                                <span><?php echo $item['name']; ?></span>
                            </div>
                        </td>
                        <td><?php echo number_format($item['price']); ?> MMK</td>
                        <td>
                            <form method="POST" action="">
                                <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                <div class="qty-control">
                                    <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="0" class="qty-input">
                                </div>
                                <button type="submit" name="update" class="update-btn">Update</button>
                            </form>
                        </td>
                        <td><strong><?php echo number_format($item['price'] * $item['quantity']); ?> MMK</strong></td>
                        <td>
                            <a href="cart.php?remove=<?php echo $item['id']; ?>" class="remove-btn" onclick="return confirm('Remove this item?')">🗑️</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="cart-summary">
            <h3>Order Summary</h3>
            <p>Subtotal: <span><?php echo number_format($total); ?> MMK</span></p>
            <p>Shipping: Free</p>
            <hr>
            <p class="total">Total: <span><?php echo number_format($total); ?> MMK</span></p>
            <a href="checkout.php" class="checkout-btn">Proceed to Checkout →</a>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>