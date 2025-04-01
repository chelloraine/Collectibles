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
    // Declare the variables outside of bind_result
    $first_name = $last_name = $username = $email = $contact = $birthday = null;

    // Prepare the SQL query to select user data
    $stmt = $conn->prepare("SELECT First_Name, Last_Name, Username, Customer_Email, Contact_Number, Date_Of_Birth FROM Customers WHERE Customer_ID = ?");
    $stmt->bind_param("i", $user_id); // Bind the user ID
    $stmt->execute(); // Execute the query
    
    // Bind the result columns to PHP variables
    $stmt->bind_result($first_name, $last_name, $username, $email, $contact, $birthday);
    
    // Fetch the data into the variables
    if ($stmt->fetch()) {
        // Return the data as an associative array
        return compact('first_name', 'last_name', 'username', 'email', 'contact', 'birthday');
    } else {
        return null; // Return null if no data found
    }
    $stmt->close();
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
            $success_message = "Profile updated successfully.";  // Success message
            // Refresh user data after successful update
            $user_data = getUserData($conn, $user_id);
        } else {
            $error_message = "Error updating profile: " . $update_stmt->error;  // Error message
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
        * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Arial, sans-serif;
}

body {
    background-color: #f4f4f4;
}

/* Profile Container */
.profile-container {
    width: 50%;
    margin: 50px auto;
    background-color: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    text-align: center;
}

.profile-container h2 {
    color: #333;
    font-size: 24px;
    margin-bottom: 20px;
}

/* Error and Success Messages */
.error-message, .success-message {
    font-size: 16px;
    margin-bottom: 15px;
    padding: 10px;
    border-radius: 5px;
}

.error-message {
    color: red;
    background-color: #ffdddd;
    border: 1px solid red;
}

.success-message {
    color: green;
    background-color: #ddffdd;
    border: 1px solid green;
}

/* Form Styles */
form {
    display: flex;
    flex-direction: column;
    gap: 15px;
    text-align: left;
}

label {
    font-weight: bold;
    color: #333;
}

input {
    width: 100%;
    padding: 10px;
    font-size: 16px;
    border: 1px solid #ccc;
    border-radius: 5px;
}

input[readonly] {
    background-color: #e9ecef;
}

button {
    background-color: #85c1e9;
    color: white;
    font-size: 18px;
    padding: 10px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background 0.3s;
}

button:hover {
    background-color: #5a9bd6;
}

/* Links */
.links {
    margin-top: 20px;
}

.links a {
    text-decoration: none;
    color: #85c1e9;
    font-weight: bold;
}

.links a:hover {
    text-decoration: underline;
}
</style>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Passero+One&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
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
    <h2>Update Your Profile</h2>

    <form action="profile.php" method="POST">
    <label for="username">Username</label>
    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>

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

<?php if (!empty($success_message)): ?>
    <p class="success-message"><?php echo $success_message; ?></p>  <!-- Display success message -->
<?php endif; ?>

<?php if (!empty($error_message)): ?>
    <p class="error-message"><?php echo $error_message; ?></p>  <!-- Display error message -->
<?php endif; ?>

<div class="links">
    <p><a href="change_password.php">Change Password</a></p>

</div>
    </section>
</body>
</html>
