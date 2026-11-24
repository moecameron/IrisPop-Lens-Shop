<?php
session_start();
require_once '../../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Check if cart is empty
if (empty($_SESSION['cart'])) {
    header('Location: products.php');
    exit();
}

$userID = $_SESSION['user_id'];
$cartItems = $_SESSION['cart'];

// Get shipping info
$fullname = trim($_POST['fullname']);
$email = trim($_POST['email']);
$phone = trim($_POST['phone']);

// Validate phone number (only digits allowed)
if (!preg_match('/^[0-9]{7,15}$/', $phone)) {
    // If not valid, go back to checkout with an error
    header('Location: checkout.php?error=Invalid phone number. Please enter digits only.');
    exit();
}
$address = trim($_POST['address']);
$notes = trim($_POST['notes'] ?? '');

// Calculate total
$total = 0;
foreach ($cartItems as $item) {
    $total += $item['price'] * $item['quantity'];
}

//  SAVE ORDER TO DATABASE 
try {
    $conn->begin_transaction();

    // Insert into orders table
    $stmt = $conn->prepare("INSERT INTO orders (userID, totalAmount, shipping_address, phone, orderStatus) VALUES (?, ?, ?, ?, 'pending')");
    $stmt->bind_param("idss", $userID, $total, $address, $phone);
    $stmt->execute();
    $orderID = $conn->insert_id;

    // Insert into orderitems table AND reduce product stock
    $stmt = $conn->prepare("INSERT INTO orderitems (orderID, productID, quantity, subTotal) VALUES (?, ?, ?, ?)");
    $stockStmt = $conn->prepare("UPDATE products SET stock = GREATEST(stock - ?, 0) WHERE productID = ?");

    foreach ($cartItems as $item) {
        $subTotal = $item['price'] * $item['quantity'];
        $stmt->bind_param("iiid", $orderID, $item['id'], $item['quantity'], $subTotal);
        $stmt->execute();

        // Reduce stock for this product
        $stockStmt->bind_param("ii", $item['quantity'], $item['id']);
        $stockStmt->execute();
    }
    $conn->commit();

    // Clear cart
    $_SESSION['cart'] = [];

    // Redirect to order history
    header('Location: order-history.php?msg=Order placed successfully');
    exit();

} catch (Exception $e) {
    $conn->rollback();
    header('Location: checkout.php?error=Failed to place order');
    exit();
}
?>