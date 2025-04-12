<?php
session_start();
include "../connection.php";

$customer_id = $_SESSION['customer_id'] ?? null;

if (!$customer_id) {
    header("Location: ../loginpage.php");
    exit;
}

// Handle "Set as Default" Action
if (isset($_GET['set_default'])) {
    $address_id = $_GET['set_default'];

    // First, unset the default flag on all addresses for this customer
    $conn->query("UPDATE Customer_Address SET Is_Default = 0 WHERE Customer_ID = $customer_id");

    // Then, set the selected address as default
    $stmt = $conn->prepare("UPDATE Customer_Address SET Is_Default = 1 WHERE Address_ID = ? AND Customer_ID = ?");
    $stmt->bind_param("ii", $address_id, $customer_id);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Address set as default successfully!";
    } else {
        $_SESSION['error_message'] = "Failed to set address as default. Please try again.";
    }

    // Redirect back to the addresses page
    header("Location: my_account.php?tab=addresses");
    exit;
}

// Fetch Addresses
$addresses = [];
if (isset($_GET['tab']) && $_GET['tab'] === 'addresses') {
    $result = $conn->query("SELECT * FROM Customer_Address WHERE Customer_ID = $customer_id");
    $addresses = $result->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Account - Oshi Haven</title>
    <link rel="stylesheet" href="homepage.css">
    <style>
        /* Your existing CSS */
    </style>
</head>
<body>
<!-- Your header and navigation code -->

<!-- Main Content -->
<div class="main-container">
    <!-- Side Navigation -->
    <div class="side-nav">
        <h3>Account</h3>
        <a href="my_account.php?tab=orders" class="<?= $tab === 'orders' ? 'active' : '' ?>">My Orders</a>
        <a href="my_account.php?tab=addresses" class="<?= $tab === 'addresses' ? 'active' : '' ?>">Manage My Addresses</a>
    </div>

    <!-- Dynamic Content Area -->
    <div style="flex: 1;">
        <?php if (isset($_GET['tab']) && $_GET['tab'] === 'addresses'): ?>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2>My Addresses</h2>
                <button class="add-address-btn" onclick="openAddressModal()">+ Add New Address</button>
            </div>

            <?php foreach ($addresses as $address): ?>
                <div class="address-card <?= $address['Is_Default'] ? 'default' : '' ?>">
                    <p><strong><?= htmlspecialchars($address['Full_Name']) ?></strong></p>
                    <p><?= htmlspecialchars($address['Street_Address']) ?>, <?= htmlspecialchars($address['City']) ?>, <?= htmlspecialchars($address['Province']) ?>, <?= htmlspecialchars($address['Zip_Code']) ?></p>
                    <p>📞 <?= htmlspecialchars($address['Phone']) ?></p>
                    <div class="address-actions">
                        <?php if (!$address['Is_Default']): ?>
                            <!-- Updated link for setting the address as default -->
                            <a href="my_account.php?tab=addresses&set_default=<?= $address['Address_ID'] ?>">Set as Default</a>
                        <?php else: ?>
                            <span style="color: green; font-weight: bold;">Default Address</span>
                        <?php endif; ?>
                        <a href="edit_address.php?id=<?= $address['Address_ID'] ?>">Edit</a>
                        <a href="manage_addresses.php?delete=<?= $address['Address_ID'] ?>" onclick="return confirm('Delete this address?')">Delete</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Address Modal -->
<!-- Your address modal HTML -->

<script>
// Your existing JS functions
</script>

</body>
</html>
