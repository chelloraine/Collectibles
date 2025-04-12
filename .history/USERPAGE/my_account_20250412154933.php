<?php
session_start();
include "../connection.php";

$customer_id = $_SESSION['customer_id'] ?? null;

if (!$customer_id) {
    header("Location: ../loginpage.php");
    exit;
}

$status_tabs = ['to_pay', 'processing', 'in_transit', 'shipped', 'cancelled'];
$current_status = $_GET['status'] ?? 'to_pay';

$stmt = $conn->prepare("SELECT * FROM Orders WHERE Customer_ID = ? AND Status = ?");
$stmt->bind_param("is", $customer_id, $current_status);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}
$stmt->close();

// For Products dropdown in nav
$products = [
    'Hoodies' => 'hoodies.php',
    'T-Shirts' => 'tshirts.php',
    'Sweatpants' => 'sweatpants.php',
    'Hats' => 'hats.php'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Orders - Oshi Haven</title>
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

        <div class="search-bar">
            <input type="text" placeholder="Search...">
        </div>

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
        <a href="my_account.php" class="active">My Orders</a>
        <!-- Add more links like Profile Settings, etc. if needed -->
    </div>

    <!-- Orders Section -->
    <div style="flex: 1;">
        <div class="order-status-bar">
            <?php foreach ($status_tabs as $status): ?>
                <a href="?status=<?php echo $status; ?>" class="<?php echo $current_status === $status ? 'active' : ''; ?>">
                    <?php echo ucwords(str_replace('_', ' ', $status)); ?>
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
                            <strong>Order #<?php echo $order['Order_ID']; ?></strong> - <?php echo $order['Product_Name']; ?>
                            <br><small>Ordered on: <?php echo $order['Order_Date']; ?></small>
                        </div>
                        <button onclick="toggleDetails(this)">Details</button>
                    </div>

                    <div class="order-details">
                        <p><strong>Quantity:</strong> <?php echo $order['Quantity']; ?></p>
                        <p><strong>Total:</strong> ₱<?php echo number_format($order['Total_Amount'], 2); ?></p>
                        <p><strong>Status:</strong> <?php echo ucwords(str_replace('_', ' ', $order['Status'])); ?></p>

                        <div class="order-btns">
                            <?php if ($order['Status'] !== 'cancelled' && $order['Status'] !== 'shipped'): ?>
                                <button class="cancel-btn" onclick="cancelOrder(<?php echo $order['Order_ID']; ?>)">Cancel</button>
                            <?php endif; ?>
                            <button class="track-btn">Track Order</button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleDetails(btn) {
    const details = btn.parentElement.nextElementSibling;
    details.style.display = details.style.display === 'block' ? 'none' : 'block';
}

function cancelOrder(orderId) {
    if (confirm("Are you sure you want to cancel this order?")) {
        // Simulate cancel - you can hook this up to an actual PHP cancel_order.php
        alert("Order #" + orderId + " cancelled!");
        // You would redirect or send a fetch POST here to update the DB
    }
}
</script>

</body>
</html>
