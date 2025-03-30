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
    <main class="dashboard-container">
        <section class="profile-section">
            <h2>Saved Addresses</h2>
            <ul class="address-list">
                <?php if ($address_result && $address_result->num_rows > 0): ?>
                    <?php while ($address = $address_result->fetch_assoc()): ?>
                        <li class="address-item" onclick="displayAddress('<?php echo htmlspecialchars($address['address']); ?>', '<?php echo htmlspecialchars($address['city']); ?>', '<?php echo htmlspecialchars($address['state']); ?>', '<?php echo htmlspecialchars($address['zip']); ?>')">
                            <?php echo htmlspecialchars($address['address']); ?>
                        </li>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No saved addresses.</p>
                <?php endif; ?>
            </ul>
        </section>
        
        <!-- Display Selected Address -->
        <section class="address-display">
            <h2>Address Details</h2>
            <p id="selected-address">Click an address to view details here.</p>
        </section>
    </main>
    
    <script>
        function displayAddress(address, city, state, zip) {
            document.getElementById('selected-address').innerHTML = `<strong>Address:</strong> ${address}<br><strong>City:</strong> ${city}<br><strong>State:</strong> ${state}<br><strong>ZIP Code:</strong> ${zip}`;
        }
    </script>
</body>
</html>
