<?php
session_start();

if (!isset($_SESSION['customer_id'])) {
    header("Location: loginpage.php");
    exit;
}

include '../connection.php';

$customer_id = $_SESSION['customer_id'];

// Fetch orders grouped by status
$stmt = $conn->prepare("SELECT * FROM Orders WHERE Customer_ID = ? ORDER BY Order_Date DESC");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[$row['Status']][] = $row;
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Account</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: Arial, sans-serif;
            display: flex;
            height: 100vh;
            background: #f9f9f9;
        }

        /* Sidebar */
        .sidebar {
            width: 200px;
            background-color: #2c3e50;
            padding: 20px 10px;
            color: white;
        }

        .sidebar h3 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 18px;
        }

        .sidebar a {
            display: block;
            color: #ecf0f1;
            text-decoration: none;
            padding: 10px 15px;
            margin: 10px 0;
            border-radius: 6px;
            transition: 0.3s;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background-color: #1abc9c;
        }

        /* Main Content */
        .main-content {
            flex-grow: 1;
            padding: 30px;
            overflow-y: auto;
        }

        .nav-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .nav-tabs button {
            padding: 10px 15px;
            border: none;
            background: #ddd;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.3s;
        }

        .nav-tabs button.active {
            background-color: #2980b9;
            color: white;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
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

        th {
            background: #f2f2f2;
        }

        .welcome {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .logout {
            margin-top: 20px;
            display: inline-block;
            color: white;
            background: #c0392b;
            padding: 8px 12px;
            border-radius: 6px;
            text-decoration: none;
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <h3>My Account</h3>
    <a href="#" class="active" onclick="showSection('orders')">📦 My Orders</a>
    <a href="../logout.php" class="logout">Logout</a>
</div>

<!-- Main Content -->
<div class="main-content">
    <div class="welcome">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</div>

    <!-- Orders Section -->
    <div id="orders" class="orders-section">
        <div class="nav-tabs">
            <button class="tab-btn active" data-tab="to_pay">🧾 To Pay</button>
            <button class="tab-btn" data-tab="processing">⚙️ Processing</button>
            <button class="tab-btn" data-tab="in_transit">🚚 In Transit</button>
            <button class="tab-btn" data-tab="shipped">📦 Shipped</button>
            <button class="tab-btn" data-tab="cancelled">❌ Cancelled</button>
        </div>

        <?php
        $status_labels = [
            "to_pay" => "To Pay",
            "processing" => "Processing",
            "in_transit" => "In Transit",
            "shipped" => "Shipped",
            "cancelled" => "Cancelled"
        ];

        foreach ($status_labels as $status => $label):
        ?>
            <div id="<?php echo $status; ?>" class="tab-content <?php echo $status === 'to_pay' ? 'active' : ''; ?>">
                <h2><?php echo $label; ?> Orders</h2>

                <?php if (!empty($orders[$status])): ?>
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
                                <tr>
                                    <td>#<?php echo $order['Order_ID']; ?></td>
                                    <td><?php echo htmlspecialchars($order['Product_Name']); ?></td>
                                    <td><?php echo $order['Quantity']; ?></td>
                                    <td>₱<?php echo number_format($order['Total_Amount'], 2); ?></td>
                                    <td><?php echo date('M d, Y h:i A', strtotime($order['Order_Date'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No orders in this category.</p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    tabButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            tabButtons.forEach(b => b.classList.remove('active'));
            tabContents.forEach(tab => tab.classList.remove('active'));

            btn.classList.add('active');
            document.getElementById(btn.dataset.tab).classList.add('active');
        });
    });

    function showSection(sectionId) {
        // For future if you want to add more sections
        document.getElementById('orders').style.display = 'block';
    }
</script>

</body>
</html>
