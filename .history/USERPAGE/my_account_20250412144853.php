<?php
session_start();

if (!isset($_SESSION['customer_id'])) {
    header("Location: loginpage.php");
    exit;
}

include '../connection.php'; // Make sure this path is correct

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
    <title>My Orders</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f9f9f9; }
        h1 { text-align: center; margin-bottom: 30px; }
        h2 { margin-top: 40px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; background: white; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #f2f2f2; }
        .section-title { font-size: 1.3em; margin-bottom: 10px; }
        .no-orders { color: #999; font-style: italic; }
        .logout { float: right; margin-bottom: 20px; }
    </style>
</head>
<body>

<h1>🛒 My Orders</h1>

<div class="logout">
    <a href="../logout.php">Logout</a>
</div>

<?php
$status_labels = [
    "to_pay" => "🧾 To Pay",
    "processing" => "⚙️ Processing",
    "in_transit" => "🚚 In Transit",
    "shipped" => "📦 Shipped",
    "cancelled" => "❌ Cancelled"
];

foreach ($status_labels as $status => $label):
?>
    <h2><?php echo $label; ?></h2>

    <?php if (!empty($orders[$status])): ?>
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Total Amount</th>
                    <th>Order Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders[$status] as $order): ?>
                    <tr>
                        <td>#<?php echo htmlspecialchars($order['Order_ID']); ?></td>
                        <td><?php echo htmlspecialchars($order['Product_Name']); ?></td>
                        <td><?php echo $order['Quantity']; ?></td>
                        <td>₱<?php echo number_format($order['Total_Amount'], 2); ?></td>
                        <td><?php echo date("M d, Y h:i A", strtotime($order['Order_Date'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="no-orders">No orders in this category.</p>
    <?php endif; ?>

<?php endforeach; ?>

</body>
</html>
