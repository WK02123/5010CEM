<?php
$host = 'localhost';
$dbname = 'enterprise';
$username = 'root';
$password = '';

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT id, name, price, description, storage, ram, processor, category, image FROM console";
$result = $conn->query($sql);

if ($result === false) {
    die("SQL Error: " . $conn->error);
}

// Modify the image path in the products array
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $row['image'] = "images/" . $row['image'];  // Prepend images/ to the image name
        $products[] = $row;
    }
}


// Fetch distinct category names from the category table
$categoriesQuery = "SELECT DISTINCT name FROM category";
$categoriesResult = $conn->query($categoriesQuery);

// Array to store categories
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
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="Description" content="Enter your description here" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.1.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="compare.css">
    <style>
        td>img {
            width: 100%;
            height: 250px;
            object-fit: contain;
        }
    </style>
    <title>Compare Consoles</title>
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



    <h1 class="display-5 my-5 text-center">Compare Consoles</h1>
    <div class="container">
        <div class="col-md-9 mx-auto">
            <table class="table">
                <tr class="bg-light">
                    <th>Select Product</th>
                    <th width="300px">
                        <select class="form-control" id="select1" onchange="item1(this.value)">
                            <option value="0">-- Select Anyone --</option>
                            <?php foreach ($products as $index => $product): ?>
                                <option value="<?= $index + 1 ?>"><?= $product['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </th>
                    <th width="300px">
                        <select class="form-control" id="select2" onchange="item2(this.value)">
                            <option value="0">-- Select Anyone --</option>
                            <?php foreach ($products as $index => $product): ?>
                                <option value="<?= $index + 1 ?>"><?= $product['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </th>
                </tr>
                <tr>
                    <th>Product Image</th>
                    <td>
                        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/6/65/No-Image-Placeholder.svg/1665px-No-Image-Placeholder.svg.png"
                            id="img1" alt=" ">
                    </td>
                    <td>
                        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/6/65/No-Image-Placeholder.svg/1665px-No-Image-Placeholder.svg.png"
                            id="img2" alt=" ">
                    </td>
                </tr>
                <tr>
                    <th>Product Price</th>
                    <td id="price1">N/A</td>
                    <td id="price2">N/A</td>
                </tr>
                <tr>
                    <th>Product Description</th>
                    <td id="desc1">N/A</td>
                    <td id="desc2">N/A</td>
                </tr>
                <tr>
                    <th>Product Brand</th>
                    <td id="brand1">N/A</td>
                    <td id="brand2">N/A</td>
                </tr>
                <tr>
                    <th>Product storage (GB)</th>
                    <td id="storage1">N/A</td>
                    <td id="storage2">N/A</td>
                </tr>
                <tr>
                    <th>Product ram (GB)</th>
                    <td id="ram1">N/A</td>
                    <td id="ram2">N/A</td>
                </tr>
                <tr>
                    <th>Product processor</th>
                    <td id="processor1">N/A</td>
                    <td id="processor2">N/A</td>
                </tr>
            </table>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.9.2/umd/popper.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.1.0/js/bootstrap.min.js"></script>
</body>

</html>

<script>
    var product = <?php echo json_encode($products); ?>;

    function item1(a) {
        var select2 = document.getElementById("select2").value;
        if (a != select2) {
            document.getElementById("img1").src = product[a - 1].image;
            document.getElementById("price1").innerHTML = "RM " + product[a - 1].price;
            document.getElementById("desc1").innerHTML = product[a - 1].description;
            document.getElementById("brand1").innerHTML = product[a - 1].category;
            document.getElementById("storage1").innerHTML = product[a - 1].storage;
            document.getElementById("ram1").innerHTML = product[a - 1].ram;
            document.getElementById("processor1").innerHTML = product[a - 1].processor;
        } else {
            document.getElementById("select1").selectedIndex = 0;
            document.getElementById("img1").src = "https://upload.wikimedia.org/wikipedia/commons/thumb/6/65/No-Image-Placeholder.svg/1665px-No-Image-Placeholder.svg.png";
            document.getElementById("price1").innerHTML = "";
            document.getElementById("desc1").innerHTML = "";
            document.getElementById("brand1").innerHTML = "";
        }
    }

    function item2(a) {
        var select1 = document.getElementById("select1").value;
        if (a != select1) {
            document.getElementById("img2").src = product[a - 1].image;
            document.getElementById("price2").innerHTML = "RM " + product[a - 1].price;
            document.getElementById("desc2").innerHTML = product[a - 1].description;
            document.getElementById("brand2").innerHTML = product[a - 1].category;
            document.getElementById("storage2").innerHTML = product[a - 1].storage;
            document.getElementById("ram2").innerHTML = product[a - 1].ram;
            document.getElementById("processor2").innerHTML = product[a - 1].processor;
        } else {
            document.getElementById("select2").selectedIndex = 0;
            document.getElementById("img2").src = "https://upload.wikimedia.org/wikipedia/commons/thumb/6/65/No-Image-Placeholder.svg/1665px-No-Image-Placeholder.svg.png";
            document.getElementById("price2").innerHTML = "";
            document.getElementById("desc2").innerHTML = "";
            document.getElementById("brand2").innerHTML = "";
        }
    }
</script>