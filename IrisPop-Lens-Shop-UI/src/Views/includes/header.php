<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get cart count from session
$cartCount = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cartCount += $item['quantity'];
    }
}

// Get current page for active link
$currentPage = basename($_SERVER['PHP_SELF']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IrisPop Lens Shop - Online Contact Lenses Store</title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

</head>
<body>

    <div class="header">
        <div class="logo">
            <h1>🤍 IrisPop Lens Shop</h1>
            <p>Make Your Iris Pop 🤍</p>
        </div>
        <div class="nav">
            <a href="index.php" class="<?php echo $currentPage == 'index.php' ? 'active' : ''; ?>">Home</a>
            <a href="products.php" class="<?php echo $currentPage == 'products.php' ? 'active' : ''; ?>">Products</a>
            <a href="order-history.php" class="<?php echo $currentPage == 'order-history.php' ? 'active' : ''; ?>">My Orders</a>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php" class="<?php echo $currentPage == 'login.php' ? 'active' : ''; ?>">Login</a>
                <a href="register.php" class="<?php echo $currentPage == 'register.php' ? 'active' : ''; ?>">Register</a>
            <?php endif; ?>
            
            <a href="cart.php" class="cart-icon <?php echo $currentPage == 'cart.php' ? 'active' : ''; ?>">
                Cart 🛒 <span class="cart-count"><?php echo $cartCount; ?></span>
            </a>
        </div>
    </div>