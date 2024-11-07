<?php
session_start();

// Clear the cart
if (isset($_SESSION['cart'])) {
    unset($_SESSION['cart']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart Cleared</title>
    <link rel="stylesheet" href="cart.css">
</head>
<body>
    <header>
        <h1>Your Cart Has Been Cleared</h1>
    </header>
    <main>
        <p>Your shopping cart is now empty.</p>

        <!-- Change button to a link to games.php -->
        <a href="games.php" class="continue-shopping-button">Continue Shopping</a>
        <a href="clear_cart.php" class="clear-cart-link">Clear Cart</a> <!-- You can keep this for clarity -->
    </main>
</body>
</html>