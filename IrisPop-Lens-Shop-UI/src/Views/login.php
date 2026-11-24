<?php
session_start();
require_once '../../config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        $sql = "SELECT userID, name, email, password, role FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['userID'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];

                // Redirect based on role
                if ($user['role'] === 'admin') {
                    header("Location: ../../admin/dashboard.php");
                    exit();
                } else {
                    header("Location: index.php");
                    exit();
                }
            } else {
                $error = 'Invalid password!';
            }
        } else {
            $error = 'Email not found!';
        }
    }
}
?>
<?php include 'includes/header.php'; ?>

<div class="auth-container">
    <div class="auth-card">
        <div class="logo">
            <h1>🤍 IrisPop Lens Shop</h1>
            <p>Make Your Iris Pop 🤍</p>
        </div>
        <h2>Welcome Back</h2>
        <p class="subtitle">Login to your account</p>

        <?php if (isset($_GET['msg'])): ?>
            <div style="background: #E8F5E9; color: #2E7D32; padding: 10px 15px; border-radius: 10px; margin-bottom: 15px; border: 1px solid #A5D6A7;">
                ✅ <?php echo htmlspecialchars($_GET['msg']); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div style="background: #FFE4E4; color: #FF1493; padding: 10px 15px; border-radius: 10px; margin-bottom: 15px; border: 1px solid #FFB6C1;">
                ❌ <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="input-group">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="Enter your email" required>
            </div>
            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Enter your password" required>
            </div>
            <button type="submit" class="btn-login">Login 💓→</button>
        </form>

        <div class="auth-link">
            Don't have an account? <a href="register.php">Register here</a>
        </div>
        <div class="back-home">
            <a href="index.php">← Back to Home</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>