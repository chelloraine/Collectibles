<?php
ini_set('session.gc_maxlifetime', 3600);  // Set session timeout to 1 hour
session_start();


session_start();

// Database connection
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
    // Get the username and password from the POST request
    $username = trim($_POST['Username']);
    $password = trim($_POST['Password']);

    // First, check if the username exists in the Admins table
    $stmt = $conn->prepare("SELECT Admin_ID, Admin_Name, Admin_Password FROM Admins WHERE LOWER(Admin_Name) = LOWER(?)");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        // Admin found
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['Admin_Password'])) {
            // Login success for Admin
            $_SESSION['Admin_id'] = $row['Admin_ID'];
            $_SESSION['username'] = $row['Admin_Name'];
            $_SESSION['role'] = 'admin';
            $_SESSION['admin_logged_in'] = true;

            // Redirect to admin dashboard
            header("Location: http://localhost/website/ADMIN/admin_dashboard.php");
            exit;
        } else {
            // Incorrect password for Admin
            $_SESSION['error'] = "Incorrect password for admin!";
            header("Location: loginpage.php");
            exit;
        }
    } else {
        // Admin not found, check Customers table
        $stmt->close(); // Close the previous statement

        // Check for the username in Customers table
        $stmt = $conn->prepare("SELECT Customer_ID, Username, Password FROM Customers WHERE LOWER(Username) = LOWER(?)");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            // Customer found
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['Password'])) {
                // Login success for Customer
                $_SESSION['customer_id'] = $row['Customer_ID'];
                $_SESSION['username'] = $row['Username'];
                $_SESSION['role'] = 'customer';

                // Redirect to customer dashboard
                header("Location: http://localhost/website/USERPAGE/userpageproto.php");
                exit;
            } else {
                // Incorrect password for Customer
                $_SESSION['error'] = "Incorrect password for customer!";
                header("Location: loginpage.php");
                exit;
            }
        } else {
            // User not found in both Admin and Customer tables
            $_SESSION['error'] = "User not found!";
            header("Location: loginpage.php");
            exit;
        }
    }

    $stmt->close(); // Close the statement after both checks
}

$conn->close();
?>
