<?php

//array for dropdown option sa navbar
$products = [
    'Hoodies' => 'hoodies.php',
    'T-Shirts' => 'tshirts.php',
    'Sweatpants' => 'sweatpants.php',
    'Hats' => 'hats.php'
];

//array for images
$featured_items = [
    ['image' => 'hololive friends to go - Nekomata Okayu Keychain.jpg', 'title' => 'Hololive', 'category' => 'HAORI'],
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
        nav ul {
            list-style: none;
            display: flex;
            gap: 20px;
        }
        nav ul li {
            position: relative;
        }
        nav ul li a {
            text-decoration: none;
            color: black;
            font-size: 18px;
            font-weight: bold;
            padding: 10px;
        }
        .dropdown-menu {
            display: none;
            position: absolute;
            background-color: white;
            box-shadow: 0px 8px 16px rgba(0,0,0,0.2);
            list-style: none;
            padding: 10px 0;
            width: 220px;
            border-radius: 5px;
            z-index: 10;
        }
        .dropdown-menu li {
            padding: 10px 20px;
        }
        .dropdown-menu li a {
            color: black;
            display: block;
            width: 100%;
        }
        .dropdown:hover .dropdown-menu {
            display: block;
        }
        .featured-container {
            width: 90%;
            margin: auto;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            padding: 40px 0;
        }
        .featured-item {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
        }
        .featured-item:hover { /*hover feature para sa cursor*/
            transform: scale(1.05);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }
        .featured-item img { /*hover feature para sa images*/
            width: 100%;
            border-radius: 10px;
        }
        .featured-item h3 {
            margin-top: 10px;
            font-size: 18px;
            color: #333;
        }
        .featured-item p {
            font-size: 14px;
            color: #777;
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
fonts.googleapis.com