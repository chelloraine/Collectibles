<?php
session_start();
ob_start(); // Start output buffering

include '../connection.php'; // Ensure connection.php properly initializes $conn

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Prepare and execute SQL query
    $stmt = $conn->prepare("SELECT Customer_ID, Username, Password FROM customers WHERE Username = ?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error); // Debugging
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();

        // Debugging: Check stored password format
        // echo "Stored Password: " . $row['Password']; exit;

        // Ensure stored password is hashed before using password_verify()
        if (password_verify($password, $row['Password'])) {
            $_SESSION['user_id'] = $row['Customer_ID'];
            $_SESSION['username'] = $row['Username'];

            // Redirect to user page
            header("Location: ../USERPAGE/userpage.php");
            exit;
        } else {
            $_SESSION['error'] = "Invalid username or password!";
        }
    } else {
        $_SESSION['error'] = "User not found!";
    }

    $stmt->close();
    $conn->close();
}

// Redirect back to login page if login fails
header("Location: ../USERPAGE/userpage.php");

exit;
?>
