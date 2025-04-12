<?php
session_start();

if (!isset($_SESSION['customer_id'])) {
    header("Location: loginpage.php");
    exit;
}

include '../connection.php';

$customer_id = $_SESSION['customer_id'];

// Fetch all orders for this customer
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
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            padding: 20px;
        }

        h1 {
            text-align: center;
        }

        .nav {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }

        .nav button {
            background: #ddd;
            border: none;
            padding: 12px 20px;
            margin: 0 5px;
            cursor: pointer;
            font-weight: bold;
            border-radius: 5px;
            transition: background 0.3s ease;
        }

        .nav button.active {
            background-color: #4285f4;
            color: white;
        }

        .tab-content {
            display: none;
            background: white;
            padding: 20px;
            border-radius: 8px;
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
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: #f2f2f2;
        }

        .logout {
            float: right;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="logout">
    <a href="../logout.php">Logout</a>
</div>

<h1>My Account</h1>

<div class="nav">
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

<script>
    const buttons = document.querySelectorAll(".tab-btn");
    const tabs = document.querySelectorAll(".tab-content");

    buttons.forEach(btn => {
        btn.addEventListener("click", () => {
            buttons.forEach(b => b.classList.remove("active"));
            tabs.forEach(tab => tab.classList.remove("active"));

            btn.classList.add("active");
            document.getElementById(btn.dataset.tab).classList.add("active");
        });
    });
</script>

</body>
</html>
