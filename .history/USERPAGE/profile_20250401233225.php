<?php
session_start();

// Debugging session issues (Uncomment if needed)
// echo "<pre>";
// print_r($_SESSION);
// echo "</pre>";
// exit;

include "../connection.php";  // Ensure correct database connection file path

// Check if user is logged in
if (!isset($_SESSION['customer_id'])) {
    die("Access Denied! Please log in first.");
}

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

// Fetch the user's data
$user_id = $_SESSION['customer_id']; // Assign session ID to $user_id

$stmt = $conn->prepare("SELECT First_Name, Last_Name, Username, Customer_Email, Contact_Number, Date_Of_Birth FROM Customers WHERE Customer_ID = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($first_name, $last_name, $username, $email, $contact, $birthday);
$stmt->fetch();
$stmt->close();

// Handle form submission
$error_message = "";
$success_message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = htmlspecialchars(trim($_POST['first_name']));
    $last_name = htmlspecialchars(trim($_POST['last_name']));
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $contact = htmlspecialchars(trim($_POST['contact']));
    $birthday = $_POST['birthday'];

    // Error handling
    if (empty($first_name) || empty($last_name) || empty($email) || empty($contact) || empty($birthday)) {
        $error_message = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
    }

    if (empty($error_message)) {
        // Update profile data
        $update_stmt = $conn->prepare("UPDATE Customers SET First_Name = ?, Last_Name = ?, Customer_Email = ?, Contact_Number = ?, Date_Of_Birth = ? WHERE Customer_ID = ?");
        $update_stmt->bind_param("sssssi", $first_name, $last_name, $email, $contact, $birthday, $user_id);

        if ($update_stmt->execute()) {
            $success_message = "Profile updated successfully.";
        } else {
            $error_message = "Error: " . $update_stmt->error;
        }
        $update_stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Oshi Haven</title>
    <link rel="stylesheet" href="homepage.css">
</head>
<body>
    <header>
    <img src="../images/logo.png" alt="Logo" class="logo" style="width: 110px; height: 100px;">
        <h1>Oshi Haven</h1>
        <div class="nav-container">
            <nav>
                <ul>
                    <li><a href="#">Vtuber</a></li>
                    <li class="dropdown">
                        <a href="#">Products</a>
                        <ul class="dropdown-menu">
                            <?php foreach ($products as $name => $link): ?>
                                <li><a href="<?php echo $link; ?>"><?php echo $name; ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    <li><a href="#">Wishlist</a></li>
                    <li><a href="#">Rules</a></li>
                    <li><a href="#">About Us</a></li>
                </ul>
            </nav>
            <div class="search-bar">
                <input type="text" placeholder="Search...">
            </div>

            <div class="icons"> 
                <div class="icon"><a href="profile.php"></a></div> 
                <div class="icon"><a href="cart.php"></a></div>
                <div class="icon"><a href="settings.php"></a></div>
            </div>
        </div>
    </header>
    
    <section class="featured-container">
       
    </section>
</body>
</html>
