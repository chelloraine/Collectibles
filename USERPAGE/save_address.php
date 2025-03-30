<?php
session_start();
include_once("../connection.php");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "User not logged in."]);
    exit;
}

$user_id = $_SESSION['user_id'];

// Check if POST data is received
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $state = trim($_POST['state']);
    $zip = trim($_POST['zip']);

    if (empty($address) || empty($city) || empty($state) || empty($zip)) {
        echo json_encode(["status" => "error", "message" => "All fields are required."]);
        exit;
    }

    // Insert into the database
    $stmt = $conn->prepare("INSERT INTO addresses (user_id, address, city, state, zip) VALUES (?, ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("issss", $user_id, $address, $city, $state, $zip);
        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Address saved successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Database error."]);
        }
        $stmt->close();
    } else {
        echo json_encode(["status" => "error", "message" => "Database error."]);
    }
}

$conn->close();
?>
