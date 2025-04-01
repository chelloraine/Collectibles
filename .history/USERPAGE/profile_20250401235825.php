<?php
session_start();

// Ensure user is logged in
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

// Get user ID from session
$user_id = $_SESSION['customer_id'];

// Function to fetch user data
function getUserData($conn, $user_id) {
    $stmt = $conn->prepare("SELECT First_Name, Last_Name, Username, Customer_Email, Contact_Number, Date_Of_Birth FROM Customers WHERE Customer_ID = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($first_name, $last_name, $username, $email, $contact, $birthday);
    $stmt->fetch();
    $stmt->close();

    return compact('first_name', 'last_name', 'username', 'email', 'contact', 'birthday');
}

// Get initial user data
$user_data = getUserData($conn, $user_id);

$error_message = "";
$success_message = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = htmlspecialchars(trim($_POST['first_name']));
    $last_name = htmlspecialchars(trim($_POST['last_name']));
    $new_username = htmlspecialchars(trim($_POST['username']));
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $contact = htmlspecialchars(trim($_POST['contact']));
    $birthday = $_POST['birthday'];

    // Validate inputs
    if (empty($first_name) || empty($last_name) || empty($new_username) || empty($email) || empty($contact) || empty($birthday)) {
        $error_message = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
    } elseif (!preg_match("/^[0-9]{10,15}$/", $contact)) { 
        $error_message = "Invalid contact number (10-15 digits allowed).";
    } elseif (strlen($new_username) < 4) {
        $error_message = "Username must be at least 4 characters long.";
    }

    // Check duplicate email
    if (empty($error_message)) {
        $email_check = $conn->prepare("SELECT Customer_ID FROM Customers WHERE Customer_Email = ? AND Customer_ID != ?");
        $email_check->bind_param("si", $email, $user_id);
        $email_check->execute();
        $email_check->store_result();
        if ($email_check->num_rows > 0) {
            $error_message = "This email is already in use!";
        }
        $email_check->close();
    }

    // Check duplicate username
    if (empty($error_message)) {
        $username_check = $conn->prepare("SELECT Customer_ID FROM Customers WHERE Username = ? AND Customer_ID != ?");
        $username_check->bind_param("si", $new_username, $user_id);
        $username_check->execute();
        $username_check->store_result();
        if ($username_check->num_rows > 0) {
            $error_message = "This username is already taken!";
        }
        $username_check->close();
    }

    if (empty($error_message)) {
        // Update user data in Customers table
        $update_stmt = $conn->prepare("UPDATE Customers SET First_Name = ?, Last_Name = ?, Username = ?, Customer_Email = ?, Contact_Number = ?, Date_Of_Birth = ? WHERE Customer_ID = ?");
        $update_stmt->bind_param("ssssssi", $first_name, $last_name, $new_username, $email, $contact, $birthday, $user_id);

        if ($update_stmt->execute()) {
            $success_message = "Profile updated successfully.";
            
            // 🔄 REFRESH user data
            $user_data = getUserData($conn, $user_id);
        } else {
            $error_message = "Error updating profile: " . $update_stmt->error;
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
    <style>
        body { background-color: #f4f4f4; font-family: Arial, sans-serif; }
        .profile-container { width: 50%; margin: 50px auto; background-color: white; padding: 30px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2); text-align: center; }
        .profile-container h2 { color: #333; font-size: 24px; margin-bottom: 20px; }
        .error-message, .success-message { font-size: 16px; margin-top: 15px; padding: 10px; border-radius: 5px; text-align: center; width: 100%; }
        .error-message { color: red; background-color: #ffdddd; border: 1px solid red; }
        .success-message { color: green; background-color: #ddffdd; border: 1px solid green; }
        form { display: flex; flex-direction: column; gap: 15px; text-align: left; }
        label { font-weight: bold; color: #333; }
        input { width: 100%; padding: 10px; font-size: 16px; border: 1px solid #ccc; border-radius: 5px; }
        button { background-color: #85c1e9; color: white; font-size: 18px; padding: 10px; border: none; border-radius: 5px; cursor: pointer; transition: background 0.3s; }
        button:hover { background-color: #5a9bd6; }
    </style>
</head>
<body>
    <div class="profile-container">
        <h2>Update Your Profile</h2>

        <form action="profile.php" method="POST">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" value="<?php echo isset($user_data['username']) ? htmlspecialchars($user_data['username']) : ''; ?>" required>

            <label for="first_name">First Name</label>
            <input type="text" id="first_name" name="first_name" value="<?php echo isset($user_data['first_name']) ? htmlspecialchars($user_data['first_name']) : ''; ?>" required>

            <label for="last_name">Last Name</label>
            <input type="text" id="last_name" name="last_name" value="<?php echo isset($user_data['last_name']) ? htmlspecialchars($user_data['last_name']) : ''; ?>" required>

            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" value="<?php echo isset($user_data['email']) ? htmlspecialchars($user_data['email']) : ''; ?>" required>

            <label for="contact">Contact Number</label>
            <input type="tel" id="contact" name="contact" value="<?php echo isset($user_data['contact']) ? htmlspecialchars($user_data['contact']) : ''; ?>" required>

            <label for="birthday">Birthday</label>
            <input type="date" id="birthday" name="birthday" value="<?php echo isset($user_data['birthday']) ? htmlspecialchars($user_data['birthday']) : ''; ?>" required>

            <button type="submit">Update Profile</button>

            <?php if (!empty($success_message)): ?>
                <p class="success-message"><?php echo $success_message; ?></p>
            <?php elseif (!empty($error_message)): ?>
                <p class="error-message"><?php echo $error_message; ?></p>
            <?php endif; ?>
        </form>
    </div>
</body>
</html>
