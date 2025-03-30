<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include '../connection.php';

$user_id = $_SESSION['user_id'];

// Fetch user details
$stmt = $conn->prepare("SELECT first_name, last_name, email, username, status FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    
    $update_stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, username = ? WHERE id = ?");
    $update_stmt->bind_param("ssssi", $first_name, $last_name, $email, $username, $user_id);
    if ($update_stmt->execute()) {
        $_SESSION['message'] = "Profile updated successfully!";
        header("Location: profile.php");
        exit;
    } else {
        $_SESSION['error'] = "Error updating profile!";
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
    
    $password_stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $password_stmt->bind_param("i", $user_id);
    $password_stmt->execute();
    $password_result = $password_stmt->get_result();
    $user_data = $password_result->fetch_assoc();
    
    if (password_verify($current_password, $user_data['password'])) {
        $update_pass_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $update_pass_stmt->bind_param("si", $new_password, $user_id);
        if ($update_pass_stmt->execute()) {
            $_SESSION['message'] = "Password changed successfully!";
        } else {
            $_SESSION['error'] = "Error changing password!";
        }
    } else {
        $_SESSION['error'] = "Incorrect current password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h2>My Profile</h2>
    
    <?php if (isset($_SESSION['message'])): ?>
        <p class="message"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></p>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <p class="error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
    <?php endif; ?>
    
    <form method="POST">
        <label>First Name:</label>
        <input type="text" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
        
        <label>Last Name:</label>
        <input type="text" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
        
        <label>Email:</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
        
        <label>Username:</label>
        <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
        
        <label>Status:</label>
        <input type="text" value="<?php echo htmlspecialchars($user['status']); ?>" disabled>
        
        <button type="submit" name="update_profile">Update Profile</button>
    </form>
    
    <h3>Change Password</h3>
    <form method="POST">
        <label>Current Password:</label>
        <input type="password" name="current_password" required>
        
        <label>New Password:</label>
        <input type="password" name="new_password" required>
        
        <button type="submit" name="change_password">Change Password</button>
    </form>
    
    <a href="dashboard.php">Back to Dashboard</a>
</body>
</html>
