<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        echo "<p>Please log in to proceed.</p>";
        exit();
    }

    // Retrieve user input
    $user_id = $_SESSION['user_id'];
    $full_name = $_POST['firstname'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $zip = $_POST['zip'];
    $total_amount = $_SESSION['total']; // Use session total from cart if set

    // File upload handling
    $uploadDir = 'uploads/'; // Ensure this directory exists and is writable
    $fileName = basename($_FILES['picture']['name']);
    $targetFilePath = $uploadDir . $fileName;
    $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
    
    // Validate file type (optional)
    $allowedTypes = ['jpg', 'png', 'jpeg', 'gif'];
    if (in_array($fileType, $allowedTypes)) {
        // Move the uploaded file to the uploads directory
        if (move_uploaded_file($_FILES['picture']['tmp_name'], $targetFilePath)) {
            // Insert payment details into 'payments' table, including the image name
            $query = "INSERT INTO payments (user_id, full_name, email, address, city, state, zip, total_amount, receipt_image) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param("issssssds", $user_id, $full_name, $email, $address, $city, $state, $zip, $total_amount, $fileName);

            if ($stmt->execute()) {
                $payment_id = $stmt->insert_id; // Get the ID of the newly inserted payment record
                
                // Now, insert cart items into 'payment_items' table
                $cart_items_query = "
                    SELECT c.product_id, c.quantity, c.table_name 
                    FROM cart AS c 
                    WHERE c.user_id = ?";
                
                $cart_stmt = $conn->prepare($cart_items_query);
                $cart_stmt->bind_param("i", $user_id);
                $cart_stmt->execute();
                $cart_result = $cart_stmt->get_result();

                while ($cart_item = $cart_result->fetch_assoc()) {
                    $insert_item_query = "INSERT INTO payment_items (payment_id, product_id, quantity, table_name) 
                                          VALUES (?, ?, ?, ?)";
                    $item_stmt = $conn->prepare($insert_item_query);
                    $item_stmt->bind_param("iiis", $payment_id, $cart_item['product_id'], $cart_item['quantity'], $cart_item['table_name']);
                    $item_stmt->execute();
                    $item_stmt->close();
                }

                // Clear the cart after payment (optional)
                $clear_cart_query = "DELETE FROM cart WHERE user_id = ?";
                $clear_cart_stmt = $conn->prepare($clear_cart_query);
                $clear_cart_stmt->bind_param("i", $user_id);
                $clear_cart_stmt->execute();
                $clear_cart_stmt->close();

                // Redirect to homepage after successful payment
                header("Location: confirmation.php");
                exit(); // Ensure the script stops executing after the redirect
            } else {
                echo "<p>There was an error processing your payment. Please try again.</p>";
            }

            $stmt->close();
        } else {
            echo "<p>File upload failed. Please try again.</p>";
        }
    } else {
        echo "<p>Invalid file type. Only JPG, PNG, JPEG, and GIF files are allowed.</p>";
    }

    $conn->close();
}
?>
