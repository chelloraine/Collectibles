<?php
session_start();
include '../connection.php';

if (!isset($_SESSION['customer_id'])) {
    header("Location: ../loginpage.php");
    exit;
}

$customer_id = $_SESSION['customer_id'];
$statuses = ['to_pay', 'processing', 'in_transit', 'shipped', 'cancelled'];
$orders = [];

foreach ($statuses as $status) {
    $stmt = $conn->prepare("SELECT * FROM Orders WHERE Customer_ID = ? AND Status = ?");
    $stmt->bind_param("is", $customer_id, $status);
    $stmt->execute();
    $orders[$status] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Account</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            margin: 0;
        }

        /* Sidebar */
        .sidebar {
            width: 180px;
            background-color: #2c3e50;
            color: #fff;
            height: 100vh;
            padding: 20px 10px;
        }

        .sidebar h3 {
            margin-bottom: 20px;
            font-size: 18px;
        }

        .sidebar a {
            color: #fff;
            display: block;
            padding: 10px;
            text-decoration: none;
            margin-bottom: 8px;
            border-radius: 5px;
        }

        .sidebar a:hover, .sidebar a.active {
            background-color: #34495e;
        }

        /* Main content */
        .main-content {
            flex-grow: 1;
            padding: 20px;
        }

        .status-tabs {
            display: flex;
            overflow-x: auto;
            padding-bottom: 10px;
        }

        .status-tabs button {
            padding: 10px 16px;
            margin-right: 10px;
            border: none;
            background-color: #ecf0f1;
            border-radius: 5px;
            cursor: pointer;
            text-transform: capitalize;
        }

        .status-tabs button.active {
            background-color: #2980b9;
            color: white;
        }

        .orders-section {
            display: none;
        }

        .orders-section.active {
            display: block;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th, td {
            padding: 12px;
            border: 1px solid #ccc;
            text-align: left;
        }

        .order-row {
            cursor: pointer;
            background-color: #fff;
        }

        .order-row:hover {
            background-color: #f9f9f9;
        }

        .order-details-row {
            display: none;
            background-color: #f4f4f4;
        }

        .order-details-row td {
            padding: 10px;
        }

        .order-details-list {
            margin: 10px 0;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h3>Account</h3>
    <a href="#" class="active">My Orders</a>
    <!-- Add other account links here -->
</div>

<div class="main-content">
    <h2>My Orders</h2>

    <div class="status-tabs">
        <?php foreach ($statuses as $index => $status): ?>
            <button class="<?php echo $index === 0 ? 'active' : ''; ?>"
                    onclick="showTab('<?php echo $status; ?>')">
                <?php echo ucfirst(str_replace('_', ' ', $status)); ?>
            </button>
        <?php endforeach; ?>
    </div>

    <?php foreach ($statuses as $index => $status): ?>
        <div class="orders-section <?php echo $index === 0 ? 'active' : ''; ?>" id="tab-<?php echo $status; ?>">
            <?php if (empty($orders[$status])): ?>
                <p>No orders under "<?php echo ucfirst(str_replace('_', ' ', $status)); ?>"</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Total</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders[$status] as $order): ?>
                            <tr class="order-row" onclick="toggleDetails(this)">
                                <td>#<?php echo $order['Order_ID']; ?></td>
                                <td><?php echo htmlspecialchars($order['Product_Name']); ?></td>
                                <td><?php echo $order['Quantity']; ?></td>
                                <td>₱<?php echo number_format($order['Total_Amount'], 2); ?></td>
                                <td><?php echo date('M d, Y h:i A', strtotime($order['Order_Date'])); ?></td>
                            </tr>
                            <tr class="order-details-row">
                                <td colspan="5">
                                    <strong>Details:</strong>
                                    <ul class="order-details-list">
                                        <li>Product: <?php echo htmlspecialchars($order['Product_Name']); ?></li>
                                        <li>Quantity: <?php echo $order['Quantity']; ?></li>
                                        <li>Total Amount: ₱<?php echo number_format($order['Total_Amount'], 2); ?></li>
                                        <li>Order Date: <?php echo date('M d, Y h:i A', strtotime($order['Order_Date'])); ?></li>
                                        <li>Status: <?php echo ucfirst(str_replace('_', ' ', $order['Status'])); ?></li>
                                    </ul>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>

<script>
    function showTab(status) {
        document.querySelectorAll('.orders-section').forEach(section => {
            section.classList.remove('active');
        });
        document.querySelectorAll('.status-tabs button').forEach(btn => {
            btn.classList.remove('active');
        });
        document.getElementById('tab-' + status).classList.add('active');
        event.target.classList.add('active');
    }

    function toggleDetails(row) {
        const nextRow = row.nextElementSibling;
        const isVisible = nextRow.style.display === 'table-row';
        nextRow.style.display = isVisible ? 'none' : 'table-row';
    }
</script>

</body>
</html>
