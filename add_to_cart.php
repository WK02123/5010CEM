<?php
session_start();
include 'db.php';

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $table_name = isset($_POST['table']) ? $_POST['table'] : '';
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

    if ($product_id > 0 && in_array($table_name, ['games', 'console', 'accessories'])) {
        // Check if the item is already in the cart
        $checkQuery = "SELECT id FROM cart WHERE user_id = ? AND product_id = ? AND table_name = ?";
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param("iis", $user_id, $product_id, $table_name);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // If item exists, update quantity
            $updateQuery = "UPDATE cart SET quantity = quantity + ? WHERE user_id = ? AND product_id = ? AND table_name = ?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("iiis", $quantity, $user_id, $product_id, $table_name);
        } else {
            // If item doesn't exist, insert a new record
            $insertQuery = "INSERT INTO cart (user_id, product_id, table_name, quantity) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($insertQuery);
            $stmt->bind_param("iisi", $user_id, $product_id, $table_name, $quantity);
        }

        if ($stmt->execute()) {
            header("Location: cart.php");
        } else {
            echo "Error: " . $conn->error;
        }
        $stmt->close();
    } else {
        echo "Invalid product or category.";
    }
} else {
    // Show a popup message and redirect to the login page after 3 seconds
    echo '<script type="text/javascript">
            alert("Please log in to add items to your cart.");
            setTimeout(function() {
                window.location.href = "LoginRegister.php";
            }, 1000);
          </script>';
}

$conn->close();
?>
