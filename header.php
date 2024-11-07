<?php
session_start();
if (!isset($_SESSION['username'])) {
    // Optionally handle this case
}

// Database connection
$servername = "localhost"; // replace with your DB server
$username = "root"; // replace with your DB username
$password = ""; // replace with your DB password
$dbname = "enterprise"; // replace with your DB name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch distinct category names from the category table
$categoriesQuery = "SELECT DISTINCT name FROM category";
$categoriesResult = $conn->query($categoriesQuery);

// Array to store categories
$categories = [];

if ($categoriesResult->num_rows > 0) {
    while ($row = $categoriesResult->fetch_assoc()) {
        $categories[] = $row['name'];
    }
}

$conn->close();
?>

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
