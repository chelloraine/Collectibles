<?php
session_start();
include_once("../connection.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user details
$stmt = $conn->prepare("SELECT first_name, last_name, email, username, profile_picture, contact FROM users WHERE id = ?");
if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
}

// Fetch user addresses
$addresses = [];
$table_check = $conn->query("SHOW TABLES LIKE 'addresses'");
if ($table_check && $table_check->num_rows > 0) {
    $address_stmt = $conn->prepare("SELECT id, address, city, state, zip FROM addresses WHERE user_id = ?");
    if ($address_stmt) {
        $address_stmt->bind_param("i", $user_id);
        $address_stmt->execute();
        $result = $address_stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $addresses[] = $row;
        }
        $address_stmt->close();
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
                <h2 id="saved-addresses">Saved Addresses</h2>
            </div>
        </section>

        <!-- Address Display Section (Initially Hidden) -->
        <section class="address-display" id="address-container" style="display: none;">
            <div class="address-header">
                <h2>My Addresses</h2>
                <button class="profile-btn" id="add-address-btn">Add New Address</button>
            </div>
            <ul id="address-list">
                <?php if (!empty($addresses)): ?>
                    <?php foreach ($addresses as $address): ?>
                        <li class="address-item">
                            <?php echo htmlspecialchars($address['address'] . ', ' . $address['city'] . ', ' . $address['state'] . ' ' . $address['zip']); ?>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>You currently don't have any saved addresses.</p>
                <?php endif; ?>
            </ul>
        </section>
    </main>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Your Website. All rights reserved.</p>
    </footer>

    <!-- Pop-up Modal for Adding Address -->
    <div id="address-modal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Add New Address</h2>
            <p><strong>Contact Number:</strong> <?php echo htmlspecialchars($user['contact']); ?></p>
            <form id="address-form">
                <input type="text" name="address" placeholder="Enter Address" required>
                <input type="text" name="city" placeholder="City" required>
                <input type="text" name="state" placeholder="State" required>
                <input type="text" name="zip" placeholder="ZIP Code" required>
                <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                <button type="submit" class="profile-btn">Save Address</button>
            </form>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $("#saved-addresses").click(function() {
                $("#address-container").fadeToggle();
            });

            $("#add-address-btn").click(function() {
                $("#address-modal").fadeIn();
            });

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
            float: right;
        }

        .address-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .address-item {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }

        #saved-addresses {
            cursor: pointer;
            color: blue;
            text-decoration: underline;
        }
    </style>
</body>
</html>
