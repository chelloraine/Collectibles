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

    // First, check if the username exists in the Admins table
    $stmt = $conn->prepare("SELECT Admin_ID, Admin_Name, Admin_Password FROM Admins WHERE Admin_Name = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        // Admin found
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['Admin_Password'])) {
            $_SESSION['user_id'] = $row['Admin_ID'];
            $_SESSION['username'] = $row['Admin_Name'];
            $_SESSION['role'] = 'admin';
            $_SESSION['admin_logged_in'] = true;

            // Redirect to admin page
            header("Location: http://localhost/website/ADMIN/adminpage.php");
            exit;
        } else {
            $_SESSION['error'] = "Incorrect password for admin!";
            header("Location: loginpage.php"); // Stay on the login page
            exit;
        }
    } else {
        // No admin found, check the Customers table
        $stmt->close(); // Close the previous statement

        $stmt = $conn->prepare("SELECT Customer_ID, Username, Password FROM Customers WHERE Username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            // Customer found
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['Password'])) {
                $_SESSION['user_id'] = $row['Customer_ID'];
                $_SESSION['username'] = $row['Username'];
                $_SESSION['role'] = 'customer';

                // Redirect to customer dashboard
                header("Location: user_dashboard.php");
                exit;
            } else {
                $_SESSION['error'] = "Incorrect password for customer!";
                header("Location: loginpage.php"); // Stay on the login page
                exit;
            }
        } else {
            $_SESSION['error'] = "User not found!";
            header("Location: loginpage.php"); // Stay on the login page
            exit;
        }
    }

    $stmt->close();
}

$conn->close();
?>
