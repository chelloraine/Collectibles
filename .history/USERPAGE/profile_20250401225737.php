<?php

session_start();
session_regenerate_id(true);  // Regenerate session to avoid session fixation
include "../connection.php";

// Debugging session issues (Uncomment if needed)
// echo "<pre>";
// print_r($_SESSION);
// echo "</pre>";
// exit;

// Check if user is logged in
if (!isset($_SESSION['customer_id'])) {
    echo "Session customer_id is NOT set! Debug: ";
    print_r($_SESSION); // 🚀 See what's inside your session
    exit;
    header("Location: loginpage.php");
    exit;
}



// Get the customer's data
$customer_id = $_SESSION['Customer_ID'];

$stmt = $conn->prepare("SELECT First_Name, Last_Name, Username, Customer_Email, Contact_Number, Date_Of_Birth FROM Customers WHERE Customer_ID = ?");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$stmt->bind_result($first_name, $last_name, $username, $email, $contact, $birthday);
$stmt->fetch();
$stmt->close();

// Handle form submission
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
        // Update profile data
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
                    <li><a href="../logout.php" class="logout-btn">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="profile-container">
        <h2>Update Your Profile</h2>

        <?php if (!empty($error_message)): ?>
            <p class="error-message"><?php echo $error_message; ?></p>
        <?php endif; ?>

        <?php if (!empty($success_message)): ?>
            <p class="success-message"><?php echo $success_message; ?></p>
        <?php endif; ?>

        <form action="profile.php" method="POST">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" readonly>

            <label for="first_name">First Name</label>
            <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($first_name); ?>" required>

            <label for="last_name">Last Name</label>
            <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($last_name); ?>" required>

            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>

            <label for="contact">Contact Number</label>
            <input type="tel" id="contact" name="contact" value="<?php echo htmlspecialchars($contact); ?>" required>

            <label for="birthday">Birthday</label>
            <input type="date" id="birthday" name="birthday" value="<?php echo htmlspecialchars($birthday); ?>" required>

            <button type="submit">Update Profile</button>
        </form>

        <div class="links">
            <p><a href="change_password.php">Change Password</a></p>
        </div>
    </div>

</body>
</html>
