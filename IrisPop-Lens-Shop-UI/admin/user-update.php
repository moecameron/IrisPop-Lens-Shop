<?php
session_start();
require_once '../config/database.php';
require_once 'crud.php';

// ========== CHECK ADMIN LOGIN ==========
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../src/Views/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['userID']);
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $role = $_POST['role'];

    if (updateUser($conn, $id, $name, $email, $phone, $address, $role)) {
        header("Location: users.php?msg=User updated successfully");
    } else {
        header("Location: users.php?error=Failed to update user");
    }
    exit();
}
?>