<?php
session_start(); // Start the session to access session variables

include 'db.php';

// Get the selected category from the URL, or default to 'PS5'
$category = isset($_GET['category']) ? $_GET['category'] : 'PS5';

// Fetch consoles based on the selected category
$sql = "SELECT * FROM console WHERE category = '$category'";
$result = $conn->query($sql);

// Fetch distinct category names from the category table
$categoriesQuery = "SELECT DISTINCT name FROM category";
$categoriesResult = $conn->query($categoriesQuery);
$categories = [];

if ($categoriesResult->num_rows > 0) {
    while ($row = $categoriesResult->fetch_assoc()) {
        $categories[] = $row['name'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consoles List - <?php echo htmlspecialchars($category); ?></title>
    <link rel="stylesheet" href="productlist.css">
</head>
<div>
    <!-- Header Section from the homepage -->
    <header>
        <nav>
            <div class="logo">
                <a href="homepage.php">
                    <img src="logo.png" alt="Game4Life Logo">
                </a>
            </div>
            <ul>
                <!-- Games Dropdown -->
                <li class="dropdown">
                    <a href="javascript:void(0)" class="dropbtn">Games</a>
                    <div class="dropdown-content">
                        <?php foreach ($categories as $category): ?>
                            <a href="games.php?category=<?php echo urlencode($category); ?>&table=games">
                                <?php echo htmlspecialchars($category); ?> Games
                            </a>
                        <?php endforeach; ?>
                    </div>
                </li>
                <!-- Consoles Dropdown -->
                <li class="dropdown">
                    <a href="javascript:void(0)" class="dropbtn">Consoles</a>
                    <div class="dropdown-content">
                        <?php foreach ($categories as $category): ?>
                            <a href="consoles.php?category=<?php echo urlencode($category); ?>&table=console">
                                <?php echo htmlspecialchars($category); ?> Consoles
                            </a>
                        <?php endforeach; ?>
                    </div>
                </li>
                <!-- Accessories Dropdown -->
                <li class="dropdown">
                    <a href="javascript:void(0)" class="dropbtn">Accessories</a>
                    <div class="dropdown-content">
                        <?php foreach ($categories as $category): ?>
                            <a href="accessories.php?category=<?php echo urlencode($category); ?>&table=accessories">
                                <?php echo htmlspecialchars($category); ?> Accessories
                            </a>
                        <?php endforeach; ?>
                    </div>
                </li>
                <!-- Compare Button -->
                <li>
                    <a href="compare.php" class="compare-btn">Compare</a>
                </li>
                <!-- Account Dropdown -->
                <li class="dropdown">
                    <a href="javascript:void(0)" class="dropbtn">Account</a>
                    <div class="dropdown-content">
                        <?php if (isset($_SESSION['username'])): ?>
                            <a href="profile.php">Profile</a>
                            <a href="cart.php">Cart</a>
                            <a href="logout.php">Logout</a>
                        <?php else: ?>
                            <a href="LoginRegister.php">Login</a>
                            <a href="LoginRegister.php">Register</a>
                        <?php endif; ?>
                    </div>
                </li>
            </ul>
        </nav>
    </header>

    <!-- Page Content -->
    <h1>List of the Consoles:</h1>

    <div class="product-list">
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo '<div class="product-item">';
                echo '<a href="productdetails.php?id=' . $row["id"] . '&table=console">'; // Change 'accessories' to 'console'
                echo '<img src="images/' . htmlspecialchars($row["image"]) . '" alt="' . htmlspecialchars($row["name"]) . '" loading="lazy">';
                echo '<h3>' . htmlspecialchars($row["name"]) . '</h3>';
                echo '<p>Price: RM ' . number_format($row["price"], 2) . '</p>';
                echo '</a>';
                echo '</div>';
            }
        } else {
            echo "0 products found";
        }
        ?>
    </div>
</div>
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

<?php
$conn->close();
?>