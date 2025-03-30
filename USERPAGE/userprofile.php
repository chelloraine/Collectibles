<?php
session_start();
include_once("../connection.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user details including contact number
$stmt = $conn->prepare("SELECT first_name, last_name, email, username, profile_picture, contact FROM users WHERE id = ?");
if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
}

// Fetch user addresses
$address_result = false;
$table_check = $conn->query("SHOW TABLES LIKE 'addresses'");
if ($table_check && $table_check->num_rows > 0) {
    $address_stmt = $conn->prepare("SELECT id, address, city, state, zip FROM addresses WHERE user_id = ?");
    if ($address_stmt) {
        $address_stmt->bind_param("i", $user_id);
        $address_stmt->execute();
        $address_result = $address_stmt->get_result();
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
            <button id="toggle-address" class="profile-btn">Saved Addresses</button>
        </section>

        <section class="address-panel" id="address-panel">
            <div class="panel-header">
                <h2>My Addresses</h2>
                <button id="add-address-btn" class="profile-btn">Add New Address</button>
            </div>
            <div id="address-list">
                <?php if ($address_result && $address_result->num_rows > 0): ?>
                    <ul>
                        <?php while ($address = $address_result->fetch_assoc()): ?>
                            <li class="address-item">
                                <strong>Name:</strong> <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?><br>
                                <strong>Contact:</strong> <?php echo htmlspecialchars($user['contact']); ?><br>
                                <strong>Address:</strong> <?php echo htmlspecialchars($address['address']); ?><br>
                                <strong>City:</strong> <?php echo htmlspecialchars($address['city']); ?><br>
                                <strong>State:</strong> <?php echo htmlspecialchars($address['state']); ?><br>
                                <strong>ZIP Code:</strong> <?php echo htmlspecialchars($address['zip']); ?>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                <?php else: ?>
                    <p>You currently don't have any saved addresses.</p>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Your Website. All rights reserved.</p>
    </footer>

    <script>
        $(document).ready(function() {
            $("#toggle-address").click(function() {
                $("#address-panel").toggle();
            });

            $("#add-address-btn").click(function() {
                $("#address-modal").fadeIn();
            });

            $(".close").click(function() {
                $("#address-modal").fadeOut();
            });
        });
    </script>
</body>
</html>
