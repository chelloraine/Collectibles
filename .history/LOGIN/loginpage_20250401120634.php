<?php 
session_start();
include '../connection.php'; 

// Redirect logged-in users to userpage.php
if (isset($_SESSION['Customer_id'])) { // Use 'Customer_id' here, not 'user_id'
    header("Location: ../USERPAGE/userpage.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" href="style_log.css">
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>

        <!-- Display Error Message if Exists -->
        <?php if (!empty($_SESSION['error'])): ?>
    <p class="error-message" style="color:red;">
        <?php echo htmlspecialchars($_SESSION['error']); ?>
    </p>
    <?php unset($_SESSION['error']); // Remove error after displaying ?>
<?php endif; ?>


        <form action="login.php" method="POST">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required placeholder="Enter your username" autocomplete="off">

            <label for="password">Password:</label>
            <div style="position: relative;">
                <input type="password" id="password" name="password" required autocomplete="off" style="padding-right: 40px;">
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
                icon.classList.replace("fa-eye", "fa-eye-slash"); // Change to eye slash
            } else {
                passwordField.type = "password";
                icon.classList.replace("fa-eye-slash", "fa-eye"); // Change back to eye
            }
        });
    </script>

</body>
</html>
