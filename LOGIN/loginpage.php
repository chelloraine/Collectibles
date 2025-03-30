<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" href="style_log.css">
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>

        <!-- Display Error Message if Exists -->
        <?php if (isset($_SESSION['error'])): ?>
            <p class="error-message" style="color:red;"><?php echo htmlspecialchars($_SESSION['error']); ?></p>
            <?php unset($_SESSION['error']); // Remove error after displaying ?>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required placeholder="Enter your username" autocomplete="off">

            <label for="password">Password:</label>
        <div style="position: relative;">
        <input type="password" id="password" name="password" style="padding-right: 30px;">
         <span onclick="togglePassword()" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer;">
        👁️
        </span>
    </div>

<script>
    function togglePassword() {
        let passwordField = document.getElementById("password");
        passwordField.type = (passwordField.type === "password") ? "text" : "password";
    }
</script>

            <button type="submit">Login</button>
        </form>

        <p>Don't have an account? <a href="signup.php">Create one</a></p>
    </div>
</body>
</html>
