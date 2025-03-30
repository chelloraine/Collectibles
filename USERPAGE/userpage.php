<?php
session_start();
include_once("../connection.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user details
$stmt = $conn->prepare("SELECT first_name, last_name, email, username, status, profile_picture FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
    <link rel="stylesheet" href="users.css">
</head>
<body>
    <!-- Top Navigation Bar -->
    <header>
        <nav class="top-nav">
            <ul>
                <li><a href="#">Home</a></li>
                <li><a href="#">Categories</a></li>
                <li><a href="#">Notifications</a></li>
                <li><a href="#">Cart</a></li>
                <li><a href="userprofile.php">Profile</a></li>
            </ul>
        </nav>
    </header>

    <!-- Sidebar Navigation -->
    <aside class="sidebar">
        <ul>
            <li><a href="#">My Orders</a></li>
            <li><a href="#">Wishlist</a></li>
            <li><a href="#">Messages</a></li>
            <li><a href="#">Account Settings</a></li>
        </ul>
    </aside>

    <!-- Main Content Area -->
    <main>
        <h1>Welcome, <?php echo htmlspecialchars($user['username']); ?>!</h1>
        
        <!-- Profile Picture -->
        <div class="profile-picture-container">
            <img src="<?php echo !empty($user['profile_picture']) ? '../uploads/' . htmlspecialchars($user['profile_picture']) : '../uploads/default.png'; ?>" alt="Profile Picture" class="profile-picture">
            <form method="POST" enctype="multipart/form-data">
                <input type="file" name="profile_picture" accept="image/*">
                <button type="submit" name="upload_picture" class="upload-button">Upload</button>
            </form>
        </div>
        
        <!-- Profile Section -->
        <section class="profile-section">
            <h2>My Profile</h2>
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
        </section>
        
        <!-- Password Change Section -->
        <section class="password-section">
            <h3>Change Password</h3>
            <form method="POST">
                <label>Current Password:</label>
                <input type="password" name="current_password" required>
                
                <label>New Password:</label>
                <input type="password" name="new_password" required>
                
                <button type="submit" name="change_password">Change Password</button>
            </form>
        </section>
    </main>

    <!-- Footer -->
    <footer>
        <p>&copy; <?php echo date("Y"); ?> Your Website. All rights reserved.</p>
    </footer>
</body>
</html>
