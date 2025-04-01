<?php
// Database connection
include "../connection.php";

// Array for dropdown option sa navbar
$products = [
    'Hoodies' => 'hoodies.php',
    'T-Shirts' => 'tshirts.php',
    'Sweatpants' => 'sweatpants.php',
    'Hats' => 'hats.php'
];

$featured_items = [
    ['image' => '..\images\Products\hololive friends with u - Ookami Mio.jpg', 'title' => 'Hololive', 'category' => 'HAORI'],
    ['image' => 'hololive friends to go - Ookami Mio Keychain.jpg', 'title' => 'VShojo', 'category' => 'COLLECTION'],
    ['image' => 'hololive friends with u - Inugami Korone Street Outfit, Front.jpg', 'title' => 'Nijisanji', 'category' => 'HAORI'],
    ['image' => 'hololive friends with u - Ookami Mio.jpg', 'title' => 'Indie Vtuber', 'category' => 'MERCH DROP'],
    ['image' => 'hololive friends with u - Robocosan.jpg', 'title' => 'Creator 5', 'category' => 'SPECIAL EDITION'],
    ['image' => 'hololive friends with u - Sakura Miko.jpg', 'title' => 'Creator 6', 'category' => 'LIMITED EDITION']
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
    <img src="../images/logo.png" alt="Logo" class="logo" style="width: 150px; height: 100px;">
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
        <?php foreach ($featured_items as $item): ?>
            <div class="featured-item">
                <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['title']; ?>">
                <h3><?php echo $item['title']; ?></h3>
                <p><?php echo $item['category']; ?></p>
            </div>
        <?php endforeach; ?>
    </section>
</body>
</html>
