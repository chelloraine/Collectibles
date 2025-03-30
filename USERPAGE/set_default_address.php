<?php
session_start();
include_once("../connection.php");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "User not logged in."]);
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['address_id'])) {
    $address_id = intval($_POST['address_id']);

    // Remove default from all addresses of the user
    $conn->query("UPDATE addresses SET is_default = 0 WHERE user_id = $user_id");

    // Set the selected address as default
    $stmt = $conn->prepare("UPDATE addresses SET is_default = 1 WHERE id = ? AND user_id = ?");
    if ($stmt) {
        $stmt->bind_param("ii", $address_id, $user_id);
        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Default address updated."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to update default address."]);
        }
        $stmt->close();
    } else {
        echo json_encode(["status" => "error", "message" => "Database error."]);
    }
}

$conn->close();
?>
