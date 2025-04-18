<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection settings
$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "userlist_db";

// Create database connection
$conn = new mysqli($servername, $db_username, $db_password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error_message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect and sanitize form data
    $first_name = htmlspecialchars(trim($_POST['First_Name']));
    $last_name = htmlspecialchars(trim($_POST['Last_Name']));
    $username = htmlspecialchars(trim($_POST['Username']));
    $email = filter_var(trim($_POST['Customer_Email']), FILTER_SANITIZE_EMAIL);
    $contact = htmlspecialchars(trim($_POST['Contact_Number']));
    $birthday = $_POST['Date_Of_Birth'];
    $password = $_POST['Password'];
    $confirm_password = $_POST['confirm-password'];

    if (empty($first_name) || empty($last_name) || empty($username) || empty($email) || empty($contact) || empty($birthday) || empty($password) || empty($confirm_password)) {
        $error_message = "All fields are required.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
    } else {
        // Extract domain and check if it's valid
        $email_domain = substr(strrchr($email, "@"), 1);
        if (!checkdnsrr($email_domain, "MX")) {
            $error_message = "Invalid email domain.";
        }
    }

    $birthdate = DateTime::createFromFormat('Y-m-d', $birthday);
    $today = new DateTime();
    $age = $today->diff($birthdate)->y;

    if (!$birthdate || $age < 13) { // age restriction adjusted to 13 as per message
        $error_message = "You must be at least 13 years old to register.";
    }

    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password)) {
        $error_message = "Password must be at least 8 characters long, contain an uppercase letter, a lowercase letter, a number, and a special character.";
    }

    if ($password !== $confirm_password) {
        $error_message = "Passwords do not match.";
    }

    if (empty($error_message)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $check_stmt = $conn->prepare("SELECT Customer_ID FROM Customers WHERE Username = ? OR Customer_Email = ?");
        $check_stmt->bind_param("ss", $username, $email);
        $check_stmt->execute();
        $check_stmt->store_result();
        
        if ($check_stmt->num_rows > 0) {
            $error_message = "Username or Email already taken.";
        } else {
            $stmt = $conn->prepare("INSERT INTO Customers (First_Name, Last_Name, username, Customer_Email, Contact_Number, Date_Of_Birth, Password) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $first_name, $last_name, $username, $email, $contact, $birthday, $hashed_password);

            if ($stmt->execute()) {
                header("Location: loginpage.php");
                exit;
            } else {
                $error_message = "Error: " . $stmt->error;
            }
            $stmt->close();
        }
        $check_stmt->close();
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account</title>
    <link rel="stylesheet" href="signup.css">
</head>
<body>
    <div class="login-container">
        <h2>Create Account</h2>

        <?php if (!empty($error_message)) : ?>
            <p style="color: red;"><?php echo $error_message; ?></p>
        <?php endif; ?>

        <form action="signup.php" method="POST">
            <div class="name-container">
                <div class="name-field">
                    <label for="First_Name">First Name</label>
                    <input type="text" id="First_Name" name="First_Name" required placeholder="Enter your first name">
                </div>
                <div class="name-field">
                    <label for="Last_Name">Last Name</label>
                    <input type="text" id="Last_Name" name="Last_Name" required placeholder="Enter your last name">
                </div>
            </div>

            <label for="Username">Username</label>
            <input type="text" id="Username" name="Username" required placeholder="Choose a username">

            <label for="Customer_Email">Email Address</label>
            <input type="email" id="Customer_Email" name="Customer_Email" required placeholder="Enter your email">

            <label for="Contact_Number">Contact Number</label>
            <input type="tel" id="Contact_Number" name="Contact_Number" required placeholder="Enter your contact number" pattern="^(09|\+639)\d{9}$" title="Enter a valid Philippine phone number (e.g., 09171234567 or +639171234567)">

            <label for="Date_Of_Birth">Birthday</label>
            <input type="date" id="Date_Of_Birth" name="Date_Of_Birth" required>

            <label for="Password">Password</label>
            <input type="password" id="Password" name="Password" required placeholder="Choose a password" title="Password must be at least 8 characters long, contain an uppercase letter, a lowercase letter, a number, and a special character.">

            <label for="confirm-password">Confirm Password</label>
            <input type="password" id="confirm-password" name="confirm-password" required placeholder="Confirm your password">

            <button type="submit">Create Account</button>
        </form>

        <p>Already have an account? <a href="loginpage.php">Login</a></p>
    </div>
</body>
</html>
