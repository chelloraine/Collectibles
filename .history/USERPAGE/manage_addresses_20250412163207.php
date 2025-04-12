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
        /* Your existing CSS for modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0,0,0);
            background-color: rgba(0,0,0,0.4);
            padding-top: 60px;
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
        }
        
        /* Modal Close Button */
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>
<body>

<div class="main-container">
    <div class="side-nav">
        <h3>Account</h3>
        <a href="my_account.php?tab=orders">My Orders</a>
        <a href="my_account.php?tab=addresses" class="active">Manage My Addresses</a>
    </div>

    <div style="flex: 1;">
        <h2>My Addresses</h2>
        <button class="add-address-btn" onclick="openAddressModal()">+ Add New Address</button>

        <?php foreach ($addresses as $address): ?>
            <div class="address-card <?= $address['Is_Default'] ? 'default' : '' ?>">
                <p><strong><?= htmlspecialchars($address['Full_Name']) ?></strong></p>
                <p><?= htmlspecialchars($address['Street_Address']) ?>, <?= htmlspecialchars($address['City']) ?>, <?= htmlspecialchars($address['Province']) ?>, <?= htmlspecialchars($address['Zip_Code']) ?></p>
                <p>📞 <?= htmlspecialchars($address['Phone']) ?></p>
                <div class="address-actions">
                    <?php if (!$address['Is_Default']): ?>
                        <a href="my_account.php?set_default=<?= $address['Address_ID'] ?>">Set as Default</a>
                    <?php else: ?>
                        <span style="color: green; font-weight: bold;">Default Address</span>
                    <?php endif; ?>
                    <a href="javascript:void(0);" onclick="editAddress(<?= $address['Address_ID'] ?>)">Edit</a>
                    <a href="manage_addresses.php?delete=<?= $address['Address_ID'] ?>" onclick="return confirm('Delete this address?')">Delete</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Modal for Editing Address -->
<div id="editAddressModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2>Edit Address</h2>
        <form id="editAddressForm">
            <input type="hidden" name="address_id" id="address_id">
            <label for="full_name">Full Name:</label>
            <input type="text" id="full_name" name="full_name" required><br><br>
            <label for="phone">Phone:</label>
            <input type="text" id="phone" name="phone" required><br><br>
            <label for="street_address">Street Address:</label>
            <input type="text" id="street_address" name="street_address" required><br><br>
            <label for="city">City:</label>
            <input type="text" id="city" name="city" required><br><br>
            <label for="province">Province:</label>
            <input type="text" id="province" name="province" required><br><br>
            <label for="zip_code">Zip Code:</label>
            <input type="text" id="zip_code" name="zip_code" required><br><br>
            <button type="submit">Update Address</button>
        </form>
    </div>
</div>

<script>
    // Show the modal
    function openAddressModal() {
        document.getElementById("editAddressModal").style.display = "block";
    }

    // Close the modal
    function closeModal() {
        document.getElementById("editAddressModal").style.display = "none";
    }

    // Fetch address data and open modal
    function editAddress(addressId) {
        // AJAX to fetch address data
        const xhr = new XMLHttpRequest();
        xhr.open("GET", "manage_addresses.php?edit_address_id=" + addressId, true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                const address = JSON.parse(xhr.responseText);
                document.getElementById("address_id").value = address.Address_ID;
                document.getElementById("full_name").value = address.Full_Name;
                document.getElementById("phone").value = address.Phone;
                document.getElementById("street_address").value = address.Street_Address;
                document.getElementById("city").value = address.City;
                document.getElementById("province").value = address.Province;
                document.getElementById("zip_code").value = address.Zip_Code;

                openAddressModal();
            }
        };
        xhr.send();
    }

    // Handle form submission via AJAX
    document.getElementById("editAddressForm").onsubmit = function(event) {
        event.preventDefault();  // Prevent page reload

        const formData = new FormData(this);
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "manage_addresses.php", true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                alert("Address updated successfully!");
                closeModal();
                location.reload();  // Refresh the page to reflect changes
            } else {
                alert("Failed to update address.");
            }
        };
        xhr.send(formData);
    };
</script>

</body>
</html>
