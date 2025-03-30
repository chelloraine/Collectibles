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
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Fetch user addresses
$addresses = [];
$table_check = $conn->query("SHOW TABLES LIKE 'addresses'");
if ($table_check && $table_check->num_rows > 0) {
    $address_stmt = $conn->prepare("SELECT id, address, city, state, zip FROM addresses WHERE user_id = ?");
    $address_stmt->bind_param("i", $user_id);
    $address_stmt->execute();
    $addresses = $address_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $address_stmt->close();
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
    <style>
        .profile-section {
            position: relative;
            display: flex;
            flex-direction: column;
            padding: 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
            width: 600px;
        }
        .address-panel {
            position: absolute;
            top: 50px;
            right: 20px;
            width: 100%;
            max-width: 300px;
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
            transform: scaleY(0);
            transform-origin: top;
            transition: transform 0.3s ease-in-out;
        }
        .address-panel.open {
            transform: scaleY(1);
        }
        .address-panel h2 {
            margin: 0;
            font-size: 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .close-btn {
            cursor: pointer;
            font-size: 20px;
            color: red;
        }
        .address-list {
            margin-top: 10px;
        }
        .address-item {
            padding: 10px;
            border-bottom: 1px solid #ccc;
        }
        .add-address-btn {
            position: absolute;
            top: 10px;
            right: 15px;
            background: green;
            color: white;
            padding: 5px 10px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }
        .saved-addresses {
            cursor: pointer;
            font-weight: bold;
            text-decoration: none;
            display: block;
            padding: 10px;
        }
        .saved-addresses:hover {
            background: #f0f0f0;
        }
    </style>
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

            <span class="saved-addresses" id="toggle-addresses">Saved Addresses</span>

            <!-- Address Panel Inside Profile Section -->
            <div class="address-panel" id="address-panel">
                <h2>My Addresses <span class="close-btn">&times;</span></h2>
                <button class="add-address-btn" id="add-address-btn">Add New</button>
                <div class="address-list">
                    <?php if (!empty($addresses)): ?>
                        <?php foreach ($addresses as $address): ?>
                            <div class="address-item">
                                <p><strong>Address:</strong> <?php echo htmlspecialchars($address['address']); ?></p>
                                <p><strong>City:</strong> <?php echo htmlspecialchars($address['city']); ?></p>
                                <p><strong>State:</strong> <?php echo htmlspecialchars($address['state']); ?></p>
                                <p><strong>ZIP:</strong> <?php echo htmlspecialchars($address['zip']); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No saved addresses.</p>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Your Website. All rights reserved.</p>
    </footer>

    <script>
        $(document).ready(function() {
            // Toggle Address Panel
            $("#toggle-addresses").click(function() {
                $("#address-panel").toggleClass("open");
            });

            // Close Panel
            $(".close-btn").click(function() {
                $("#address-panel").removeClass("open");
            });
        });
    </script>
</body>
</html>
