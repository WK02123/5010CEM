<?php
// Database connection
$host = 'localhost';  // Change if necessary
$username = 'root';   // Change if necessary
$password = '';       // Change if necessary
$dbname = 'enterprise';  // Change to your database name

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch the images based on position
$sql = "SELECT * FROM banner ORDER BY position ASC";
$result = $conn->query($sql);

$images = [];
while ($row = $result->fetch_assoc()) {
    $images[$row['position']] = $row['image'];
}

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Game Store</title>
    <link rel="stylesheet" href="homepage.css">
</head>
<body>
    <?php include 'header.php'; // Include the header ?>

    <!-- Banner Section -->
    <section class="banner">
        <div class="banner-text">
            <h1>
                <?php if (isset($_SESSION['username'])): ?>
                    Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!
                <?php else: ?>
                    Welcome to Game4Life!
                <?php endif; ?>
            </h1>
            <p>Discover the latest games and consoles at the best prices.</p>
            <a href="LoginRegister.php" class="btn">Register Now</a>
        </div>
    </section>

    <!-- Background Sections -->
    <section class="background-section" style="background-image: url('banner/<?php echo htmlspecialchars($images[1]); ?>');">
    </section>

    <section class="background-section2" style="background-image: url('banner/<?php echo htmlspecialchars($images[2]); ?>');">
    </section>

    <section class="featured-products">
        <h2>Featured Products</h2>
        <div class="product-grid">
            <div class="product-item">
                <img src="images/PS5.png" alt="PS5">
                <h3>PlayStation 5</h3>
                <p>RM 2,399</p>
                <a href="consoles.php?category=PS5&table=console" class="btn">Explore More</a>
            </div>
            <div class="product-item">
                <img src="images/switcholed.png" alt="Nintendo Switch">
                <h3>Nintendo Switch OLED</h3>
                <p>RM 1,299</p>
                <a href="consoles.php?category=Switch&table=console" class="btn">Explore More</a>
            </div>
        </div>
    </section>

    <!-- Footer Section -->
    <footer>
        <div class="footer-content">
            <p>&copy; 2024 Game4Life. All Rights Reserved.</p>
            <div class="social-icons">
                <a href="https://www.facebook.com" target="_blank">
                    <img src="facebookicon.png" alt="Facebook" />
                </a>
                <a href="https://www.twitter.com" target="_blank">
                    <img src="twittericon.png" alt="Twitter" />
                </a>
                <a href="https://www.instagram.com" target="_blank">
                    <img src="instagramicon.png" alt="Instagram" />
                </a>
                <a href="https://www.youtube.com" target="_blank">
                    <img src="youtubeicon.png" alt="YouTube" />
                </a>
            </div>
        </div>
    </footer>
</body>
</html>
