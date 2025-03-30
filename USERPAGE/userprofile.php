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

            <div class="profile-actions">
                <a href="edit_profile.php" class="profile-btn">Edit Profile</a>
                <a href="account_settings.php" class="profile-btn">Account Settings</a>
            </div>

            <div class="address-section">
                <h2>Saved Addresses</h2>
                <button class="profile-btn" id="add-address-btn" style="float: right;">Add New Address</button>
                <nav>
                    <ul id="address-list">
                        <?php if ($address_result && $address_result->num_rows > 0): ?>
                            <?php while ($address = $address_result->fetch_assoc()): ?>
                                <li class="address-item" data-address="<?php echo htmlspecialchars(json_encode($address)); ?>">
                                    <?php echo htmlspecialchars($address['address'] . ', ' . $address['city'] . ', ' . $address['state'] . ' ' . $address['zip']); ?>
                                </li>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p>You currently don't have any saved addresses.</p>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </section>

        <!-- Address Details Display Section -->
        <section class="address-display" id="address-details">
            <h2>Selected Address</h2>
            <p id="full-address">Click on an address to view details</p>
        </section>
    </main>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Your Website. All rights reserved.</p>
    </footer>

    <script>
        $(document).ready(function() {
            // Handle address click
            $(".address-item").click(function() {
                let addressData = JSON.parse($(this).attr("data-address"));
                $("#full-address").html(
                    `<strong>Address:</strong> ${addressData.address}<br>
                    <strong>City:</strong> ${addressData.city}<br>
                    <strong>State:</strong> ${addressData.state}<br>
                    <strong>ZIP Code:</strong> ${addressData.zip}`
                );
            });

            // Open Add Address Modal
            $("#add-address-btn").click(function() {
                $("#address-modal").fadeIn();
            });

            // Close Modal
            $(".close").click(function() {
                $("#address-modal").fadeOut();
            });
        });
    </script>

    <style>
        .address-display {
            margin-top: 20px;
            padding: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #f9f9f9;
            min-height: 150px;
            max-width: 400px;
        }

        #add-address-btn {
            margin-bottom: 10px;
        }

        .address-item {
            cursor: pointer;
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }

        .address-item:hover {
            background-color: #f1f1f1;
        }
    </style>
</body>
</html>
