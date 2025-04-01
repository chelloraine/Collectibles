<?php
session_start();
include '../connection.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Prepare and execute SQL query
    $stmt = $conn->prepare("SELECT Customer_ID, Username, Password FROM customers WHERE Username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $row['Password'])) {  
            $_SESSION['user_id'] = $row['Customer_ID'];
            $_SESSION['username'] = $row['Username'];

            // Redirect to user page
            header("Location: ../USERPAGE/userpage.php");
            exit;
        } else {
            $_SESSION['error'] = "Invalid username or password!";
            header("Location: loginpage.php");
            exit;
        }
    } else {
        $_SESSION['error'] = "User not found!";
        header("Location: loginpage.php");
        exit;
    }

    $stmt->close();
}

$conn->close();
header("Location: ../USERPAGE/userpage.php");
exit;


?>
