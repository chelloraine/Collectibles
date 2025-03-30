<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../login.html"); // Redirect to login if not logged in
    exit;
}

include '../connection.php';

// Handle user deletion securely (bulk delete included)
if (isset($_POST['delete_selected'])) {
    if (!empty($_POST['user_ids'])) {
        $ids = implode(',', array_map('intval', $_POST['user_ids']));
        $conn->query("DELETE FROM users WHERE id IN ($ids)");
        $_SESSION['message'] = "Selected users deleted successfully!";
        header("Location: adminpage.php");
        exit;
    }
}

// Handle role update
if (isset($_POST['update_role'])) {
    $user_id = intval($_POST['user_id']);
    $new_role = $_POST['role'];
    $conn->query("UPDATE users SET role='$new_role' WHERE id=$user_id");
    $_SESSION['message'] = "User role updated!";
}

// Fetch all users
$sql = "SELECT id, first_name, last_name, email, username, role, status FROM users";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="admin.css">
    <script>
        function confirmDelete() {
            return confirm('Are you sure you want to delete selected users?');
        }
    </script>
</head>
<body>
    <h2>Admin Panel - User Management</h2>
    
    <!-- Show messages -->
    <?php if (isset($_SESSION['message'])): ?>
        <p class="message"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></p>
    <?php endif; ?>
    
    <div class="logout">
        <a href="../logout.php">Logout</a>  
    </div>
    
    <input type="text" id="search" placeholder="Search users..." onkeyup="searchUsers()">
    
    <form method="POST" onsubmit="return confirmDelete();">
        <table>
            <tr>
                <th>Select</th>
                <th>ID</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Email</th>
                <th>Username</th>
                <th>Role</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><input type="checkbox" name="user_ids[]" value="<?php echo $row['id']; ?>"></td>
                    <td><?php echo htmlspecialchars($row["id"]); ?></td>
                    <td><?php echo htmlspecialchars($row["first_name"]); ?></td>
                    <td><?php echo htmlspecialchars($row["last_name"]); ?></td>
                    <td><?php echo htmlspecialchars($row["email"]); ?></td>
                    <td><?php echo htmlspecialchars($row["username"]); ?></td>
                    <td>
                        <form method="POST">
                            <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                            <select name="role" onchange="this.form.submit()">
                                <option value="admin" <?php if ($row['role'] == 'admin') echo 'selected'; ?>>Admin</option>
                                <option value="user" <?php if ($row['role'] == 'user') echo 'selected'; ?>>User</option>
                                <option value="moderator" <?php if ($row['role'] == 'moderator') echo 'selected'; ?>>Moderator</option>
                            </select>
                            <input type="hidden" name="update_role" value="1">
                        </form>
                    </td>
                    <td>
                        <form method="POST">
                            <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                            <select name="status" onchange="this.form.submit()">
                                <option value="active" <?php if ($row['status'] == 'active') echo 'selected'; ?>>Active</option>
                                <option value="inactive" <?php if ($row['status'] == 'inactive') echo 'selected'; ?>>Inactive</option>
                                <option value="banned" <?php if ($row['status'] == 'banned') echo 'selected'; ?>>Banned</option>
                            </select>
                        </form>
                    </td>
                    <td>
                        <a href="adminpage.php?delete_id=<?php echo $row['id']; ?>" onclick="return confirm('Delete this user?');">
                            <button class="delete-btn">Delete</button>
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
        <button type="submit" name="delete_selected">Delete Selected Users</button>
    </form>
    
    <script>
        function searchUsers() {
            let input = document.getElementById("search").value.toLowerCase();
            let rows = document.querySelectorAll("table tr");
            
            for (let i = 1; i < rows.length; i++) {
                let cells = rows[i].getElementsByTagName("td");
                let match = false;
                for (let j = 1; j < cells.length - 2; j++) {
                    if (cells[j].innerText.toLowerCase().includes(input)) {
                        match = true;
                        break;
                    }
                }
                rows[i].style.display = match ? "" : "none";
            }
        }
    </script>
</body>
</html>
