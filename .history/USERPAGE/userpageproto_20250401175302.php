<?php
session_start();
include_"../connection.php";

// Redirect to login if not logged in
if (!isset($_SESSION['Customer_ID'])) {
    header("Location: login.php");
    exit();
}

// Fetch user information
$customer_id = $_SESSION['Customer_ID'];
$query = "SELECT First_Name FROM Customers WHERE Customer_ID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Fetch featured products
$products_query = "SELECT p.Product_Name, c.Category_Name, i.Image_Path 
                   FROM Products p 
                   JOIN Product_Categories c ON p.ProductType_ID = c.ProductType_ID 
                   JOIN Product_Images i ON p.Product_ID = i.Product_ID
                   GROUP BY p.Product_ID";
$products_result = $conn->query($products_query);

$featured_items = [];
while ($row = $products_result->fetch_assoc()) {
    $featured_items[] = [
        'image' => $row['Image_Path'],
        'title' => $row['Product_Name'],
        'category' => $row['Category_Name']
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Oshi Haven</title>
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
                    <li class="dropdown">
                        <a href="#">Products</a>
                        <ul class="dropdown-menu">
                            <li><a href="#">Figures</a></li>
                            <li><a href="#">Plushies</a></li>
                            <li><a href="#">Posters</a></li>
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
                <div class="icon"><a href="profile.php">Profile</a></div> 
                <div class="icon"><a href="cart.php">Cart</a></div>
                <div class="icon"><a href="settings.php">Settings</a></div>
            </div>
            <div class="user-info">
                <p>Welcome, <?php echo htmlspecialchars($user['First_Name']); ?>!</p>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </header>

    <section class="featured-container">
        <?php foreach ($featured_items as $item): ?>
            <div class="featured-item">
                <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">
                <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                <p><?php echo htmlspecialchars($item['category']); ?></p>
            </div>
        <?php endforeach; ?>
    </section>
</body>
</html>