<?php
session_start();
include 'db_connect.php'; // Include the database connection

// Check if the user is logged in
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];

    // Prepare the query to fetch customer data from Customers table
    $stmt = $conn->prepare("SELECT First_Name, Last_Name, Customer_Email FROM Customers WHERE Username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if user exists
    if ($result->num_rows > 0) {
        $customer = $result->fetch_assoc();
    } else {
        echo "Customer not found.";
        exit;
    }

    $stmt->close();
} else {
    echo "Please log in first.";
    exit;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="style_user.css">
</head>
<body>
    <div class="profile-container">
        <h2>Welcome, <?php echo htmlspecialchars($customer['First_Name'] . ' ' . $customer['Last_Name']); ?></h2>
        <p>Email: <?php echo htmlspecialchars($customer['Customer_Email']); ?></p>
        <p>Username: <?php echo htmlspecialchars($customer['Username']); ?></p>
        <!-- Add additional profile fields as needed -->
    </div>
</body>
</html>
