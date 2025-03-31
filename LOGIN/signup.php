<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = htmlspecialchars(trim($_POST['first_name']));
    $last_name = htmlspecialchars(trim($_POST['last_name']));
    $username = htmlspecialchars(trim($_POST['username']));
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $contact = htmlspecialchars(trim($_POST['contact']));
    $birthday = $_POST['birthday'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($first_name) || empty($last_name) || empty($username) || empty($email) || empty($contact) || empty($birthday) || empty($password) || empty($confirm_password)) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: signup.php");
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format.";
        header("Location: signup.php");
        exit;
    }

    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password)) {
        $_SESSION['error'] = "Password must be at least 8 characters long, contain an uppercase letter, a lowercase letter, a number, and a special character.";
        header("Location: signup.php");
        exit;
    }

    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match.";
        header("Location: signup.php");
        exit;
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $check_stmt = $conn->prepare("SELECT Customer_ID FROM customers WHERE Username = ? OR Customer_Email = ?");
    $check_stmt->bind_param("ss", $username, $email);
    $check_stmt->execute();
    $check_stmt->store_result();
    
    if ($check_stmt->num_rows > 0) {
        $_SESSION['error'] = "Username or Email already taken.";
        header("Location: signup.php");
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO customers (First_Name, Last_Name, Username, Customer_Email, Contact_Number, Date_Of_Birth, Password) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $first_name, $last_name, $username, $email, $contact, $birthday, $hashed_password);

    if ($stmt->execute()) {
        header("Location: loginpage.php");
        exit;
    } else {
        $_SESSION['error'] = "Error: " . $stmt->error;
        header("Location: signup.php");
        exit;
    }

    $stmt->close();
    $check_stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="stylesheet" href="signup.css">
</head>
<body>
    <div class="signup-container">
        <h2>Sign Up</h2>

        <?php if (isset($_SESSION['error'])): ?>
            <p style="color: red;"> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?> </p>
        <?php endif; ?>

        <form action="signup.php" method="POST">
            <label for="first_name">First Name</label>
            <input type="text" id="first_name" name="first_name" required>

            <label for="last_name">Last Name</label>
            <input type="text" id="last_name" name="last_name" required>

            <label for="username">Username</label>
            <input type="text" id="username" name="username" required>

            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>

            <label for="contact">Contact Number</label>
            <input type="tel" id="contact" name="contact" required>

            <label for="birthday">Date of Birth</label>
            <input type="date" id="birthday" name="birthday" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>

            <label for="confirm_password">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" required>

            <button type="submit">Register</button>
        </form>

        <p>Already have an account? <a href="loginpage.php">Login</a></p>
    </div>
</body>
</html>
