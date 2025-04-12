<?php
session_start();
include "../connection.php";

$customer_id = $_SESSION['customer_id'] ?? null;

if (!$customer_id) {
    header("Location: ../loginpage.php");
    exit;
}

// Tabs
$tab = $_GET['tab'] ?? 'orders';

// Order Status Tabs
$status_tabs = ['to_pay', 'processing', 'in_transit', 'shipped', 'cancelled'];
$current_status = $_GET['status'] ?? 'to_pay';

// Fetch Orders
$orders = [];
if ($tab === 'orders') {
    $stmt = $conn->prepare("SELECT * FROM Orders WHERE Customer_ID = ? AND Status = ?");
    $stmt->bind_param("is", $customer_id, $current_status);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
    $stmt->close();
}

// Fetch Addresses
$addresses = [];
if ($tab === 'addresses') {
    $result = $conn->query("SELECT * FROM Customer_Address WHERE Customer_ID = $customer_id");
    $addresses = $result->fetch_all(MYSQLI_ASSOC);
}

// Handle Create Address (Add New Address)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['full_name'])) {
    $full_name = $_POST['full_name'];
    $phone = $_POST['phone'];
    $street = $_POST['street'];
    $city = $_POST['city'];
    $province = $_POST['province'];
    $zip = $_POST['zip'];
    $is_default = isset($_POST['is_default']) ? 1 : 0;

    // If setting as default, unset any previous defaults
    if ($is_default) {
        $conn->query("UPDATE Customer_Address SET Is_Default = 0 WHERE Customer_ID = $customer_id");
    }

    $stmt = $conn->prepare("INSERT INTO Customer_Address (Customer_ID, Full_Name, Phone, Street_Address, City, Province, Zip_Code, Is_Default) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssssii", $customer_id, $full_name, $phone, $street, $city, $province, $zip, $is_default);
    $stmt->execute();
    $stmt->close();

    header("Location: my_account.php?tab=addresses"); // Reload to show new address
    exit;
}

// Handle Update Address (Edit Address)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_address_id'])) {
    $address_id = $_POST['update_address_id'];
    $full_name = $_POST['full_name'];
    $phone = $_POST['phone'];
    $street = $_POST['street'];
    $city = $_POST['city'];
    $province = $_POST['province'];
    $zip = $_POST['zip'];
    $is_default = isset($_POST['is_default']) ? 1 : 0;

    // If setting as default, unset any previous defaults
    if ($is_default) {
        $conn->query("UPDATE Customer_Address SET Is_Default = 0 WHERE Customer_ID = $customer_id");
    }

    $stmt = $conn->prepare("UPDATE Customer_Address SET Full_Name=?, Phone=?, Street_Address=?, City=?, Province=?, Zip_Code=?, Is_Default=? WHERE Address_ID=? AND Customer_ID=?");
    $stmt->bind_param("ssssssiii", $full_name, $phone, $street, $city, $province, $zip, $is_default, $address_id, $customer_id);
    $stmt->execute();
    $stmt->close();

    header("Location: my_account.php?tab=addresses");
    exit;
}

// Handle Delete Address
if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM Customer_Address WHERE Address_ID = ? AND Customer_ID = ?");
    $stmt->bind_param("ii", $delete_id, $customer_id);
    $stmt->execute();
    $stmt->close();

    header("Location: my_account.php?tab=addresses");
    exit;
}

// Handle Set Default Address
if (isset($_GET['set_default'])) {
    $default_id = $_GET['set_default'];
    $conn->query("UPDATE Customer_Address SET Is_Default = 0 WHERE Customer_ID = $customer_id");
    $conn->query("UPDATE Customer_Address SET Is_Default = 1 WHERE Address_ID = $default_id AND Customer_ID = $customer_id");

    header("Location: my_account.php?tab=addresses");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Account - Oshi Haven</title>
    <link rel="stylesheet" href="homepage.css">
    <style>
        /* Your existing styles */
    </style>
</head>
<body>

<!-- Main Content -->
<div class="main-container">
    <div class="side-nav">
        <h3>Account</h3>
        <a href="my_account.php?tab=orders" class="<?= $tab === 'orders' ? 'active' : '' ?>">My Orders</a>
        <a href="my_account.php?tab=addresses" class="<?= $tab === 'addresses' ? 'active' : '' ?>">Manage My Addresses</a>
    </div>

    <div style="flex: 1;">
        <?php if ($tab === 'addresses'): ?>
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
                        <a href="#" onclick="editAddress(<?= $address['Address_ID'] ?>, '<?= htmlspecialchars($address['Full_Name']) ?>', '<?= htmlspecialchars($address['Phone']) ?>', '<?= htmlspecialchars($address['Street_Address']) ?>', '<?= htmlspecialchars($address['City']) ?>', '<?= htmlspecialchars($address['Province']) ?>', '<?= htmlspecialchars($address['Zip_Code']) ?>')">Edit</a>
                        <a href="my_account.php?tab=addresses&delete=<?= $address['Address_ID'] ?>" onclick="return confirm('Delete this address?')">Delete</a>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- Address Modal -->
            <div id="addressModal" class="modal-overlay">
                <div class="modal-content">
                    <h3>Add New Address</h3>
                    <form method="post" action="">
                        <label>Full Name</label>
                        <input type="text" name="full_name" id="modalFullName" required>

                        <label>Phone Number</label>
                        <input type="text" name="phone" id="modalPhone" required>

                        <label>Street Address</label>
                        <input type="text" name="street" id="modalStreet" required>

                        <label>City</label>
                        <input type="text" name="city" id="modalCity" required>

                        <label>Province</label>
                        <input type="text" name="province" id="modalProvince" required>

                        <label>ZIP Code</label>
                        <input type="text" name="zip" id="modalZip" required>

                        <label>
                            <input type="checkbox" name="is_default" id="modalIsDefault"> Set as Default
                        </label>

                        <input type="hidden" name="update_address_id" id="updateAddressId" value="">

                        <div class="modal-buttons">
                            <button type="submit">Save Address</button>
                            <button type="button" onclick="closeAddressModal()">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>

        <?php endif; ?>
    </div>
</div>

<script>
    function openAddressModal() {
        document.getElementById("addressModal").style.display = "flex";
    }

    function closeAddressModal() {
        document.getElementById("addressModal").style.display = "none";
    }

    function editAddress(id, full_name, phone, street, city, province, zip) {
        document.getElementById("modalFullName").value = full_name;
        document.getElementById("modalPhone").value = phone;
        document.getElementById("modalStreet").value = street;
        document.getElementById("modalCity").value = city;
        document.getElementById("modalProvince").value = province;
        document.getElementById("modalZip").value = zip;
        document.getElementById("updateAddressId").value = id;

        openAddressModal();
    }
</script>

</body>
</html>
