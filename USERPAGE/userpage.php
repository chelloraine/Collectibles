<?php
session_start();
include_once("../connection.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    $username = htmlspecialchars($user['username']);
} else {
    $_SESSION['error'] = "User not found!";
    header("Location: ../login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
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
                <li><a href="#">Profile</a></li>
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
        <h1>Welcome, <?php echo $username; ?>!</h1>
        <section class="dashboard-widgets">
            <!-- Example Widget -->
            <div class="widget">
                <h2>Recent Orders</h2>
                <p>Display user's recent orders here.</p>
            </div>
            <!-- Additional widgets can be added here -->
        </section>
    </main>

    <!-- Footer -->
    <footer>
        <p>&copy; <?php echo date("Y"); ?> Your Website. All rights reserved.</p>
    </footer>
</body>
</html>
