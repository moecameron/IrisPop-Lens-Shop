<?php
session_start();
require_once '../config/database.php';
require_once 'crud.php';


// ========== CHECK ADMIN LOGIN ==========
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../src/Views/login.php');
    exit();
}

// ========== AJAX HANDLER ==========
if (isset($_GET['ajax']) && $_GET['ajax'] == 'get_category' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $category = getCategoryById($conn, $id);
    header('Content-Type: application/json');
    if ($category) {
        echo json_encode($category);
    } else {
        echo json_encode(['error' => 'Category not found']);
    }
    exit();
}

// ========== HANDLE POST REQUESTS ==========
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $name = trim($_POST['categoryName']);
        $description = trim($_POST['description']);

        if ($_POST['action'] === 'add' && !empty($name)) {
            // CHECK IF CATEGORY ALREADY EXISTS
            $checkSql = "SELECT categoryID FROM categories WHERE categoryName = ?";
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->bind_param("s", $name);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();

            if ($checkResult->num_rows > 0) {
                header("Location: category.php?error=Category '" . urlencode($name) . "' already exists!");
                exit();
            } else {
                addCategory($conn, $name, $description);
                header("Location: category.php?msg=Category added successfully");
                exit();
            }
        } 
        else if ($_POST['action'] === 'update' && !empty($name) && isset($_POST['categoryID'])) {
            $id = intval($_POST['categoryID']);
            
            $checkSql = "SELECT categoryID FROM categories WHERE categoryName = ? AND categoryID != ?";
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->bind_param("si", $name, $id);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();

            if ($checkResult->num_rows > 0) {
                header("Location: category.php?error=Category '" . urlencode($name) . "' already exists!");
                exit();
            } else {
                updateCategory($conn, $id, $name, $description);
                header("Location: category.php?msg=Category updated successfully");
                exit();
            }
        }
    }
}

// ========== HANDLE DELETE ==========
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    deleteCategory($conn, $id);
    header("Location: category.php?msg=Category deleted successfully");
    exit();
}

// ========== GET ALL CATEGORIES ==========
$categories = getAllCategories($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - Admin</title>
    <link rel="stylesheet" href="../public/assets/css/category.css">
    <link rel="stylesheet" href="../public/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<!-- Hamburger button (only shows on mobile) -->
    <button class="admin-menu-toggle" onclick="toggleSidebar()">☰</button>

    <div class="admin-container">
        <div class="admin-sidebar">
            <h2>IrisPop Admin</h2>
            <a href="dashboard.php">📊 Dashboard</a>
            <a href="adminproducts.php">👁️ Manage Products</a>
            <a href="category.php" class="active">📂 Manage Categories</a>
            <a href="orders.php">📦 Manage Orders</a>
            <a href="users.php">👤 Manage Users</a>
            <a href="reports.php">📈 Reports</a>
            <a href="../src/Views/index.php">🚪 Back to Shop</a>
        </div>

        <div class="admin-content">
            <div class="page-header">
                <h1>📂 Manage Categories</h1>
                <button class="add-btn" onclick="openModal('add')">+ Add New Category</button>
            </div>

            <!-- ===== SUCCESS MESSAGE ===== -->
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

            <!-- ===== ERROR MESSAGE ===== -->
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
                        <th>Category Name</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($categories && $categories->num_rows > 0): ?>
                        <?php while ($row = $categories->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['categoryID']; ?></td>
                                <td><strong><?php echo htmlspecialchars($row['categoryName']); ?></strong></td>
                                <td><?php echo htmlspecialchars($row['description'] ?? '-'); ?></td>
                                <td>
                                    <button class="edit-btn" onclick="openModal('edit', <?php echo $row['categoryID']; ?>)">✏️ Edit</button>
                                    <a href="category.php?delete=<?php echo $row['categoryID']; ?>" class="delete-btn" onclick="return confirm('Are you sure?')">🗑️ Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="no-data">No categories found. Add your first category!</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ===== MODAL ===== -->
    <div class="modal-overlay" id="categoryModal">
        <div class="modal">
            <h2 id="modalTitle">Add New Category</h2>
            <form method="POST" action="">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="categoryID" id="categoryID" value="">

                <div class="input-group">
                    <label>Category Name *</label>
                    <input type="text" name="categoryName" id="categoryName" placeholder="e.g., Colored Lenses" required>
                </div>

                <div class="input-group">
                    <label>Description</label>
                    <textarea name="description" id="categoryDescription" rows="3" placeholder="Brief description"></textarea>
                </div>

                <div class="btn-group">
                    <button type="submit" class="btn-save" id="saveBtn">💾 Save Category</button>
                    <button type="button" class="btn-cancel" onclick="closeModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal(action, id = null) {
            var modal = document.getElementById('categoryModal');
            var title = document.getElementById('modalTitle');
            var formAction = document.getElementById('formAction');
            var categoryID = document.getElementById('categoryID');
            var nameInput = document.getElementById('categoryName');
            var descInput = document.getElementById('categoryDescription');
            var saveBtn = document.getElementById('saveBtn');

            if (action === 'add') {
                title.textContent = '➕ Add New Category';
                formAction.value = 'add';
                categoryID.value = '';
                nameInput.value = '';
                descInput.value = '';
                saveBtn.textContent = '💾 Save Category';
                modal.classList.add('active');
            } 
            else if (action === 'edit' && id) {
                title.textContent = '✏️ Edit Category';
                formAction.value = 'update';
                categoryID.value = id;
                saveBtn.textContent = '💾 Update Category';
                modal.classList.add('active');

                fetch('category.php?ajax=get_category&id=' + id)
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            alert(data.error);
                        } else {
                            nameInput.value = data.categoryName || '';
                            descInput.value = data.description || '';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error fetching category data');
                    });
            }
        }

        function closeModal() {
            var modal = document.getElementById('categoryModal');
            modal.classList.remove('active');
        }

        document.getElementById('categoryModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>

</body>
</html>