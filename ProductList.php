<?php
include 'db.php'; // This includes the database connection

// Query to get all products
$sql = "SELECT * FROM products";

// Execute query and check if it was successful
if ($result = $conn->query($sql)) {
    // Proceed with the rest of the code if query execution is successful
} else {
    // Output error message if the query failed
    echo "Error: " . $conn->error;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product List</title>
    <link rel="stylesheet" href="productlist.css">
</head>

<body>
    <?php include 'header.php'; // Include the header ?>
    <h1>Product List</h1>
    <div class="product-list">
        <?php
        if ($result->num_rows > 0) {
            // Output data of each row
            while ($row = $result->fetch_assoc()) {
                echo '<div class="product-item">';
                echo '<img src="' . $row["image"] . '" alt="' . $row["name"] . '" loading="lazy">';
                echo '<h3>' . $row["name"] . '</h3>';
                echo '<p>Price: RM ' . $row["price"] . '</p>';
                echo '</div>';
            }
        } else {
            echo "0 products found";
        }
        ?>
    </div>
</body>

</html>

<?php
// Close connection
$conn->close();
?>