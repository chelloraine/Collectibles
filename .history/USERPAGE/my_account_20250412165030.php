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

// Products for Nav
$products = [
    'Hoodies' => 'hoodies.php',
    'T-Shirts' => 'tshirts.php',
    'Sweatpants' => 'sweatpants.php',
    'Hats' => 'hats.php'
];

// Fetch single address as JSON for modal
if (isset($_GET['fetch_address'])) {
    $address_id = intval($_GET['fetch_address']);
    $stmt = $conn->prepare("SELECT * FROM Customer_Address WHERE Address_ID = ? AND Customer_ID = ?");
    $stmt->bind_param("ii", $address_id, $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $address = $result->fetch_assoc();
    header('Content-Type: application/json');
    echo json_encode($address);
    exit;
}

// Handle update via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['address_id'])) {
    $address_id = $_POST['address_id'];
    $full_name = $_POST['full_name'];
    $phone = $_POST['phone'];
    $street = $_POST['street'];
    $city = $_POST['city'];
    $province = $_POST['province'];
    $zip = $_POST['zip'];

    $stmt = $conn->prepare("UPDATE Customer_Address SET Full_Name = ?, Phone = ?, Street_Address = ?, City = ?, Province = ?, Zip_Code = ? WHERE Address_ID = ? AND Customer_ID = ?");
    $stmt->bind_param("ssssssii", $full_name, $phone, $street, $city, $province, $zip, $address_id, $customer_id);

    if ($stmt->execute()) {
        echo "success";
    } else {
        http_response_code(500);
        echo "error";
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
        .main-container {
            display: flex;
            padding: 30px;
        }

        .side-nav {
            width: 180px;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            height: fit-content;
            margin-right: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .side-nav h3 {
            font-size: 20px;
            margin-bottom: 15px;
            color: #85c1e9;
        }

        .side-nav a {
            display: block;
            text-decoration: none;
            margin-bottom: 10px;
            font-weight: bold;
            color: #333;
            padding: 8px 12px;
            border-radius: 5px;
        }

        .side-nav a:hover,
        .side-nav a.active {
            background-color: #85c1e9;
            color: white;
        }

        .order-status-bar {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }

        .order-status-bar a {
            text-decoration: none;
            padding: 10px 20px;
            background-color: #eee;
            border-radius: 20px;
            font-weight: bold;
            color: #333;
        }

        .order-status-bar a.active {
            background-color: #85c1e9;
            color: white;
        }

        .order-card {
            background-color: #fff;
            padding: 20px;
            margin-bottom: 15px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .order-summary {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .order-details {
            margin-top: 15px;
            display: none;
        }

        .order-btns {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .order-btns button {
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }

        .cancel-btn {
            background-color: #e74c3c;
            color: white;
        }

        .track-btn {
            background-color: #3498db;
            color: white;
        }

        .address-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .address-card.default {
            border: 2px solid #85c1e9;
        }

        .address-actions {
            margin-top: 10px;
        }

        .address-actions a {
            margin-right: 10px;
            color: #3498db;
            font-weight: bold;
            text-decoration: none;
        }

        .add-address-btn {
            background-color: #85c1e9;
            color: white;
            border: none;
            padding: 10px 15px;
            font-weight: bold;
            border-radius: 5px;
            text-decoration: none;
        }

        .modal-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
            z-index: 999;
        }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            width: 400px;
            box-shadow: 0 0 15px rgba(0,0,0,0.2);
        }

        .modal-content h3 {
            margin-bottom: 15px;
            color: #85c1e9;
        }

        .modal-content input[type="text"],
        .modal-content input[type="number"],
        .modal-content select {
            width: 100%;
            padding: 8px;
            margin: 8px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .modal-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
        }

        .modal-buttons button {
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
        }

        .modal-buttons button[type="submit"] {
            background-color: #85c1e9;
            color: white;
        }

        .modal-buttons button[type="button"] {
            background-color: #ccc;
        }

    </style>
</head>
<body>

<!-- Header -->
<header>
    <img src="../images/logo.png" alt="Logo" class="logo" style="width: 110px; height: 100px;">
    <h1>Oshi Haven</h1>
    <div class="nav-container">
        <nav>
            <ul>
                <li><a href="#">Vtuber</a></li>
                <li class="dropdown">
                    <a href="#">Products</a>
                    <ul class="dropdown-menu">
                        <?php foreach ($products as $name => $link): ?>
                            <li><a href="<?php echo $link; ?>"><?php echo $name; ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </li>
                <li><a href="#">Wishlist</a></li>
                <li><a href="#">Rules</a></li>
                <li><a href="#">About Us</a></li>
                <li><a href="../logout.php" class="logout-btn">Logout</a></li>
            </ul>
        </nav>
        <div class="search-bar"><input type="text" placeholder="Search..."></div>
        <div class="icons">
            <div class="icon"><a href="..\USERPAGE\profile.php"></a></div>
            <div class="icon"><a href="cart.php"></a></div>
            <div class="icon"><a href="/website/USERPAGE/profilesettings.php">Profile</a></div>
        </div>
    </div>
</header>

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
        <?php if ($tab === 'orders'): ?>
            <div class="order-status-bar">
                <?php foreach ($status_tabs as $status): ?>
                    <a href="?tab=orders&status=<?= $status ?>" class="<?= $current_status === $status ? 'active' : '' ?>">
                        <?= ucwords(str_replace('_', ' ', $status)) ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <?php if (empty($orders)): ?>
                <p>No orders in this category.</p>
            <?php else: ?>
                <?php foreach ($orders as $order): ?>
                    <div class="order-card">
                        <div class="order-summary">
                            <div>
                                <strong>Order #<?= $order['Order_ID'] ?></strong> - <?= $order['Product_Name'] ?>
                                <br><small>Ordered on: <?= $order['Order_Date'] ?></small>
                            </div>
                            <button onclick="toggleDetails(this)">Details</button>
                        </div>

                        <div class="order-details">
                            <p><strong>Quantity:</strong> <?= $order['Quantity'] ?></p>
                            <p><strong>Total:</strong> ₱<?= number_format($order['Total_Amount'], 2) ?></p>
                            <p><strong>Status:</strong> <?= ucwords(str_replace('_', ' ', $order['Status'])) ?></p>

                            <div class="order-btns">
                                <?php if ($order['Status'] !== 'cancelled' && $order['Status'] !== 'shipped'): ?>
                                    <button class="cancel-btn" onclick="cancelOrder(<?= $order['Order_ID'] ?>)">Cancel</button>
                                <?php endif; ?>
                                <button class="track-btn">Track Order</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

        <?php elseif ($tab === 'addresses'): ?>
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

    <a href="javascript:void(0);" onclick="openEditModal(<?= $address['Address_ID'] ?>)">Edit</a>
    <a href="manage_addresses.php?delete=<?= $address['Address_ID'] ?>" onclick="return confirm('Delete this address?')">Delete</a>
</div>

    </div>
<?php endforeach; ?>


            <!-- Address Modal -->
            <div id="addressModal" class="modal-overlay">
                <div class="modal-content">
                    <h3>Add New Address</h3>
                    <form method="post" action="add_address_handler.php">
                        <label>Full Name</label>
                        <input type="text" name="full_name" required>

                        <label>Phone Number</label>
                        <input type="text" name="phone" required>

                        <label>Street Address</label>
                        <input type="text" name="street" required>

                        <label>City</label>
                        <input type="text" name="city" required>

                        <label>Province</label>
                        <input type="text" name="province" required>

                        <label>ZIP Code</label>
                        <input type="text" name="zip" required>

                        <label>
                            <input type="checkbox" name="is_default"> Set as Default
                        </label>

                        <div class="modal-buttons">
                            <button type="submit">Save Address</button>
                            <button type="button" onclick="closeAddressModal()">Cancel</button>
                        </div>
                    </form>
                </div>
                
                <!-- Edit Address Modal -->
                <div id="editAddressModal" class="modal-overlay">
  <div class="modal-content">
    <h3>Edit Address</h3>
    <form id="editAddressForm">
      <input type="hidden" name="address_id" id="edit_address_id">
      <label>Full Name</label>
      <input type="text" name="full_name" id="edit_full_name" required>

      <label>Phone Number</label>
      <input type="text" name="phone" id="edit_phone" required>

      <label>Street Address</label>
      <input type="text" name="street" id="edit_street" required>

      <label>City</label>
      <input type="text" name="city" id="edit_city" required>

      <label>Province</label>
      <input type="text" name="province" id="edit_province" required>

      <label>ZIP Code</label>
      <input type="text" name="zip" id="edit_zip" required>

      <div class="modal-buttons">
        <button type="submit">Update Address</button>
        <button type="button" onclick="closeEditModal()">Cancel</button>
      </div>
    </form>
  </div>
</div>
        <?php endif; ?>
    </div>
</div>

<script>
    function toggleDetails(button) {
        const orderDetails = button.parentElement.nextElementSibling;
        orderDetails.style.display = orderDetails.style.display === 'block' ? 'none' : 'block';
    }

    function openAddressModal() {
        document.getElementById('addressModal').style.display = 'flex';
    }

    function closeAddressModal() {
        document.getElementById('addressModal').style.display = 'none';
    }
    function openEditModal(addressId) {
        // Show modal
        document.getElementById('editAddressModal').style.display = 'flex';

        // Fetch address data via AJAX
        fetch('my_account.php?fetch_address=' + addressId)
            .then(response => response.json())
            .then(data => {
                document.getElementById('edit_address_id').value = data.Address_ID;
                document.getElementById('edit_full_name').value = data.Full_Name;
                document.getElementById('edit_phone').value = data.Phone;
                document.getElementById('edit_street').value = data.Street_Address;
                document.getElementById('edit_city').value = data.City;
                document.getElementById('edit_province').value = data.Province;
                document.getElementById('edit_zip').value = data.Zip_Code;
            });
    }

    function closeEditModal() {
        document.getElementById('editAddressModal').style.display = 'none';
    }

    document.getElementById('editAddressForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        fetch('my_account.php', {
            method: 'POST',
            body: formData
        }).then(res => {
            if (res.ok) {
                alert("Address updated!");
                closeEditModal();
                location.reload();
            } else {
                alert("Update failed.");
            }
        });
    });
</script>

</body>
</html>
