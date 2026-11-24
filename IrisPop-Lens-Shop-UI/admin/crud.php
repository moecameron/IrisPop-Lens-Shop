<?php
// admin/crud.php
require_once dirname(__DIR__) . '/config/database.php';
    

// ========== CATEGORY FUNCTIONS ==========

function getAllCategories($conn) {
    $stmt = $conn->prepare("SELECT * FROM categories ORDER BY categoryName");
    $stmt->execute();
    return $stmt->get_result();
}

function getCategoryById($conn, $id) {
    $stmt = $conn->prepare("SELECT * FROM categories WHERE categoryID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function addCategory($conn, $name, $description) {
    $stmt = $conn->prepare("INSERT INTO categories (categoryName, description) VALUES (?, ?)");
    $stmt->bind_param("ss", $name, $description);
    return $stmt->execute();
}

function updateCategory($conn, $id, $name, $description) {
    $stmt = $conn->prepare("UPDATE categories SET categoryName = ?, description = ? WHERE categoryID = ?");
    $stmt->bind_param("ssi", $name, $description, $id);
    return $stmt->execute();
}

function deleteCategory($conn, $id) {
    $stmt = $conn->prepare("DELETE FROM categories WHERE categoryID = ?");
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

// ========== BRAND FUNCTIONS ==========

function getAllBrands($conn) {
    $stmt = $conn->prepare("SELECT * FROM brands ORDER BY brandName");
    $stmt->execute();
    return $stmt->get_result();
}

function getBrandById($conn, $id) {
    $stmt = $conn->prepare("SELECT * FROM brands WHERE brandID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function addBrand($conn, $name, $description) {
    $stmt = $conn->prepare("INSERT INTO brands (brandName, description) VALUES (?, ?)");
    $stmt->bind_param("ss", $name, $description);
    return $stmt->execute();
}

function updateBrand($conn, $id, $name, $description) {
    $stmt = $conn->prepare("UPDATE brands SET brandName = ?, description = ? WHERE brandID = ?");
    $stmt->bind_param("ssi", $name, $description, $id);
    return $stmt->execute();
}

function deleteBrand($conn, $id) {
    $stmt = $conn->prepare("DELETE FROM brands WHERE brandID = ?");
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

// ========== PRODUCT FUNCTIONS ==========

function getAllProducts($conn) {
    $stmt = $conn->prepare("SELECT p.*, b.brandName, c.categoryName 
                            FROM products p 
                            LEFT JOIN brands b ON p.brandID = b.brandID 
                            LEFT JOIN categories c ON p.categoryID = c.categoryID 
                            ORDER BY p.productID");
    $stmt->execute();
    return $stmt->get_result();
}

function getProductById($conn, $id) {
    $stmt = $conn->prepare("SELECT p.*, b.brandName, c.categoryName 
                            FROM products p 
                            LEFT JOIN brands b ON p.brandID = b.brandID 
                            LEFT JOIN categories c ON p.categoryID = c.categoryID 
                            WHERE p.productID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function addProduct($conn, $name, $brandID, $categoryID, $price, $stock, $description, $image) {
    $stmt = $conn->prepare("INSERT INTO products (productName, brandID, categoryID, price, stock, description, image) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("siidsss", $name, $brandID, $categoryID, $price, $stock, $description, $image);
    return $stmt->execute();
}

function updateProduct($conn, $id, $name, $brandID, $categoryID, $price, $stock, $description, $image) {
    $stmt = $conn->prepare("UPDATE products 
                            SET productName = ?, brandID = ?, categoryID = ?, price = ?, stock = ?, description = ?, image = ? 
                            WHERE productID = ?");
    $stmt->bind_param("siidsssi", $name, $brandID, $categoryID, $price, $stock, $description, $image, $id);
    return $stmt->execute();
}

function deleteProduct($conn, $id) {
    $stmt = $conn->prepare("DELETE FROM products WHERE productID = ?");
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}


function productExists($conn, $name, $excludeId = null) {
    $sql = "SELECT productID FROM products WHERE productName = ?";
    if ($excludeId) {
        $sql .= " AND productID != ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $name, $excludeId);
    } else {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $name);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

function categoryExists($conn, $name, $excludeId = null) {
    $sql = "SELECT categoryID FROM categories WHERE categoryName = ?";
    if ($excludeId) {
        $sql .= " AND categoryID != ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $name, $excludeId);
    } else {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $name);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

// ========== ORDER FUNCTIONS ==========

function getAllOrders($conn) {
    $sql = "SELECT o.*, u.name as customerName, u.email, u.phoneNumber 
            FROM orders o 
            JOIN users u ON o.userID = u.userID 
            ORDER BY o.orderDate DESC";
    $result = $conn->query($sql);
    return $result;
}

function getOrderById($conn, $id) {
    $stmt = $conn->prepare("SELECT o.*, u.name as customerName, u.email, u.phoneNumber 
                            FROM orders o 
                            JOIN users u ON o.userID = u.userID 
                            WHERE o.orderID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function updateOrderStatus($conn, $id, $status) {
    $stmt = $conn->prepare("UPDATE orders SET orderStatus = ? WHERE orderID = ?");
    $stmt->bind_param("si", $status, $id);
    return $stmt->execute();
}

function deleteOrder($conn, $id) {
    $stmt = $conn->prepare("DELETE FROM orders WHERE orderID = ?");
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

// ========== USER FUNCTIONS ==========

function getAllUsers($conn) {
    $result = $conn->query("SELECT * FROM users ORDER BY userID");
    return $result;
}

function getUserById($conn, $id) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE userID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function updateUser($conn, $id, $name, $email, $phone, $address, $role) {
    $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, phoneNumber = ?, address = ?, role = ? WHERE userID = ?");
    $stmt->bind_param("sssssi", $name, $email, $phone, $address, $role, $id);
    return $stmt->execute();
}

function deleteUser($conn, $id) {
    $stmt = $conn->prepare("DELETE FROM users WHERE userID = ?");
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}


?>