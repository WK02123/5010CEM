<?php
session_start(); // Start the session to access session variables

include 'db.php';

// Fetch product ID from URL
$productID = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($productID <= 0) {
    echo "Invalid product.";
    exit();
}

// Fetch table from URL (either games, console, or accessories)
$table = isset($_GET['table']) ? $_GET['table'] : 'games';

// Validate the table name
$valid_tables = ['games', 'console', 'accessories'];
if (!in_array($table, $valid_tables)) {
    echo "Invalid product category.";
    exit();
}

// Fetch product details including stock from the table
$sql = "SELECT * FROM $table WHERE id = $productID";
if ($result = $conn->query($sql)) {
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
    } else {
        echo "Product not found.";
        exit();
    }
} else {
    echo "Error: " . $conn->error;
    exit();
}

// Fetch categories for dropdown
$categoriesQuery = "SELECT DISTINCT name FROM category";
$categoriesResult = $conn->query($categoriesQuery);
$categories = [];
if ($categoriesResult->num_rows > 0) {
    while ($row = $categoriesResult->fetch_assoc()) {
        $categories[] = $row['name'];
    }
}

// Add to Cart functionality
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    $_SESSION['cart'][$productID] = [
        'id' => $productID,
        'name' => $product['name'],
        'price' => $product['price'],
        'quantity' => $quantity,
    ];
    header("Location: cart.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> Details</title>
    <link rel="stylesheet" href="productdetails.css">
</head>

<body>
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

    <!-- Product Details Section -->
    <div class="product-container">
        <div class="product-image">
            <img src="images/<?php echo htmlspecialchars($product['image']); ?>"
                alt="<?php echo htmlspecialchars($product['name']); ?>">
        </div>
        <div class="product-info">
            <h1><?php echo htmlspecialchars($product['name']); ?></h1>
            <p class="category">Category: <strong><?php echo htmlspecialchars($product['category']); ?></strong></p>
            <p class="price">Price: <strong>RM <?php echo number_format($product['price'], 2); ?></strong></p>
            <p class="description"><?php echo htmlspecialchars($product['description']); ?></p>

            <!-- Display stock information -->
            <p class="stock">Stock Left: <strong><?php echo intval($product['stock']); ?></strong></p>

            <?php if ($table == 'games'): ?>
                <p class="genre"><strong>Genre:</strong> <?php echo htmlspecialchars($product['genre']); ?></p>
            <?php elseif ($table == 'console'): ?>
                <p class="storage"><strong>Storage:</strong> <?php echo htmlspecialchars($product['storage']); ?></p>
                <p class="ram"><strong>RAM:</strong> <?php echo htmlspecialchars($product['ram']); ?></p>
                <p class="processor"><strong>Processor:</strong> <?php echo htmlspecialchars($product['processor']); ?></p>
                <p class="controller"><strong>Controller:</strong> <?php echo htmlspecialchars($product['controller']); ?>
                </p>
            <?php elseif ($table == 'accessories'): ?>
                <p class="accessory-details"><strong>Details:</strong> Accessory details specific to the accessory category.
                </p>
            <?php endif; ?>

            <!-- Add to Cart Form -->
            <form method="post" action="add_to_cart.php">
                <input type="hidden" name="product_id" value="<?php echo $productID; ?>">
                <input type="hidden" name="table" value="<?php echo $table; ?>">
                <div class="quantity-selector">
                    <label for="quantity">Quantity:</label>
                    <input type="number" id="quantity" name="quantity" value="1" min="1"
                        max="<?php echo intval($product['stock']); ?>">
                </div>
                <button type="submit" name="add_to_cart" class="add-to-cart-button">Add to Cart</button>
            </form>

        </div>
    </div>

</body>

</html>

<?php
$conn->close();
?>