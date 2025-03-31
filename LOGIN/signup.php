<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../connection.php'; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect and sanitize form data
    $first_name = htmlspecialchars(trim($_POST['first_name']));
    $last_name = htmlspecialchars(trim($_POST['last_name']));
    $username = htmlspecialchars(trim($_POST['username']));
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $contact = htmlspecialchars(trim($_POST['contact']));
    $birthday = $_POST['birthday'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    $error_message = "";

    if (empty($first_name) || empty($last_name) || empty($username) || empty($email) || empty($contact) || empty($birthday) || empty($password) || empty($confirm_password)) {
        $error_message = "All fields are required.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
    } else {
        $email_domain = substr(strrchr($email, "@"), 1);
        if (!checkdnsrr($email_domain, "MX")) {
            $error_message = "Invalid email domain.";
        }
    }

    $birthdate = DateTime::createFromFormat('Y-m-d', $birthday);
    $today = new DateTime();
    $age = $today->diff($birthdate)->y;

    if (!$birthdate || $age < 16) {
        $error_message = "You must be at least 16 years old to register.";
    }

    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password)) {
        $error_message = "Password must be at least 8 characters long, contain an uppercase letter, a lowercase letter, a number, and a special character.";
    }

    if ($password !== $confirm_password) {
        $error_message = "Passwords do not match.";
    }

    if (empty($error_message)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $check_stmt = $conn->prepare("SELECT id FROM customers WHERE username = ? OR email = ?");
        $check_stmt->bind_param("ss", $username, $email);
        $check_stmt->execute();
        $check_stmt->store_result();
        
        if ($check_stmt->num_rows > 0) {
            $error_message = "Username or Email already taken.";
        } else {
            $stmt = $conn->prepare("INSERT INTO customers (first_name, last_name, username, email, contact, birthday, password) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $first_name, $last_name, $username, $email, $contact, $birthday, $hashed_password);

            if ($stmt->execute()) {
                header("Location: login.php");
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
            <p style="color: red;"> <?php echo $error_message; ?> </p>
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

            <button type="submit">Create Account</button>
        </form>

        <p>Already have an account? <a href="loginpage.php">Login</a></p>
    </div>
</body>
</html>
