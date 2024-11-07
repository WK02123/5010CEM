<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header('Location: admin_login.php'); 
    exit;
}

// Handle image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['banner_image']) && $_FILES['banner_image']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['banner_image']['tmp_name'];
        $fileName = $_FILES['banner_image']['name'];
        $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($fileExtension, $allowedExtensions)) {
            $uploadFileDir = 'uploads/';
            $newFileName = 'TopBanner.' . $fileExtension;
            $destPath = $uploadFileDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $destPath)) {
                $message = 'Banner image successfully uploaded.';
            } else {
                $message = 'There was an error uploading the banner.';
            }
        } else {
            $message = 'Invalid file type. Only JPG, PNG, and GIF files are allowed.';
        }
    } else {
        $message = 'No file uploaded or there was an upload error.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Change Banner</title>
    <link rel="stylesheet" href="admin.css">
</head>

<body>
    <header>
        <h1>Admin Panel - Change Banner</h1>
    </header>
    
    <main>
        <form action="" method="POST" enctype="multipart/form-data">
            <label for="banner_image">Upload New Banner Image:</label>
            <input type="file" name="banner_image" id="banner_image" required>
            <button type="submit">Upload</button>
        </form>

        <?php if (isset($message)): ?>
            <p><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <section class="current-banner">
            <h2>Current Banner Image:</h2>
            <img src="uploads/TopBanner.png" alt="Current Banner" width="500px">
        </section>
    </main>

    <footer>
        <p>&copy; 2024 Game4Life Admin Panel</p>
    </footer>
</body>

</html>
