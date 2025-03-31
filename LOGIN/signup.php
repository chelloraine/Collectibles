<?php
session_start();
include 'db_connect.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect and sanitize user input
    $first_name = htmlspecialchars(trim($_POST['first_name']));
    $last_name = htmlspecialchars(trim($_POST['last_name']));
    $username = htmlspecialchars(trim($_POST['username']));
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $contact = htmlspecialchars(trim($_POST['contact']));
    $birthday = $_POST['birthday'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Error handling
    $error_message = "";

    if (empty($first_name) || empty($last_name) || empty($username) || empty($email) || empty($contact) || empty($birthday) || empty($password) || empty($confirm_password)) {
        $error_message = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
    } elseif ($password !== $confirm_password) {
        $error_message = "Passwords do not match.";
    }

    if (empty($error_message)) {
        // Check if the contact number already exists in the database
        $stmt_check_contact = $conn->prepare("SELECT * FROM Customers WHERE Contact_Number = ?");
        $stmt_check_contact->bind_param("s", $contact);
        $stmt_check_contact->execute();
        $result = $stmt_check_contact->get_result();

        if ($result->num_rows > 0) {
            $error_message = "This contact number is already registered. Please use a different contact number.";
        } else {
            // Hash password for security
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Prepare SQL to insert the new user into the database
            $stmt = $conn->prepare("INSERT INTO Customers (First_Name, Last_Name, Username, Customer_Email, Contact_Number, Date_Of_Birth, Password) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $first_name, $last_name, $username, $email, $contact, $birthday, $hashed_password);

            if ($stmt->execute()) {
                // Redirect to login page after successful signup
                header("Location: login.php");
                exit;
            } else {
                $error_message = "Error: " . $stmt->error;
            }
            $stmt->close();
        }

        $stmt_check_contact->close();
    }

    // Close the connection
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Oshi Haven</title>
    <link rel="stylesheet" href="signup.css">
    <script defer src="script.js"></script>
    <link href='https://fonts.googleapis.com/css?family=Passero One' rel='stylesheet'>
    <style>
        /* Include your CSS styling here from previous responses */
    </style>
</head>
<body>

<header>
    <img src="logo.png" alt="Logo" class="logo">
    <h1>Oshi Haven</h1>
</header>

<div class="signup-container">
    <h2>Create an Account</h2>

    <!-- Error Message -->
    <?php if (!empty($error_message)): ?>
        <p class="error-message"><?php echo $error_message; ?></p>
    <?php endif; ?>

    <form action="signup.php" method="POST">
        <label for="first_name">First Name</label>
        <input type="text" id="first_name" name="first_name" required>

        <label for="last_name">Last Name</label>
        <input type="text" id="last_name" name="last_name" required>

        <label for="username">Username</label>
        <input type="text" id="username" name="username" required>

        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" required>

        <label for="contact">Contact Number</label>
        <input type="tel" id="contact" name="contact" required>

        <label for="birthday">Birthday</label>
        <input type="date" id="birthday" name="birthday" required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>

        <label for="confirm_password">Confirm Password</label>
        <input type="password" id="confirm_password" name="confirm_password" required>

        <button type="submit">Sign Up</button>
    </form>

    <div class="links">
        <p>Already have an account? <a href="loginpage.php">Login here</a></p>
    </div>
</div>

</body>
</html>
