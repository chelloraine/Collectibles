<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection settings
$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "userlist_db";

// Create database connection
$conn = new mysqli($servername, $db_username, $db_password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error_message = "";

if (!isset($_SESSION['user_id'])) {
    header("Location: loginpage.php");
    exit();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $message = "All fields are required.";
    } elseif ($new_password !== $confirm_password) {
        $message = "New password and confirm password do not match.";
    } else {
        $user_id = $_SESSION['user_id'];
        $query = "SELECT password FROM users WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($hashed_password);
        $stmt->fetch();
        $stmt->close();

        if (password_verify($current_password, $hashed_password)) {
            $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_query = "UPDATE users SET password = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("si", $new_hashed_password, $user_id);

            if ($update_stmt->execute()) {
                $message = "Password updated successfully.";
            } else {
                $message = "Error updating password.";
            }
            $update_stmt->close();
        } else {
            $message = "Current password is incorrect.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
</head>
<body>
    <h2>Change Password</h2>
    <?php if ($message): ?>
        <p><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
    <form method="POST" action="">
        <label for="current_password">Current Password:</label>
        <input type="password" name="current_password" id="current_password" required><br><br>

        <label for="new_password">New Password:</label>
        <input type="password" name="new_password" id="new_password" required><br><br>

        <label for="confirm_password">Confirm New Password:</label>
        <input type="password" name="confirm_password" id="confirm_password" required><br><br>

        <button type="submit">Change Password</button>
    </form>
</body>
</html>