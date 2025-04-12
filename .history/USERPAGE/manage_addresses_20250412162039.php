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

// Handle address edit
if (isset($_GET['edit'])) {
    $address_id = $_GET['edit'];

    // Fetch the existing address details
    $stmt = $conn->prepare("SELECT * FROM Customer_Address WHERE Address_ID = ? AND Customer_ID = ?");
    $stmt->bind_param("ii", $address_id, $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $address = $result->fetch_assoc();

    if (!$address) {
        $_SESSION['error_message'] = "Address not found.";
        header("Location: my_account.php?tab=addresses");
        exit;
    }
}

// Handle form submission to update address
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_address'])) {
    $address_id = $_POST['address_id'];
    $full_name = $_POST['full_name'];
    $phone = $_POST['phone'];
    $street_address = $_POST['street_address'];
    $city = $_POST['city'];
    $province = $_POST['province'];
    $zip_code = $_POST['zip_code'];

    $stmt = $conn->prepare("UPDATE Customer_Address SET Full_Name = ?, Phone = ?, Street_Address = ?, City = ?, Province = ?, Zip_Code = ? WHERE Address_ID = ? AND Customer_ID = ?");
    $stmt->bind_param("ssssssii", $full_name, $phone, $street_address, $city, $province, $zip_code, $address_id, $customer_id);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Address updated successfully!";
        header("Location: my_account.php?tab=addresses");
        exit;
    } else {
        $_SESSION['error_message'] = "Failed to update address. Please try again.";
    }
}

// Fetch Addresses for displaying on the page
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
                            <a href="my_account.php?tab=addresses&set_default=<?= $address['Address_ID'] ?>">Set as Default</a>
                        <?php else: ?>
                            <span style="color: green; font-weight: bold;">Default Address</span>
                        <?php endif; ?>
                        <a href="my_account.php?tab=addresses&edit=<?= $address['Address_ID'] ?>">Edit</a>
                        <a href="manage_addresses.php?delete=<?= $address['Address_ID'] ?>" onclick="return confirm('Delete this address?')">Delete</a>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if (isset($address)): ?>
                <!-- Edit Address Form -->
                <div class="address-form">
                    <h3>Edit Address</h3>
                    <?php if (isset($_SESSION['error_message'])): ?>
                        <div class="error"><?= $_SESSION['error_message'] ?></div>
                        <?php unset($_SESSION['error_message']); ?>
                    <?php endif; ?>
                    <form method="POST">
                        <input type="hidden" name="address_id" value="<?= $address['Address_ID'] ?>">
                        <div>
                            <label for="full_name">Full Name:</label>
                            <input type="text" name="full_name" value="<?= htmlspecialchars($address['Full_Name']) ?>" required>
                        </div>
                        <div>
                            <label for="phone">Phone:</label>
                            <input type="text" name="phone" value="<?= htmlspecialchars($address['Phone']) ?>" required>
                        </div>
                        <div>
                            <label for="street_address">Street Address:</label>
                            <input type="text" name="street_address" value="<?= htmlspecialchars($address['Street_Address']) ?>" required>
                        </div>
                        <div>
                            <label for="city">City:</label>
                            <input type="text" name="city" value="<?= htmlspecialchars($address['City']) ?>" required>
                        </div>
                        <div>
                            <label for="province">Province:</label>
                            <input type="text" name="province" value="<?= htmlspecialchars($address['Province']) ?>" required>
                        </div>
                        <div>
                            <label for="zip_code">Zip Code:</label>
                            <input type="text" name="zip_code" value="<?= htmlspecialchars($address['Zip_Code']) ?>" required>
                        </div>
                        <div>
                            <button type="submit" name="update_address">Update Address</button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
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
