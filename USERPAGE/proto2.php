<?php
session_start();
include_once("../connection.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user details
$stmt = $conn->prepare("SELECT first_name, last_name, email, username,contact, status, profile_picture, password FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

$products = [
    'Hoodies' => 'hoodies.php',
    'T-Shirts' => 'tshirts.php',
    'Sweatpants' => 'sweatpants.php',
    'Hats' => 'hats.php'
];

//array for images
$featured_items = [
    ['image' => 'hololive friends to go - Nekomata Okayu Keychain.jpg', 'title' => 'Hololive', 'category' => 'HAORI'],
    ['image' => 'hololive friends to go - Ookami Mio Keychain.jpg', 'title' => 'VShojo', 'category' => 'COLLECTION'],
    ['image' => 'hololive friends with u - Inugami Korone Street Outfit, Front.jpg', 'title' => 'Nijisanji', 'category' => 'HAORI'],
    ['image' => 'hololive friends with u - Ookami Mio.jpg', 'title' => 'Indie Vtuber', 'category' => 'MERCH DROP'],
    ['image' => 'hololive friends with u - Robocosan.jpg', 'title' => 'Creator 5', 'category' => 'SPECIAL EDITION'],
    ['image' => 'hololive friends with u - Sakura Miko.jpg', 'title' => 'Creator 6', 'category' => 'LIMITED EDITION']
];

// Handle Profile Update
if (isset($_POST['update_profile'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);

    $update_stmt = $conn->prepare("UPDATE users SET first_name=?, last_name=?, email=?, username=? WHERE id=?");
    $update_stmt->bind_param("ssssi", $first_name, $last_name, $email, $username, $user_id);
    $update_stmt->execute();
    $update_stmt->close();

    $_SESSION['message'] = "Profile updated successfully!";
    header("Location: userprofile.php");
    exit;
}

// Handle Profile Picture Upload
if (isset($_POST['upload_picture']) && isset($_FILES['profile_picture'])) {
    $target_dir = "../uploads/";
    $imageFileType = strtolower(pathinfo($_FILES["profile_picture"]["name"], PATHINFO_EXTENSION));
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

    if (in_array($imageFileType, $allowed_types)) {
        $new_filename = uniqid() . "." . $imageFileType;
        $target_file = $target_dir . $new_filename;

        if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
            $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
            $stmt->bind_param("si", $new_filename, $user_id);
            $stmt->execute();
            $stmt->close();

            $_SESSION['message'] = "Profile picture updated!";
            header("Location: userprofile.php");
            exit;
        }
    }
}

// Handle Password Change
if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];

    if (password_verify($current_password, $user['password'])) {
        $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
        $stmt->bind_param("si", $hashed_new_password, $user_id);
        $stmt->execute();
        $stmt->close();

        $_SESSION['message'] = "Password changed successfully!";
    } else {
        $_SESSION['error'] = "Current password is incorrect!";
    }
    header("Location: userprofile.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
    <link rel="stylesheet" href="users.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Toggle visibility of account settings when the link is clicked
            $("#account-settings-link").click(function(event) {
                event.preventDefault(); // Prevent the default link behavior
                $("#account-settings").toggle(); // Toggle visibility of account settings
            });
        });
    </script>
</head>
<body>

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

<aside class="sidebar">
    <ul>
        <li><a href="#">My Orders</a></li>
        <li><a href="#">Wishlist</a></li>
        <li><a href="#">Messages</a></li>
        <li><a href="#" id="account-settings-link">Account Settings</a></li>
    </ul>
</aside>

<main>
    <h1>Welcome, <?php echo htmlspecialchars($user['username']); ?>!</h1>

    <?php if (isset($_SESSION['message'])): ?>
        <p style="color: green;"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></p>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <p style="color: red;"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
    <?php endif; ?>

    <div class="account-settings" id="account-settings" style="display: none;">
        <img src="<?php echo !empty($user['profile_picture']) ? '../uploads/' . htmlspecialchars($user['profile_picture']) : '../uploads/default.png'; ?>" alt="Profile Picture" class="profile-picture">
        <form method="POST" enctype="multipart/form-data">
            <input type="file" name="profile_picture" accept="image/*">
            <button type="submit" name="upload_picture" class="upload-button">Upload</button>
        </form>

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
    </div>

   
</main>

<footer>
    <p>&copy; <?php echo date("Y"); ?> Your Website. All rights reserved.</p>
</footer>

</body>
</html>
