<?php
session_start();
include '../connection.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Prepare and execute SQL query
    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $row['password'])) {  
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = strtolower(trim($row['role'])); // Normalize role to lowercase

            // Redirect based on role
            if ($_SESSION['role'] === "admin") { 
                $_SESSION['admin_logged_in'] = true;
                header("Location: ../ADMIN/adminpage.php"); // Redirect admin
                exit;
            } else {
                header("Location: ../USERPAGE/userpage.php"); // Redirect normal users
                exit;
            }
        } else {
            $_SESSION['error'] = "Invalid username or password!";
            header("Location: loginpage.php"); // Stay on login page
            exit;
        }
    } else {
        $_SESSION['error'] = "User not found!";
        header("Location: loginpage.php"); // Stay on login page
        exit;
    }

    $stmt->close();
}

$conn->close();
?>
