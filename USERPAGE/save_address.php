<?php
session_start();
include_once("../connection.php");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $state = trim($_POST['state']);
    $zip = trim($_POST['zip']);

    // Check if this is the first address (set as default)
    $result = $conn->query("SELECT COUNT(*) as count FROM addresses WHERE user_id = $user_id");
    $row = $result->fetch_assoc();
    $is_default = ($row['count'] == 0) ? 1 : 0;

    // Insert new address
    $stmt = $conn->prepare("INSERT INTO addresses (user_id, address, city, state, zip, is_default) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("issssi", $user_id, $address, $city, $state, $zip, $is_default);
        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Address saved successfully!"]);
        } else {
            echo json_encode(["success" => false, "message" => "Error saving address: " . $conn->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(["success" => false, "message" => "Database error: " . $conn->error]);
    }

    $conn->close();
}
?>
