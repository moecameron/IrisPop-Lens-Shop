<?php
session_start();
require_once '../config/database.php';
require_once 'crud.php';

// ========== CHECK ADMIN LOGIN ==========
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../src/Views/login.php');
    exit();
}

// ========== HANDLE AJAX GET PRODUCT ==========
if (isset($_GET['ajax']) && $_GET['ajax'] == 'get_product' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $product = getProductById($conn, $id);
    header('Content-Type: application/json');
    if ($product) {
        echo json_encode($product);
    } else {
        echo json_encode(['error' => 'Product not found']);
    }
    exit();
}

// ========== HANDLE POST (ADD/UPDATE) ==========
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $name = trim($_POST['productName']);
    $brandID = !empty($_POST['brandID']) ? intval($_POST['brandID']) : null;
    $categoryID = !empty($_POST['categoryID']) ? intval($_POST['categoryID']) : null;
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock'] ?? 0);
    $description = trim($_POST['description']);

    // Handle image upload
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $targetDir = '../public/assets/images/';
        $image = 'public/assets/images/' . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $targetDir . basename($_FILES['image']['name']));
    }

    if ($action === 'add') {
        // CHECK IF PRODUCT ALREADY EXISTS
        $checkSql = "SELECT productID FROM products WHERE productName = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("s", $name);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows > 0) {
            header("Location: adminproducts.php?error=Product '" . urlencode($name) . "' already exists!");
            exit();
        } else {
            addProduct($conn, $name, $brandID, $categoryID, $price, $stock, $description, $image);
            header("Location: adminproducts.php?msg=Product added successfully");
            exit();
        }
    } else if ($action === 'update') {
        $id = intval($_POST['productID']);
        
        // CHECK IF PRODUCT EXISTS (excluding current product)
        $checkSql = "SELECT productID FROM products WHERE productName = ? AND productID != ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("si", $name, $id);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows > 0) {
            header("Location: adminproducts.php?error=Product '" . urlencode($name) . "' already exists!");
            exit();
        } else {
            if (empty($image)) {
                $existing = getProductById($conn, $id);
                $image = $existing['image'] ?? '';
            }
            updateProduct($conn, $id, $name, $brandID, $categoryID, $price, $stock, $description, $image);
            header("Location: adminproducts.php?msg=Product updated successfully");
            exit();
        }
    }
}



// ========== HANDLE DELETE ==========
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    deleteProduct($conn, $id);
    header("Location: adminproducts.php?msg=Product deleted successfully");
    exit();
}

// ========== GET ALL PRODUCTS ==========
$products = getAllProducts($conn);
$brands = getAllBrands($conn);
$categories = getAllCategories($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - Admin</title>
    <link rel="stylesheet" href="../public/assets/css/style.css">
    <link rel="stylesheet" href="../public/assets/css/category.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
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
            min-width: 60px;
        }

        .action-buttons .edit-btn {
            background: #FF69B4;
            color: white;
        }

        .action-buttons .edit-btn:hover {
            background: #FF1493;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 20, 147, 0.3);
        }

        .action-buttons .delete-btn {
            background: #C71585;
            color: white;
        }

        .action-buttons .delete-btn:hover {
            background: #8B0A5B;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(199, 21, 133, 0.3);
        }

        /* ===== TABLE ACTION COLUMN ===== */
        .admin-table td:last-child {
            min-width: 140px;
        }

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

        .add-btn {
            background: #FF1493;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-size: 15px;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
        }

        .add-btn:hover {
            background: #FF69B4;
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

        .admin-table {
            width: 100%;
            background: white;
            border-radius: 16px;
            overflow: hidden;
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
        }

        .admin-table .edit-btn {
            background: #FF69B4;
            color: white;
            border: none;
            padding: 6px 14px;
            border-radius: 8px;
            cursor: pointer;
        }

        .admin-table .edit-btn:hover {
            background: #FF1493;
        }

        .admin-table .delete-btn {
            background: #C71585;
            color: white;
            border: none;
            padding: 6px 14px;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
        }

        .admin-table .delete-btn:hover {
            background: #8B0A5B;
        }

        .no-data {
            text-align: center;
            padding: 40px;
            color: #888;
        }

        .product-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 8px;
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
            max-width: 550px;
            width: 90%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            animation: slideDown 0.3s ease;
            max-height: 90vh;
            overflow-y: auto;
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
        .modal .input-group select,
        .modal .input-group textarea {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #FFB6C1;
            border-radius: 12px;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
        }
        
        .modal .input-group input:focus,
        .modal .input-group select:focus,
        .modal .input-group textarea:focus {
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
            <a href="adminproducts.php" class="active">👁️ Manage Products</a>
            <a href="category.php">📂 Manage Categories</a>
            <a href="orders.php">📦 Manage Orders</a>
            <a href="users.php">👤 Manage Users</a>
            <a href="reports.php">📈 Reports</a>
            <a href="../src/Views/logout.php">🚪 Logout</a>
            <a href="../src/Views/index.php">🚪 Back to Shop</a>
        </div>

        <div class="admin-content">
            <div class="page-header">
                <h1>Manage Products</h1>
                <button class="add-btn" onclick="openModal('add')">+ Add New Product</button>
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
                        <th>Image</th>
                        <th>Product Name</th>
                        <th>Brand</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($products && $products->num_rows > 0): ?>
                        <?php while ($row = $products->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['productID']; ?></td>
                                <td>
                                    <?php if ($row['image']): ?>
                                        <img src="../<?php echo $row['image']; ?>" alt="<?php echo $row['productName']; ?>" class="product-image">
                                    <?php else: ?>
                                        <span style="color:#888;">No image</span>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?php echo htmlspecialchars($row['productName']); ?></strong></td>
                                <td><?php echo htmlspecialchars($row['brandName'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($row['categoryName'] ?? '-'); ?></td>
                                <td><?php echo number_format($row['price']); ?> MMK</td>
                                <td><?php echo $row['stock']; ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="edit-btn" onclick="openModal('edit', <?php echo $row['productID']; ?>)">✏️ Edit</button>
                                        <a href="adminproducts.php?delete=<?php echo $row['productID']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this product?')">🗑️ Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="no-data">No products found. Add your first product!</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add/Edit Modal -->
    <div class="modal-overlay" id="productModal">
        <div class="modal">
            <h2 id="modalTitle">Add New Product</h2>
            <form method="POST" action="" enctype="multipart/form-data">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="productID" id="productID" value="">

                <div class="input-group">
                    <label>Product Name *</label>
                    <input type="text" name="productName" id="productName" placeholder="e.g., Korean Natural Brown" required>
                </div>

                <div class="input-group">
                    <label>Brand</label>
                    <select name="brandID" id="brandID">
                        <option value="">Select Brand</option>
                        <?php while ($brand = $brands->fetch_assoc()): ?>
                            <option value="<?php echo $brand['brandID']; ?>"><?php echo htmlspecialchars($brand['brandName']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="input-group">
                    <label>Category</label>
                    <select name="categoryID" id="categoryID">
                        <option value="">Select Category</option>
                        <?php while ($cat = $categories->fetch_assoc()): ?>
                            <option value="<?php echo $cat['categoryID']; ?>"><?php echo htmlspecialchars($cat['categoryName']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="input-group">
                    <label>Price *</label>
                    <input type="number" name="price" id="price" placeholder="e.g., 25000" required>
                </div>

                <div class="input-group">
                    <label>Stock</label>
                    <input type="number" name="stock" id="stock" placeholder="e.g., 50">
                </div>

                <div class="input-group">
                    <label>Description</label>
                    <textarea name="description" id="description" rows="3" placeholder="Product description"></textarea>
                </div>

                <div class="input-group">
                    <label>Product Image</label>
                    <input type="file" name="image" id="image" accept="image/*">
                </div>

                <div class="btn-group">
                    <button type="submit" class="btn-save" id="saveBtn">💾 Save Product</button>
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
        document.getElementById('sidebarOverlay').classList.toggle('active');}
    

    
        function openModal(action, id = null) {
            var modal = document.getElementById('productModal');
            var title = document.getElementById('modalTitle');
            var formAction = document.getElementById('formAction');
            var productID = document.getElementById('productID');
            var nameInput = document.getElementById('productName');
            var brandInput = document.getElementById('brandID');
            var categoryInput = document.getElementById('categoryID');
            var priceInput = document.getElementById('price');
            var stockInput = document.getElementById('stock');
            var descInput = document.getElementById('description');
            var saveBtn = document.getElementById('saveBtn');

            if (action === 'add') {
                title.textContent = '➕ Add New Product';
                formAction.value = 'add';
                productID.value = '';
                nameInput.value = '';
                brandInput.value = '';
                categoryInput.value = '';
                priceInput.value = '';
                stockInput.value = '';
                descInput.value = '';
                saveBtn.textContent = '💾 Save Product';
                modal.classList.add('active');
            } else if (action === 'edit' && id) {
                title.textContent = '✏️ Edit Product';
                formAction.value = 'update';
                productID.value = id;
                saveBtn.textContent = '💾 Update Product';
                modal.classList.add('active');

                fetch('adminproducts.php?ajax=get_product&id=' + id)
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            alert(data.error);
                        } else {
                            nameInput.value = data.productName || '';
                            brandInput.value = data.brandID || '';
                            categoryInput.value = data.categoryID || '';
                            priceInput.value = data.price || '';
                            stockInput.value = data.stock || '';
                            descInput.value = data.description || '';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error fetching product data');
                    });
            }
        }

        function closeModal() {
            document.getElementById('productModal').classList.remove('active');
        }

        document.getElementById('productModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>

</body>
</html>