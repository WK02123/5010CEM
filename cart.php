<?php
session_start();
include 'db.php';

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Handle item removal
    if (isset($_POST['remove_item'])) {
        $cart_id = intval($_POST['cart_id']);
        $deleteQuery = "DELETE FROM cart WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($deleteQuery);
        $stmt->bind_param("ii", $cart_id, $user_id);
        $stmt->execute();
        $stmt->close();
    }

    // Fetch cart items with product details and stock
    $query = "
        SELECT c.id AS cart_id, c.product_id, c.quantity, c.table_name,
            CASE 
                WHEN c.table_name = 'games' THEN g.name
                WHEN c.table_name = 'console' THEN con.name
                WHEN c.table_name = 'accessories' THEN acc.name
            END AS product_name,
            CASE 
                WHEN c.table_name = 'games' THEN g.price
                WHEN c.table_name = 'console' THEN con.price
                WHEN c.table_name = 'accessories' THEN acc.price
            END AS product_price,
            CASE 
                WHEN c.table_name = 'games' THEN g.stock
                WHEN c.table_name = 'console' THEN con.stock
                WHEN c.table_name = 'accessories' THEN acc.stock
            END AS product_stock
        FROM cart AS c
        LEFT JOIN games AS g ON c.product_id = g.id AND c.table_name = 'games'
        LEFT JOIN console AS con ON c.product_id = con.id AND c.table_name = 'console'
        LEFT JOIN accessories AS acc ON c.product_id = acc.id AND c.table_name = 'accessories'
        WHERE c.user_id = ?
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $cart_items = [];
    $total = 0;

    while ($row = $result->fetch_assoc()) {
        if ($row['quantity'] > $row['product_stock']) {
            $new_quantity = $row['product_stock'];
            $updateQuery = "UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?";
            $stmtUpdate = $conn->prepare($updateQuery);
            $stmtUpdate->bind_param("iii", $new_quantity, $row['cart_id'], $user_id);
            $stmtUpdate->execute();
            $stmtUpdate->close();
    
            // Update the cart item to reflect the new quantity
            $row['quantity'] = $new_quantity;
        }

        $cart_items[] = $row;
        $total += $row['product_price'] * $row['quantity'];
    }
    $_SESSION['total'] = $total;

    $stmt->close();
} else {
    echo "<p>Please log in to view your cart.</p>";
    exit();
}

// Fetch distinct category names from the category table
$categoriesQuery = "SELECT DISTINCT name FROM category";
$categoriesResult = $conn->query($categoriesQuery);
$categories = [];

if ($categoriesResult->num_rows > 0) {
    while ($row = $categoriesResult->fetch_assoc()) {
        $categories[] = $row['name'];
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart</title>
    <link rel="stylesheet" href="cart.css">
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

    <div class="cart-container">
        <h2>Your Cart</h2>

        <?php if (count($cart_items) > 0): ?>
            <?php foreach ($cart_items as $item): ?>
                <div class="cart-item">
                    <div>
                        <p class="product-name"><?php echo htmlspecialchars($item['product_name']); ?></p>
                        <p class="product-price">Price: RM <?php echo number_format($item['product_price'], 2); ?></p>
                        <p class="product-quantity">Quantity: <strong><?php echo intval($item['quantity']); ?></strong></p>
                        <p class="product-stock">Available Stock: <?php echo intval($item['product_stock']); ?></p>
                    </div>

                    <!-- Button Group for Quantity and Remove Buttons -->
                    <div class="button-group">
                        <form method="post" action="cart.php">
                            <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                            <button type="submit" name="remove_item" class="remove-button">Remove</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>

            <p class="total">Total: RM <?php echo number_format($total, 2); ?></p>

            <!-- Cart Summary Form for Checkout -->
            <form method="post" action="payment.php">
                <?php foreach ($cart_items as $item): ?>
                    <input type="hidden" name="items[<?php echo $item['cart_id']; ?>][product_name]"
                        value="<?php echo htmlspecialchars($item['product_name']); ?>">
                    <input type="hidden" name="items[<?php echo $item['cart_id']; ?>][product_price]"
                        value="<?php echo htmlspecialchars($item['product_price']); ?>">
                    <input type="hidden" name="items[<?php echo $item['cart_id']; ?>][quantity]"
                        value="<?php echo intval($item['quantity']); ?>"> <!-- This is the adjusted quantity -->
                <?php endforeach; ?>
                <input type="hidden" name="total" value="<?php echo number_format($total, 2); ?>">
                <button type="submit" class="checkout-button">Proceed to Checkout</button>
            </form>


        <?php else: ?>
            <p>Your cart is empty.</p>
        <?php endif; ?>
    </div>
</body>

</html>