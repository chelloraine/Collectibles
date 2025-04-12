<?php
session_start();
include "../connection.php";

$customer_id = $_SESSION['customer_id'] ?? null;

if (!$customer_id) {
    header("Location: ../loginpage.php");
    exit;
}

// Set default address
if (isset($_GET['set_default'])) {
    $address_id = (int)$_GET['set_default'];
    $conn->query("UPDATE Customer_Address SET Is_Default = 0 WHERE Customer_ID = $customer_id");
    $conn->query("UPDATE Customer_Address SET Is_Default = 1 WHERE Address_ID = $address_id AND Customer_ID = $customer_id");
    header("Location: manage_addresses.php");
    exit;
}

// Delete address
if (isset($_GET['delete'])) {
    $address_id = (int)$_GET['delete'];
    $conn->query("DELETE FROM Customer_Address WHERE Address_ID = $address_id AND Customer_ID = $customer_id");
    header("Location: manage_addresses.php");
    exit;
}

// Fetch all addresses
$result = $conn->query("SELECT * FROM Customer_Address WHERE Customer_ID = $customer_id");
$addresses = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage My Addresses</title>
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
        .side-nav a {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
            text-decoration: none;
            padding: 8px 12px;
            border-radius: 5px;
            color: #333;
        }
        .side-nav a:hover,
        .side-nav a.active {
            background-color: #85c1e9;
            color: white;
        }
        .address-container {
            flex: 1;
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
    </style>
</head>
<body>

<?php include "header.php"; ?>

<div class="main-container">
    <div class="side-nav">
        <h3>Account</h3>
        <a href="my_account.php">My Orders</a>
        <a href="manage_addresses.php" class="active">Manage My Addresses</a>
    </div>

    <div class="address-container">
        <h2>My Addresses</h2>
        <a href="add_address.php" class="add-address-btn">+ Add New Address</a>
        <br><br>
        <?php foreach ($addresses as $address): ?>
            <div class="address-card <?php echo $address['Is_Default'] ? 'default' : ''; ?>">
                <p><strong><?php echo htmlspecialchars($address['Full_Name']); ?></strong></p>
                <p><?php echo htmlspecialchars($address['Street_Address']); ?>, <?php echo htmlspecialchars($address['City']); ?>, <?php echo htmlspecialchars($address['Province']); ?>, <?php echo htmlspecialchars($address['Zip_Code']); ?></p>
                <p>📞 <?php echo htmlspecialchars($address['Phone']); ?></p>
                <div class="address-actions">
                    <?php if (!$address['Is_Default']): ?>
                        <a href="?set_default=<?php echo $address['Address_ID']; ?>">Set as Default</a>
                    <?php else: ?>
                        <span style="color: green; font-weight: bold;">Default Address</span>
                    <?php endif; ?>
                    <a href="edit_address.php?id=<?php echo $address['Address_ID']; ?>">Edit</a>
                    <a href="?delete=<?php echo $address['Address_ID']; ?>" onclick="return confirm('Delete this address?')">Delete</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

</body>
</html>
