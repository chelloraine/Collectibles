<?php
session_start();

$host = "127.0.0.1";  
$user = "root";       
$password = "";       
$database = "userlist_db";  

$conn = new mysqli($host, $user, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['Username']);
    $password = trim($_POST['Password']);

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
            $_SESSION['role'] = $row['role'];

            // Redirect based on role
            if (strtolower(trim($row['role'])) === "admin") { 
                $_SESSION['admin_logged_in'] = true;
                header("Location: http://localhost/website/ADMIN/adminpage.php"); // Redirect to admin page
                exit;
            } else {
                header("Location: user_dashboard.php"); // Redirect normal users
                exit;
            }
        } else {
            // Incorrect password case
            if (strtolower(trim($username)) === "admin") {
                $_SESSION['error'] = "Access to admin page denied! Incorrect password.";
            } else {
                $_SESSION['error'] = "Invalid username or password!";
            }
            header("Location: loginpage.php"); // Stay on the login page
            exit;
        }
    } else {
        $_SESSION['error'] = "User not found!";
        header("Location: loginpage.php"); // Stay on the login page
        exit;
    }

    $stmt->close();
}

$conn->close();
?>
