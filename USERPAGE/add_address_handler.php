<?php
session_start();
include "../connection.php";

$customer_id = $_SESSION['customer_id'] ?? null;

if (!$customer_id) {
    header("Location: ../loginpage.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $full_name = $_POST['full_name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $street = $_POST['street'] ?? '';
    $city = $_POST['city'] ?? '';
    $province = $_POST['province'] ?? '';
    $zip = $_POST['zip'] ?? '';
    $is_default = isset($_POST['is_default']) ? 1 : 0;

    // Validation (you can add more validation here)
    if (empty($full_name) || empty($phone) || empty($street) || empty($city) || empty($province) || empty($zip)) {
        // Error: Missing required fields
        $_SESSION['error_message'] = "All fields are required.";
        header("Location: my_account.php?tab=addresses");
        exit;
    }

    // Prepare the SQL query to insert the new address
    $stmt = $conn->prepare("INSERT INTO Customer_Address (Customer_ID, Full_Name, Phone, Street_Address, City, Province, Zip_Code, Is_Default) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssssi", $customer_id, $full_name, $phone, $street, $city, $province, $zip, $is_default);

    if ($stmt->execute()) {
        // If address is added successfully, handle default address logic
        if ($is_default) {
            // Set all other addresses for this customer to not default
            $conn->query("UPDATE Customer_Address SET Is_Default = 0 WHERE Customer_ID = $customer_id AND Address_ID != {$stmt->insert_id}");
        }

        $_SESSION['success_message'] = "Address added successfully!";
    } else {
        $_SESSION['error_message'] = "Failed to add address. Please try again.";
    }

    // Redirect back to the addresses tab
    header("Location: my_account.php?tab=addresses");
    exit;
}
?>
