<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get product ID and quantity
$productId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$quantity = isset($_GET['qty']) ? intval($_GET['qty']) : 1;

if ($productId <= 0) {
    header('Location: products.php');
    exit();
}

// Get product from database
require_once '../../config/database.php';
$stmt = $conn->prepare("SELECT productID, productName, price, image, stock FROM products WHERE productID = ?");
$stmt->bind_param("i", $productId);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    header('Location: products.php');
    exit();
}

// ========== CHECK STOCK (MOVED HERE) ==========
// Check if stock is available BEFORE adding to cart
if ($product['stock'] <= 0) {
    echo "<script>alert('Sorry, this product is out of stock.'); window.location.href='product-detail.php?id=$productId';</script>";
    exit();
}

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Add to cart
$found = false;
foreach ($_SESSION['cart'] as &$item) {
    if ($item['id'] == $productId) {
        // Check if new total quantity exceeds stock
        if (($item['quantity'] + $quantity) > $product['stock']) {
            $item['quantity'] = $product['stock']; // Limit to max stock
        } else {
            $item['quantity'] += $quantity;
        }
        $found = true;
        break;
    }
}

if (!$found) {
    $_SESSION['cart'][] = [
        'id' => $product['productID'],
        'name' => $product['productName'],
        'price' => $product['price'],
        'image' => $product['image'],
        'quantity' => $quantity
    ];
}

// Redirect back to previous page
$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'products.php';
header('Location: ' . $referer);
exit();
?>