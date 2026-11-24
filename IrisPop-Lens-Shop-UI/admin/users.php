<?php
session_start();
require_once '../config/database.php';
require_once 'crud.php';

// ========== CHECK ADMIN LOGIN ==========
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../src/Views/login.php');
    exit();
}

// ========== HANDLE DELETE USER ==========
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM users WHERE userID = ? AND role != 'admin'");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        header("Location: users.php?msg=User deleted successfully");
    } else {
        header("Location: users.php?error=Failed to delete user");
    }
    exit();
}

// ========== GET ALL USERS ==========
$users = getAllUsers($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin</title>
    <link rel="stylesheet" href="../public/assets/css/style.css">
    <link rel="stylesheet" href="../public/assets/css/category.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .admin-content {
            padding: 30px;
            background: #F8F9FA;
            flex: 1;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .page-header h1 {
            color: #FF1493;
            font-size: 28px;
        }

        .badge {
            background: #FFF0F5;
            color: #FF1493;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }

        .message {
            padding: 12px 18px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .message.success {
            background: #E8F5E9;
            color: #2E7D32;
            border: 1px solid #A5D6A7;
        }

        .message.error {
            background: #FFEBEE;
            color: #C62828;
            border: 1px solid #EF9A9A;
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
        }

        .admin-table td {
            padding: 14px 16px;
            border-bottom: 1px solid #eee;
            color: #333;
        }

        .admin-table tr:hover {
            background: #FFF5F8;
        }

        .role-badge {
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }

        .role-admin {
            background: #FF1493;
            color: white;
        }

        .role-customer {
            background: #FF69B4;
            color: white;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
        }

        .action-buttons .edit-btn,
        .action-buttons .delete-btn {
            padding: 6px 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            min-width: 70px;
        }

        .action-buttons .edit-btn {
            background: #FF69B4;
            color: white;
        }

        .action-buttons .edit-btn:hover {
            background: #FF1493;
            transform: translateY(-2px);
        }

        .action-buttons .delete-btn {
            background: #C71585;
            color: white;
        }

        .action-buttons .delete-btn:hover {
            background: #8B0A5B;
            transform: translateY(-2px);
        }

        .no-data {
            text-align: center;
            padding: 40px;
            color: #888;
        }

        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 999;
            justify-content: center;
            align-items: center;
        }

        .modal-overlay.active {
            display: flex !important;
        }

        .modal {
            background: white;
            border-radius: 20px;
            padding: 35px;
            max-width: 450px;
            width: 90%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .modal h2 {
            color: #FF1493;
            margin-bottom: 20px;
        }

        .modal .input-group {
            margin-bottom: 18px;
            text-align: left;
        }

        .modal .input-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            font-size: 14px;
            color: #333;
        }

        .modal .input-group input,
        .modal .input-group select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #FFB6C1;
            border-radius: 12px;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
        }

        .modal .input-group input:focus,
        .modal .input-group select:focus {
            border-color: #FF1493;
            outline: none;
        }

        .modal .btn-group {
            display: flex;
            gap: 12px;
            margin-top: 10px;
        }

        .modal .btn-save {
            background: #FF1493;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            font-weight: 600;
            flex: 1;
        }

        .modal .btn-save:hover {
            background: #FF69B4;
        }

        .modal .btn-cancel {
            background: #e0e0e0;
            color: #333;
            padding: 12px 30px;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            font-weight: 600;
            flex: 1;
        }

        .modal .btn-cancel:hover {
            background: #ccc;
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
            <a href="orders.php">📦 Manage Orders</a>
            <a href="users.php" class="active">👤 Manage Users</a>
            <a href="reports.php">📈 Reports</a>
            <a href="../src/Views/logout.php">🚪 Logout</a>
            <a href="../src/Views/index.php">🚪 Back to Shop</a>
        </div>

        <div class="admin-content">
            <div class="page-header">
                <h1>👤 Manage Users</h1>
                <span class="badge"><?php echo $users->num_rows; ?> Users</span>
            </div>

            <?php if (isset($_GET['msg'])): ?>
                <div class="message success" id="successMessage">
                    ✅ <?php echo htmlspecialchars($_GET['msg']); ?>
                </div>
                <script>
                    setTimeout(function() {
                        var msg = document.getElementById('successMessage');
                        if (msg) {
                            msg.style.display = 'none';
                        }
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
                        if (msg) {
                            msg.style.display = 'none';
                        }
                    }, 4000);
                </script>
            <?php endif; ?>

            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Address</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($users && $users->num_rows > 0): ?>
                        <?php while ($row = $users->fetch_assoc()): ?>
                            <?php $roleClass = $row['role'] === 'admin' ? 'role-admin' : 'role-customer'; ?>
                            <tr>
                                <td><?php echo $row['userID']; ?></td>
                                <td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['phoneNumber'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($row['address'] ?? '-'); ?></td>
                                <td>
                                    <span class="role-badge <?php echo $roleClass; ?>">
                                        <?php echo ucfirst($row['role']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="edit-btn" onclick="openModal('<?php echo $row['userID']; ?>', '<?php echo htmlspecialchars($row['name']); ?>', '<?php echo htmlspecialchars($row['email']); ?>', '<?php echo htmlspecialchars($row['phoneNumber']); ?>', '<?php echo htmlspecialchars($row['address']); ?>', '<?php echo $row['role']; ?>')">✏️ Edit</button>
                                        <?php if ($row['role'] !== 'admin'): ?>
                                            <a href="users.php?delete=<?php echo $row['userID']; ?>" class="delete-btn" onclick="return confirm('Are you sure?')">🗑️ Delete</a>
                                        <?php else: ?>
                                            <span style="color:#888; font-size:12px;">Cannot delete</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="no-data">No users found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal-overlay" id="userModal">
        <div class="modal">
            <h2>✏️ Edit User</h2>
            <form method="POST" action="user-update.php">
                <input type="hidden" name="userID" id="userID" value="">

                <div class="input-group">
                    <label>Full Name</label>
                    <input type="text" name="name" id="userName" required>
                </div>

                <div class="input-group">
                    <label>Email</label>
                    <input type="email" name="email" id="userEmail" required>
                </div>

                <div class="input-group">
                    <label>Phone Number</label>
                    <input type="text" name="phone" id="userPhone">
                </div>

                <div class="input-group">
                    <label>Address</label>
                    <input type="text" name="address" id="userAddress">
                </div>

                <div class="input-group">
                    <label>Role</label>
                    <select name="role" id="userRole">
                        <option value="customer">Customer</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>

                <div class="btn-group">
                    <button type="submit" class="btn-save">💾 Update User</button>
                    <button type="button" class="btn-cancel" onclick="closeModal()">Cancel</button>
                </div>
            </form>
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
   
        function openModal(id, name, email, phone, address, role) {
            var modal = document.getElementById('userModal');
            document.getElementById('userID').value = id;
            document.getElementById('userName').value = name;
            document.getElementById('userEmail').value = email;
            document.getElementById('userPhone').value = phone || '';
            document.getElementById('userAddress').value = address || '';
            document.getElementById('userRole').value = role;
            modal.classList.add('active');
        }

        function closeModal() {
            document.getElementById('userModal').classList.remove('active');
        }

        document.getElementById('userModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>

</body>
</html>