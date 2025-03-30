<?php
include_once("../connection.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_POST['user_id'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $zip = $_POST['zip'];

    $stmt = $conn->prepare("INSERT INTO addresses (user_id, address, city, state, zip) VALUES (?, ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("issss", $user_id, $address, $city, $state, $zip);
        if ($stmt->execute()) {
            echo "Address saved successfully!";
        } else {
            echo "Error saving address: " . $conn->error;
        }
        $stmt->close();
    } else {
        echo "Database error: " . $conn->error;
    }

    $conn->close();
}
?>
