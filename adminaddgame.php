<?php
session_start();

$host = 'localhost';
$dbname = 'enterprise';
$username = 'root';
$password = '';

$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: LoginRegister.php");
    exit;
}

// Handle form submission for adding game
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['name'])) {
    $target_dir = "images/";
    $target_file = $target_dir . basename($_FILES["image"]["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    $check = getimagesize($_FILES["image"]["tmp_name"]);
    if ($check === false) {
        echo "<script>alert('File is not an image.');</script>";
        exit();
    }

    if ($_FILES["image"]["size"] > 5000000) {
        echo "<script>alert('Sorry, your file is too large.');</script>";
        exit();
    }

    if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
        echo "<script>alert('Sorry, only JPG, JPEG, PNG & GIF files are allowed.');</script>";
        exit();
    }

    if (!move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        echo "<script>alert('Sorry, there was an error uploading your file.');</script>";
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO games (name, description, price, genre, category, image) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdsss", $name, $description, $price, $genre, $category, $image_name);

    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $genre = $_POST['genre'];
    $category = $_POST['category'];
    $image_name = basename($_FILES["image"]["name"]);

    if ($stmt->execute()) {
        echo "<script>alert('New game added successfully');</script>";
    } else {
        echo "<script>alert('Error: " . $stmt->error . "');</script>";
    }

    $stmt->close();
}

// Handle new category submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['new_category'])) {
    $new_category = $_POST['new_category'];

    // Insert the new category into the category table
    $stmt = $conn->prepare("INSERT INTO category (name) VALUES (?)");
    $stmt->bind_param("s", $new_category);

    if ($stmt->execute()) {
        echo "<script>alert('New category added successfully');</script>";
    } else {
        echo "<script>alert('Error: " . $stmt->error . "');</script>";
    }

    $stmt->close();
}

// Handle category deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_category'])) {
    $delete_category = $_POST['delete_category'];

    // Prepare the delete statement
    $stmt = $conn->prepare("DELETE FROM category WHERE name = ?");
    $stmt->bind_param("s", $delete_category);

    if ($stmt->execute()) {
        echo "<script>alert('Category deleted successfully');</script>";
    } else {
        echo "<script>alert('Error: " . $stmt->error . "');</script>";
    }

    $stmt->close();
}

// Fetch existing categories
$categories = [];
$result = $conn->query("SELECT name FROM category");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Game - Admin</title>
    <link rel="stylesheet" href="adminaddgame.css">
</head>

<body>
    <header>
        <nav>
            <div class="logo">
                <img src="logo.png" alt="Game4Life Logo">
            </div>
            <ul>
                <li><a href="admin_dashboard.php">Dashboard</a></li>
                <li><a href="admin_banner_upload.php">Banner</a></li>
                <li><a href="admineditproducts.php">Edit Product</a></li>
                <!-- Add dropdown for Add section -->
                <li class="dropdown">
                    <a href="javascript:void(0)" class="dropbtn">Add</a>
                    <div class="dropdown-content">
                        <a href="adminaddconsole.php">Add Console</a>
                        <a href="adminaddgame.php">Add Game</a>
                        <a href="adminaddaccessory.php">Add Accessory</a>
                    </div>
                </li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <section class="add-game">
        <h2>Add New Game</h2>
        <form method="POST" action="" enctype="multipart/form-data">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" required>

            <label for="description">Description:</label>
            <textarea id="description" name="description" required></textarea>

            <label for="price">Price:</label>
            <input type="number" id="price" name="price" step="0.01" required>

            <label for="genre">Genre:</label>
            <input type="text" id="genre" name="genre" required>

            <label for="image">Category:</label>
            <select id="category" name="category" required>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['name'] ?>"><?= $cat['name'] ?></option>
                <?php endforeach; ?>
            </select>

            <label for="image">Upload Image:</label>
            <input type="file" id="image" name="image" required>

            <button type="submit" class="btn">Add Game</button>
            <hr style="border: 1px solid #ccc; margin-top: 20px; margin-bottom: 5px;">
        </form>

        <!-- Form to add a new category -->
        <h3>Add New Category</h3>
        <form method="POST" action="">
            <label for="new_category">New Category Name:</label>
            <input type="text" id="new_category" name="new_category" required>
            <button type="submit">Add Category</button>
            <hr style="border: 1px solid #ccc; margin-top: 20px; margin-bottom: 5px;">
        </form>

        <!-- Form to delete a category -->
        <h3>Delete Category</h3>
        <form method="POST" action="">
            <label for="delete_category">Select Category to Delete:</label>
            <select id="delete_category" name="delete_category" required>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['name'] ?>"><?= $cat['name'] ?></option> <!-- Use category 'name' -->
                <?php endforeach; ?>
            </select>
            <button type="submit">Delete Category</button>
        </form>
    </section>

</body>

</html>