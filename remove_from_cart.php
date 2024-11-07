<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: LoginRegister.php');
    exit();
}

if (isset($_GET['id'])) {
    $productId = $_GET['id'];
    unset($_SESSION['cart'][$productId]); // Remove item from cart
}

// Redirect back to cart page
header('Location: cart.php');
exit();
?>
