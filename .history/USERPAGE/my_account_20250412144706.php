<?php
session_start();

if (!isset($_SESSION['customer_id'])) {
    header("Location: loginpage.php");
    exit;
}

include '../connection.php'; // Update this path if necessary

$customer_id = $_SESSION['customer_id'];

// Fetch orders grouped by status
$stmt = $conn->prepare("
    SELECT Order_ID, Order_Status, Payment_ID, Order_Status
    FROM Orders
    WHERE Customer_ID = ?
    ORDER BY Order_ID DESC
");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[$row['Order_Status']][] = $row;
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
        body { font-family: Arial, sans-serif; padding: 20px; }
        h2 { margin-top: 40px; }
        table { border-collapse: collapse; width: 100%; margin-top: 10px; }
        th, td { border: 1px solid #ccc; padding: 10px; }
        th { background-color: #f4f4f4; }
        .logout { float: right; margin-bottom: 20px; }
    </style>
</head>
<body>

    <h1>My Orders</h1>
    <div class="logout">
        <a href="../logout.php">Logout</a>
    </div>

    <?php
    $status_labels = [
        "Pending" => "🧾 To Pay",
        "Processing" => "🔄 Processing",
        "Shipped" => "🚚 In Transit",
        "Delivered" => "✅ Delivered",
        "Cancelled" => "❌ Cancelled"
    ];

    foreach ($status_labels as $key => $label):
        if (!empty($orders[$key])):
    ?>
        <h2><?php echo $label; ?></h2>
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Status</th>
                    <th>Payment ID</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders[$key] as $order): ?>
                    <tr>
                        <td>#<?php echo htmlspecialchars($order['Order_ID']); ?></td>
                        <td><?php echo $order['Order_Status']; ?></td>
                        <td><?php echo $order['Payment_ID']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php
        endif;
    endforeach;
    ?>

</body>
</html>
