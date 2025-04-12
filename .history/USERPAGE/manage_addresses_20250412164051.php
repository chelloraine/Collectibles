<?php
session_start();
include "../connection.php";

$customer_id = $_SESSION['customer_id'] ?? null;

if (!$customer_id) {
    header("Location: ../loginpage.php");
    exit;
}

// Handle "Set as Default"
if (isset($_GET['set_default'])) {
    $address_id = intval($_GET['set_default']);

    // Unset current default
    $conn->query("UPDATE Customer_Address SET Is_Default = 0 WHERE Customer_ID = $customer_id");

    // Set selected as default
    $stmt = $conn->prepare("UPDATE Customer_Address SET Is_Default = 1 WHERE Address_ID = ? AND Customer_ID = ?");
    $stmt->bind_param("ii", $address_id, $customer_id);
    $stmt->execute();

    header("Location: my_account.php?tab=addresses");
    exit;
}

// Handle Deletion
if (isset($_GET['delete'])) {
    $address_id = intval($_GET['delete']);

    $stmt = $conn->prepare("DELETE FROM Customer_Address WHERE Address_ID = ? AND Customer_ID = ?");
    $stmt->bind_param("ii", $address_id, $customer_id);
    $stmt->execute();

    header("Location: my_account.php?tab=addresses");
    exit;
}

// OPTIONAL: Handle edit AJAX request for legacy fallback (still safe to leave in)
if (isset($_GET['edit_address_id'])) {
    $address_id = intval($_GET['edit_address_id']);
    $stmt = $conn->prepare("SELECT * FROM Customer_Address WHERE Address_ID = ? AND Customer_ID = ?");
    $stmt->bind_param("ii", $address_id, $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $address = $result->fetch_assoc();

    header('Content-Type: application/json');
    echo json_encode($address);
    exit;
}

// OPTIONAL: Legacy POST update (not used anymore with modal on my_account.php)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['address_id'])) {
    $address_id = intval($_POST['address_id']);
    $full_name = $_POST['full_name'];
    $phone = $_POST['phone'];
    $street = $_POST['street_address'];
    $city = $_POST['city'];
    $province = $_POST['province'];
    $zip = $_POST['zip_code'];

    $stmt = $conn->prepare("UPDATE Customer_Address SET Full_Name=?, Phone=?, Street_Address=?, City=?, Province=?, Zip_Code=? WHERE Address_ID=? AND Customer_ID=?");
    $stmt->bind_param("ssssssii", $full_name, $phone, $street, $city, $province, $zip, $address_id, $customer_id);

    if ($stmt->execute()) {
        echo "success";
    } else {
        http_response_code(500);
        echo "error";
    }
    exit;
}

// Redirect in case someone opens this page directly
header("Location: my_account.php?tab=addresses");
exit;
?>
