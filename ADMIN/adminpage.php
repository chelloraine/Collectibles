<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../login.html"); // Redirect to login if not logged in
    exit;
}

include '../connection.php'; 

// Handle user deletion securely
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']); // Ensure it's an integer
    $delete_stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $delete_stmt->bind_param("i", $delete_id);
    
    if ($delete_stmt->execute()) {
        $_SESSION['message'] = "User deleted successfully!";
        header("Location: adminpage.php");
        exit;
    } else {
        $_SESSION['error'] = "Error deleting user!";
    }
    $delete_stmt->close();
}

// Fetch all users
$sql = "SELECT id, first_name, last_name, email, username FROM users";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>

<h2>Admin Panel - User Management</h2>

<!-- Show messages -->
<?php if (isset($_SESSION['message'])): ?>
    <p class="message"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></p>
<?php endif; ?>
<?php if (isset($_SESSION['error'])): ?>
    <p class="error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
<?php endif; ?>

<div class="logout">
    <a href="../logout.php">Logout</a>  
</div>

<?php if ($result->num_rows > 0): ?>
    <table>
        <tr>
            <th>ID</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Email</th>
            <th>Username</th>
            <th>Action</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row["id"]); ?></td>
                <td><?php echo htmlspecialchars($row["first_name"]); ?></td>
                <td><?php echo htmlspecialchars($row["last_name"]); ?></td>
                <td><?php echo htmlspecialchars($row["email"]); ?></td>
                <td><?php echo htmlspecialchars($row["username"]); ?></td>
                <td>
                    <a href="adminpage.php?delete_id=<?php echo $row['id']; ?>" 
                       onclick="return confirm('Are you sure you want to delete this user?');">
                        <button class="delete-btn">Delete</button>
                    </a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
<?php else: ?>
    <p class="no-users">No users found!</p>
<?php endif; ?>

<?php $conn->close(); ?>
</body>
</html>
