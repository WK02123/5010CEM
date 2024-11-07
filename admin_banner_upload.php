<?php
session_start();

// Check if the user is an admin
//if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
//    die("Access denied.");
//}

// Database connection
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'enterprise';

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle the image upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['background_image'])) {
    $position = isset($_POST['position']) ? (int) $_POST['position'] : 1; // Get the position (1 or 2)
    $target_dir = "banner/";
    $target_file = $target_dir . basename($_FILES["background_image"]["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if the image file is a valid image
    if (getimagesize($_FILES["background_image"]["tmp_name"])) {
        // Move the uploaded file to the 'banner' folder
        if (move_uploaded_file($_FILES["background_image"]["tmp_name"], $target_file)) {
            // Update the image name in the database for the correct position
            $sql = "UPDATE banner SET image = '" . basename($_FILES["background_image"]["name"]) . "' WHERE position = $position";
            if ($conn->query($sql) === TRUE) {
                echo "Image uploaded and saved in the database.";
            } else {
                echo "Error: " . $sql . "<br>" . $conn->error;
            }
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    } else {
        echo "File is not an image.";
    }
}


// Fetch the images based on position
$sql = "SELECT * FROM banner ORDER BY position ASC";
$result = $conn->query($sql);
$images = [];
while ($row = $result->fetch_assoc()) {
    $images[$row['position']] = $row['image'];
}

// Fetch existing categories
$categories = [];
$result = $conn->query("SELECT name FROM category");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Banner Upload</title>
    <link rel="stylesheet" href="admin_banner_upload.css">
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

    <!-- Banner Sections for Admin Upload -->
    <section class="banner">
        <div class="background-section"
            style="background-image: url('banner/<?php echo htmlspecialchars($images[1]); ?>');">
            <div class="overlay">
                <form action="admin_banner_upload.php" method="post" enctype="multipart/form-data" class="upload-form">
                    <input type="hidden" name="position" value="1" /> <!-- For the first background -->
                    <input type="file" name="background_image" accept="image/*" required />
                    <input type="submit" value="Upload New Image" />
                </form>
            </div>
        </div>
    </section>

    <section class="background-section2"
        style="background-image: url('banner/<?php echo htmlspecialchars($images[2]); ?>');">
        <div class="overlay">
            <form action="admin_banner_upload.php" method="post" enctype="multipart/form-data" class="upload-form">
                <input type="hidden" name="position" value="2" /> <!-- For the second background -->
                <input type="file" name="background_image" accept="image/*" required />
                <input type="submit" value="Upload New Image" />
            </form>
        </div>
    </section>

</body>

</html>