<?php
$host = "127.0.0.1";
$user = "root";
$password = "";
$database = "userlist_db";

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Define admin details
$admin_name = "Mechelle Loraine N. Monsale";
$admin_username = "admin123"; // new column
$admin_email = "admin@example.com";
$plain_password = "SecurePass123"; // 🔐 Choose a secure password

// Hash the password
$hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);

// Prepare the insert statement
$stmt = $conn->prepare("INSERT INTO Admins (Admin_Name, Admin_Username, Admin_Email, Admin_Password) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $admin_name, $admin_username, $admin_email, $hashed_password);

if ($stmt->execute()) {
    echo "✅ Admin account created successfully!";
} else {
    echo "❌ Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
