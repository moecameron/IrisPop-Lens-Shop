<?php
require_once '../../config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $password = $_POST['password'];
    $confirm = $_POST['confirm'];

    if ($password !== $confirm) {
        $error = 'Passwords do not match!';
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = 'Email already exists!';
        } else {
            $sql = "INSERT INTO users (name, email, password, address, phoneNumber) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssss", $fullname, $email, $hashedPassword, $address, $phone);

            if ($stmt->execute()) {
                header("Location: login.php?msg=Registration successful");
                exit;
            } else {
                $error = 'Error: ' . $stmt->error;
            }
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
            <h2>Create Account</h2>
            <p class="subtitle">Join us for exclusive deals</p>

            <?php if (!empty($error)): ?>
                <div style="background: #FFE4E4; color: #FF1493; padding: 10px 15px; border-radius: 10px; margin-bottom: 15px; border: 1px solid #FFB6C1;">
                    ❌ <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form id="registerForm" action="register.php" method="POST">
                <div class="input-group">
                    <label>Full Name</label>
                    <input type="text" id="fullname" name="fullname" placeholder="Enter your full name" required value="<?php echo htmlspecialchars($_POST['fullname'] ?? ''); ?>">
                </div>
                <div class="input-group">
                    <label>Email Address</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>
                <div class="input-group">
                    <label>Phone Number</label>
                    <input type="tel" id="phone" name="phone" placeholder="Enter your phone number" required value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                </div>
                <div class="input-group">
                    <label>Delivery Address</label>
                    <input type="text" id="address" name="address" placeholder="Enter your address" required value="<?php echo htmlspecialchars($_POST['address'] ?? ''); ?>">
                </div>
                <div class="input-group">
                    <label>Password</label>
                    <input type="password" id="password" name="password" placeholder="Create a password" required>
                </div>
                <div class="input-group">
                    <label>Confirm Password</label>
                    <input type="password" id="confirm" name="confirm" placeholder="Confirm your password" required>
                </div>
                <button type="submit" class="btn-register">Register 💓→</button>
            </form>

            <div class="auth-link">
                Already have an account? <a href="login.php">Login here</a>
            </div>
            <div class="back-home">
                <a href="index.php">← Back to Home</a>
            </div>
        </div>
    </div>
<?php include 'includes/footer.php'; ?>
</body>
</html>