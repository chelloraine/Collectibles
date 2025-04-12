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

// Fetch Addresses for displaying on the page
$addresses = [];
if (isset($_GET['tab']) && $_GET['tab'] === 'addresses') {
    $result = $conn->query("SELECT * FROM Customer_Address WHERE Customer_ID = $customer_id");
    $addresses = $result->fetch_all(MYSQLI_ASSOC);
}

// Handle address update via AJAX
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
        echo json_encode(['success' => true, 'message' => 'Address updated successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update address.']);
    }
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
        /* Your existing CSS */

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            width: 400px;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-close {
            cursor: pointer;
            font-size: 20px;
            font-weight: bold;
        }
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
                            <a href="manage_addresses.php?set_default=<?= $address['Address_ID'] ?>">Set as Default</a>
                        <?php else: ?>
                            <span style="color: green; font-weight: bold;">Default Address</span>
                        <?php endif; ?>
                        <a href="javascript:void(0)" onclick="editAddress(<?= $address['Address_ID'] ?>)">Edit</a>
                        <a href="manage_addresses.php?delete=<?= $address['Address_ID'] ?>" onclick="return confirm('Delete this address?')">Delete</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Edit Address Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Edit Address</h3>
            <span class="modal-close" onclick="closeModal()">&times;</span>
        </div>
        <form id="editForm">
            <input type="hidden" id="address_id" name="address_id">
            <div>
                <label for="full_name">Full Name:</label>
                <input type="text" id="full_name" name="full_name" required>
            </div>
            <div>
                <label for="phone">Phone:</label>
                <input type="text" id="phone" name="phone" required>
            </div>
            <div>
                <label for="street_address">Street Address:</label>
                <input type="text" id="street_address" name="street_address" required>
            </div>
            <div>
                <label for="city">City:</label>
                <input type="text" id="city" name="city" required>
            </div>
            <div>
                <label for="province">Province:</label>
                <input type="text" id="province" name="province" required>
            </div>
            <div>
                <label for="zip_code">Zip Code:</label>
                <input type="text" id="zip_code" name="zip_code" required>
            </div>
            <div>
                <button type="submit" name="update_address">Update Address</button>
            </div>
        </form>
    </div>
</div>

<script>
// Open the modal with the address data
function editAddress(addressId) {
    // Fetch the address details using AJAX
    fetch(`manage_addresses.php?edit=${addressId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Fill the form with the address data
                const address = data.address;
                document.getElementById('address_id').value = address.Address_ID;
                document.getElementById('full_name').value = address.Full_Name;
                document.getElementById('phone').value = address.Phone;
                document.getElementById('street_address').value = address.Street_Address;
                document.getElementById('city').value = address.City;
                document.getElementById('province').value = address.Province;
                document.getElementById('zip_code').value = address.Zip_Code;

                // Show the modal
                document.getElementById('editModal').style.display = 'flex';
            } else {
                alert('Address not found.');
            }
        });
}

// Close the modal
function closeModal() {
    document.getElementById('editModal').style.display = 'none';
}

// Handle form submission (AJAX)
document.getElementById('editForm').addEventListener('submit', function(event) {
    event.preventDefault();

    const formData = new FormData(this);
    formData.append('update_address', true);

    fetch('manage_addresses.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            closeModal();
            location.reload();  // Reload the page to reflect changes
        } else {
            alert(data.message);
        }
    });
});
</script>

</body>
</html>
