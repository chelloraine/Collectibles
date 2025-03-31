<?php
session_start();
include 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['customer_id'])) {
    header("Location: login_page.php"); // Redirect to login if not logged in
    exit;
}

// Get the customer's data
$customer_id = $_SESSION['customer_id'];

$stmt = $conn->prepare("SELECT First_Name, Last_Name, Username, Customer_Email, Contact_Number, Date_Of_Birth FROM Customers WHERE Customer_ID = ?");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($first_name, $last_name, $username, $email, $contact, $birthday);
$stmt->fetch();
$stmt->close();

// Handle the form submission for profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = htmlspecialchars(trim($_POST['first_name']));
    $last_name = htmlspecialchars(trim($_POST['last_name']));
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $contact = htmlspecialchars(trim($_POST['contact']));
    $birthday = $_POST['birthday'];

    // Error handling
    $error_message = "";

    if (empty($first_name) || empty($last_name) || empty($email) || empty($contact) || empty($birthday)) {
        $error_message = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
    }

    if (empty($error_message)) {
        // Update the customer's profile information
        $update_stmt = $conn->prepare("UPDATE Customers SET First_Name = ?, Last_Name = ?, Customer_Email = ?, Contact_Number = ?, Date_Of_Birth = ? WHERE Customer_ID = ?");
        $update_stmt->bind_param("sssssi", $first_name, $last_name, $email, $contact, $birthday, $customer_id);

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
    <link rel="stylesheet" href="styles.css">
    <script defer src="script.js"></script>
    <link href='https://fonts.googleapis.com/css?family=Passero One' rel='stylesheet'>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        body {
            background-color: #f4f4f4;
            font-family: 'Passero One', sans-serif;
        }
        header {
            background-color: #fff;
            padding: 15px 40px;
            display: flex;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .logo {
            width: 50px;
            height: 50px;
            margin-right: 10px;
        }
        .nav-container {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-left: 50px;
            flex-grow: 1;
        }
        .search-bar {
            margin-left: auto;
        }
        .search-bar input {
            padding: 5px 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .icons {
            display: flex;
            gap: 15px;
        }
        .icon {
            width: 40px;
            height: 40px;
            background-color: #ddd;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .icon a {
            display: block;
            width: 100%;
            height: 100%;
        }
        h1 {
            color: #85c1e9;
            font-size: 50px;
            font-weight: bold;
            font-family: 'Passero One', sans-serif;
        }
        .profile-container {
            width: 80%;
            margin: 40px auto;
            padding: 20px;
            background-color: white;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }
        .profile-container h2 {
            text-align: center;
            font-size: 30px;
            color: #333;
            margin-bottom: 30px;
        }
        .profile-container form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .profile-container label {
            font-size: 16px;
            font-weight: bold;
        }
        .profile-container input {
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .profile-container button {
            padding: 10px;
            font-size: 16px;
            background-color: #85c1e9;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .profile-container button:hover {
            background-color: #6ba1c8;
        }
        .profile-container .error-message,
        .profile-container .success-message {
            color: red;
            font-size: 16px;
            text-align: center;
        }
        .profile-container .success-message {
            color: green;
        }
        .profile-container .links {
            text-align: center;
            margin-top: 20px;
        }
        .profile-container .links a {
            color: #85c1e9;
            text-decoration: none;
            font-size: 16px;
        }
        .profile-container .links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <header>
        <img src="logo.png" alt="Logo" class="logo">
        <h1>Oshi Haven</h1>
        <div class="nav-container">
            <nav>
                <ul>
                    <li><a href="#">Vtuber</a></li>
                    <li><a href="#">Products</a></li>
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

    <div class="profile-container">
        <h2>Update Your Profile</h2>

        <!-- Error Message -->
        <?php if (!empty($error_message)): ?>
            <p class="error-message"><?php echo $error_message; ?></p>
        <?php endif; ?>

        <!-- Success Message -->
        <?php if (!empty($success_message)): ?>
            <p class="success-message"><?php echo $success_message; ?></p>
        <?php endif; ?>

        <form action="profile.php" method="POST">
            <label for="first_name">First Name</label>
            <input type="text" id="first_name" name="first_name" value="<?php echo $first_name; ?>" required>

            <label for="last_name">Last Name</label>
            <input type="text" id="last_name" name="last_name" value="<?php echo $last_name; ?>" required>

            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" value="<?php echo $email; ?>" required>

            <label for="contact">Contact Number</label>
            <input type="tel" id="contact" name="contact" value="<?php echo $contact; ?>" required>

            <label for="birthday">Birthday</label>
            <input type="date" id="birthday" name="birthday" value="<?php echo $birthday; ?>" required>

            <button type="submit">Update Profile</button>
        </form>

        <div class="links">
            <p><a href="change_password.php">Change Password</a></p>
        </div>
    </div>

</body>
</html>
