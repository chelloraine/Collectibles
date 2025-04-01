<?php
// Database connection
include "../connection.php";

// Fetch products with images
$query = "
    SELECT p.Product_ID, p.Product_Name, p.Product_Quantity, pi.Image_Path, c.Category_Name
    FROM Products p
    JOIN Product_Images pi ON p.Product_ID = pi.Product_ID
    JOIN Product_Categories c ON p.ProductType_ID = c.ProductType_ID
    LIMIT 6"; // Show 6 featured items
$result = mysqli_query($conn, $query);

$featured_items = [];
while ($row = mysqli_fetch_assoc($result)) {
    $featured_items[] = $row;
}

// Array for dropdown option sa navbar
$products = [
    'Hoodies' => 'hoodies.php',
    'T-Shirts' => 'tshirts.php',
    'Sweatpants' => 'sweatpants.php',
    'Hats' => 'hats.php'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Oshi Haven</title>
    <link rel="stylesheet" href="homepage.css">
    <script defer src="script.js"></script>
    <link href='https://fonts.googleapis.com/css?family=Passero One' rel='stylesheet'>
</head>
<body>
    <header>
        <img src="../logo.png" alt="Logo" class="logo">
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
        <?php if (!empty($featured_items)): ?>
            <?php foreach ($featured_items as $item): ?>
                <div class="featured-item">
                    <img src="uploads/<?php echo $item['Image_Path']; ?>" alt="<?php echo $item['Product_Name']; ?>">
                    <h3><?php echo $item['Product_Name']; ?></h3>
                    <p><?php echo $item['Category_Name']; ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No featured products available.</p>
        <?php endif; ?>
    </section>
</body>
</html>
