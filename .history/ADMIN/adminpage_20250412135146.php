<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../loginpage.php");
    exit;
}

include '../connection.php';

// Optional: Fetch current admin data
$admin_id = $_SESSION['Admin_id'];
$admin_query = $conn->prepare("SELECT Admin_Name, Admin_Username FROM Admins WHERE Admin_ID = ?");
$admin_query->bind_param("i", $admin_id);
$admin_query->execute();
$admin_query->bind_result($admin_name, $admin_username);
$admin_query->fetch();
$admin_query->close();

// Handle deleting selected customers
if (isset($_POST['delete_selected']) && !empty($_POST['customer_ids'])) {
    $ids = implode(',', array_map('intval', $_POST['customer_ids']));
    $conn->query("DELETE FROM Customers WHERE Customer_ID IN ($ids)");
    $_SESSION['message'] = "Selected customers deleted successfully!";
    header("Location: admin.php");
    exit;
}

// Fetch all customers
$sql = "SELECT Customer_ID, First_Name, Last_Name, Customer_Email, Username, Contact_Number, Date_Of_Birth FROM Customers";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Manage Customers</title>
    <link rel="stylesheet" href="admin.css">
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background-color: #f2f2f2; }
        .message { color: green; margin-bottom: 15px; }
        .logout { margin-bottom: 20px; }
        .welcome { margin-bottom: 10px; font-weight: bold; }
    </style>
</head>
<body>

    <h2>Admin Panel - Customer Management</h2>

    <div class="welcome">
        Welcome, <?php echo htmlspecialchars($admin_username ?? $admin_name); ?>!
    </div>

    <?php if (isset($_SESSION['message'])): ?>
        <p class="message"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></p>
    <?php endif; ?>

    <div class="logout">
        <a href="../logout.php">Logout</a>
    </div>

    <form method="POST" onsubmit="return confirm('Are you sure you want to delete selected customers?');">
        <table>
            <thead>
                <tr>
                    <th>Select</th>
                    <th>ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Email</th>
                    <th>Username</th>
                    <th>Contact</th>
                    <th>Birthday</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><input type="checkbox" name="customer_ids[]" value="<?php echo $row['Customer_ID']; ?>"></td>
                        <td><?php echo htmlspecialchars($row["Customer_ID"]); ?></td>
                        <td><?php echo htmlspecialchars($row["First_Name"]); ?></td>
                        <td><?php echo htmlspecialchars($row["Last_Name"]); ?></td>
                        <td><?php echo htmlspecialchars($row["Customer_Email"]); ?></td>
                        <td><?php echo htmlspecialchars($row["Username"]); ?></td>
                        <td><?php echo htmlspecialchars($row["Contact_Number"]); ?></td>
                        <td><?php echo htmlspecialchars($row["Date_Of_Birth"]); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <br>
        <button type="submit" name="delete_selected">Delete Selected Customers</button>
    </form>

</body>
</html>
