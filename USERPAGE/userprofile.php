<?php
session_start();
include_once("../connection.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user details
$stmt = $conn->prepare("SELECT first_name, last_name, email, username, profile_picture FROM users WHERE id = ?");
if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
} else {
    die("Error fetching user details: " . $conn->error);
}

// Check if 'addresses' table exists
$address_result = false;
$table_check = $conn->query("SHOW TABLES LIKE 'addresses'");
if ($table_check && $table_check->num_rows > 0) {
    // Fetch user addresses
    $address_stmt = $conn->prepare("SELECT id, address, city, state, zip FROM addresses WHERE user_id = ?");
    if ($address_stmt) {
        $address_stmt->bind_param("i", $user_id);
        $address_stmt->execute();
        $address_result = $address_stmt->get_result();
    } else {
        die("Error fetching addresses: " . $conn->error);
    }
}

// Check if 'orders' table exists before querying
$history_result = false;
$table_check = $conn->query("SHOW TABLES LIKE 'orders'");
if ($table_check->num_rows > 0) {
    $history_stmt = $conn->prepare("SELECT order_id, product_name, price, order_date FROM orders WHERE user_id = ? ORDER BY order_date DESC");
    if ($history_stmt) {
        $history_stmt->bind_param("i", $user_id);
        $history_stmt->execute();
        $history_result = $history_stmt->get_result();
        $history_stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Dashboard</title>
    <link rel="stylesheet" href="userprofile.css">
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
    
    <main class="dashboard-container">
        <section class="profile-section">
            <img src="<?php echo !empty($user['profile_picture']) ? '../uploads/' . htmlspecialchars($user['profile_picture']) : '../uploads/default.png'; ?>" alt="Profile Picture" class="profile-picture">
            <h2><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h2>
            <p>Email: <?php echo htmlspecialchars($user['email']); ?></p>
            <p>Username: <?php echo htmlspecialchars($user['username']); ?></p>

            <!-- Buttons for Edit Profile & Account Settings -->
            <div class="profile-actions">
                <a href="edit_profile.php" class="profile-btn">Edit Profile</a>
                <a href="account_settings.php" class="profile-btn">Account Settings</a>
            </div>

            <!-- Address Section (Moved Below Profile Actions) -->
            <div class="address-section">
                <h2>Saved Addresses</h2>
                <?php if ($address_result && $address_result->num_rows > 0): ?>
                    <ul>
                        <?php while ($address = $address_result->fetch_assoc()): ?>
                            <li><?php echo htmlspecialchars($address['address'] . ', ' . $address['city'] . ', ' . $address['state'] . ' ' . $address['zip']); ?></li>
                        <?php endwhile; ?>
                    </ul>
                <?php else: ?>
                    <p>You currently don't have any saved addresses.</p>
                <?php endif; ?>
                <a href="add_address.php" class="profile-btn">Add New Address</a>
            </div>
        </section>

        <!-- Shopping History Section -->
        <section class="history-section">
            <h2>Shopping History</h2>
            <?php if ($history_result && $history_result->num_rows > 0): ?>
                <table>
                    <tr>
                        <th>Order ID</th>
                        <th>Product Name</th>
                        <th>Price</th>
                        <th>Order Date</th>
                    </tr>
                    <?php while ($order = $history_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($order['order_id']); ?></td>
                            <td><?php echo htmlspecialchars($order['product_name']); ?></td>
                            <td>$<?php echo htmlspecialchars($order['price']); ?></td>
                            <td><?php echo htmlspecialchars($order['order_date']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            <?php else: ?>
                <p>No shopping history found.</p>
            <?php endif; ?>
        </section>
    </main>
    
    <footer>
        <p>&copy; <?php echo date("Y"); ?> Your Website. All rights reserved.</p>
    </footer>
</body>
</html>
