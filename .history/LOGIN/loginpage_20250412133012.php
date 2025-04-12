<?php 
// Set session settings before starting session
ini_set('session.gc_maxlifetime', 3600); 
session_start(); 
session_regenerate_id(true);  // Regenerate session for security

// Ensure `customer_id` is set if the session exists
$customer_id = isset($_SESSION['customer_id']) ? $_SESSION['customer_id'] : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" href="style_log.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>

        <!-- Display Error Message if Exists -->
        <?php if (isset($_SESSION['error'])): ?>
            <p class="error-message" style="color:red;">
                <?php echo htmlspecialchars($_SESSION['error']); ?>
            </p>
            <?php unset($_SESSION['error']); // Remove error after displaying ?>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <label for="username">Username</label>
            <input type="text" id="username" name="Username" required placeholder="Enter your username" autocomplete="off">

            <label for="password">Password:</label>
            <div style="position: relative;">
                <input type="password" id="password" name="Password" required style="padding-right: 40px;">
                <i class="fa-solid fa-eye" id="togglePassword" 
                   style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer;">
                </i>
            </div>

            <button type="submit">Login</button>
        </form>

        <p>Don't have an account? <a href="signup.php">Create one</a></p>
    </div>

    <!-- JavaScript for Password Toggle -->
    <script>
        document.getElementById("togglePassword").addEventListener("click", function() {
            let passwordField = document.getElementById("password");
            let icon = this;

            if (passwordField.type === "password") {
                passwordField.type = "text";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash"); 
            } else {
                passwordField.type = "password";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye"); 
            }
        });
    </script>

</body>
</html>
