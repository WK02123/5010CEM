<?php
session_start();

$host = 'localhost';
$dbname = 'enterprise';
$username = 'root';
$password = '';

$conn = new mysqli($host, $username, $password, $dbname);

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: LoginRegister.php");
    exit;
}

$categories = [];
$categoryResult = $conn->query("SELECT name FROM category");
if ($categoryResult && $categoryResult->num_rows > 0) {
    while ($row = $categoryResult->fetch_assoc()) {
        $categories[] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = $_POST['id'];

    if (isset($_POST['delete'])) {
        $productType = $_POST['productType'];
        $stmt = $conn->prepare("DELETE FROM $productType WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            echo "Product deleted successfully.";
        } else {
            echo "Error deleting product: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $productType = $_POST['productType'];
        $name = $_POST['name'];
        $price = $_POST['price'];
        $description = $_POST['description'];
        $category = $_POST['category'];

        $imageName = '';
        if (!empty($_FILES['image']['name'])) {
            if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $image = $_FILES['image']['name'];
                $imageName = basename($image);
                if (move_uploaded_file($_FILES['image']['tmp_name'], 'images/' . $imageName)) {
                    echo "Image uploaded: " . $imageName . "<br>";
                } else {
                    echo "Error uploading image.<br>";
                    exit;
                }
            } else {
                echo "File upload error: " . $_FILES['image']['error'] . "<br>";
                exit;
            }
        } else {
            $stmt = $conn->prepare("SELECT image FROM $productType WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $imageName = $row['image'];
            echo "No new image uploaded. Retaining old image name: " . $imageName . "<br>";
        }

        if ($productType == "console") {
            $storage = isset($_POST['storage']) ? $_POST['storage'] : null;
            $ram = isset($_POST['ram']) ? $_POST['ram'] : null;
            $processor = isset($_POST['processor']) ? $_POST['processor'] : null;
            $controller = isset($_POST['controller']) ? $_POST['controller'] : null;

            $stmt = $conn->prepare("UPDATE console SET name = ?, price = ?, description = ?, image = ?, storage = ?, ram = ?, processor = ?, controller = ?, category = ? WHERE id = ?");
            $stmt->bind_param("sdsssisssi", $name, $price, $description, $imageName, $storage, $ram, $processor, $controller, $category, $id);
        } elseif ($productType == "games") {
            $stmt = $conn->prepare("UPDATE games SET name = ?, price = ?, description = ?, image = ?, category = ? WHERE id = ?");
            $stmt->bind_param("sdsssi", $name, $price, $description, $imageName, $category, $id);
        } elseif ($productType == "accessories") {
            $stmt = $conn->prepare("UPDATE accessories SET name = ?, price = ?, description = ?, image = ?, category = ? WHERE id = ?");
            $stmt->bind_param("sdsssi", $name, $price, $description, $imageName, $category, $id);
        }

        if ($stmt->execute()) {
            echo "Product updated successfully.";
        } else {
            echo "Error updating product: " . $stmt->error;
        }
        $stmt->close();
    }
}

$products = [];
$productType = isset($_GET['productType']) ? $_GET['productType'] : '';

if (!empty($productType)) {
    if ($productType == "console") {
        $result = $conn->query("SELECT id, name FROM console");
    } elseif ($productType == "games") {
        $result = $conn->query("SELECT id, name FROM games");
    } elseif ($productType == "accessories") {
        $result = $conn->query("SELECT id, name FROM accessories");
    }

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }
}

$productData = null;
if (isset($_GET['id']) && !empty($productType)) {
    $id = $_GET['id'];

    if ($productType == "console") {
        $stmt = $conn->prepare("SELECT * FROM console WHERE id = ?");
    } elseif ($productType == "games") {
        $stmt = $conn->prepare("SELECT * FROM games WHERE id = ?");
    } elseif ($productType == "accessories") {
        $stmt = $conn->prepare("SELECT * FROM accessories WHERE id = ?");
    }

    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $productData = $result->fetch_assoc();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - Admin</title>
    <link rel="stylesheet" href="admineditproducts.css">
    <script>
        function loadProducts() {
            document.getElementById('productForm').submit();
        }

        function confirmDeletion() {
            return confirm("Are you sure you want to delete this product?");
        }
    </script>
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


    <section class="edit-product">
        <h2>Edit Product</h2>

        <form id="productForm" method="get" action="<?php echo $_SERVER['PHP_SELF']; ?>">
            <label for="productType">Product Category</label>
            <select id="productType" name="productType" onchange="loadProducts()" required>
                <option value="">Select Category</option>
                <option value="games" <?php if ($productType == 'games')
                    echo 'selected'; ?>>Games</option>
                <option value="console" <?php if ($productType == 'console')
                    echo 'selected'; ?>>Console</option>
                <option value="accessories" <?php if ($productType == 'accessories')
                    echo 'selected'; ?>>Accessories
                </option>
            </select>
        </form>

        <?php if (!empty($products)) { ?>
            <form id="selectProductForm" method="get" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                <input type="hidden" name="productType" value="<?php echo $productType; ?>">
                <label for="productId">Select Product to Edit</label>
                <select id="productId" name="id" onchange="document.getElementById('selectProductForm').submit()" required>
                    <option value="">Select Product</option>
                    <?php foreach ($products as $product) { ?>
                        <option value="<?php echo $product['id']; ?>"><?php echo $product['name']; ?></option>
                    <?php } ?>
                </select>
            </form>
        <?php } ?>

        <?php if (!empty($productData)) { ?>
            <form id="editProductForm" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post"
                enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?php echo $productData['id']; ?>">
                <input type="hidden" name="productType" value="<?php echo $productType; ?>">

                <label for="name">Product Name</label>
                <input type="text" id="name" name="name" value="<?php echo $productData['name']; ?>" required>

                <label for="price">Price</label>
                <input type="number" id="price" name="price" step="0.01" value="<?php echo $productData['price']; ?>"
                    required>

                <label for="description">Description</label>
                <textarea id="description" name="description" required><?php echo $productData['description']; ?></textarea>

                <label for="category">Category</label>
                <select id="category" name="category" required>
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $category) { ?>
                        <option value="<?php echo $category['name']; ?>" <?php if ($productData['category'] === $category['name'])
                               echo 'selected'; ?>><?php echo $category['name']; ?></option>
                    <?php } ?>
                </select>

                <label for="image">Image</label>
                <input type="file" id="image" name="image" accept="image/*">
                <p>Current Image: <?php echo $productData['image']; ?></p>

                <?php if ($productType == "console") { ?>
                    <label for="storage">Storage</label>
                    <input type="text" id="storage" name="storage" value="<?php echo $productData['storage']; ?>">

                    <label for="ram">RAM</label>
                    <input type="text" id="ram" name="ram" value="<?php echo $productData['ram']; ?>">

                    <label for="processor">Processor</label>
                    <input type="text" id="processor" name="processor" value="<?php echo $productData['processor']; ?>">

                    <label for="controller">Controller</label>
                    <input type="text" id="controller" name="controller" value="<?php echo $productData['controller']; ?>">
                <?php } ?>

                <button type="submit">Update Product</button>
                <button type="delete" name="delete" onclick="return confirmDeletion()">Delete Product</button>
            </form>
        <?php } ?>
    </section>
</body>

</html>