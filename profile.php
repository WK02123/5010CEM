<?php
session_start();
include 'db.php'; // Ensure this file contains the connection to your database

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}

// Fetch user data
$user_id = $_SESSION['user_id'];
$query = "SELECT username, email, address, city, state, zip, role, created_at FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Query preparation failed: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    echo "<p>User not found.</p>";
    exit();
}

// Handle profile update
$updateSuccess = false; // Flag for successful update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $zip = $_POST['zip'];

    // Update query excluding email
    $updateQuery = "UPDATE users SET username=?, address=?, city=?, state=?, zip=? WHERE id=?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("ssssii", $username, $address, $city, $state, $zip, $user_id);

    if ($updateStmt->execute()) {
        $updateSuccess = true; // Set success flag
        // Optionally, refresh the user data after updating
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
    } else {
        echo "<p>Error updating profile: " . $updateStmt->error . "</p>";
    }

    $updateStmt->close();
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

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="profile.css"> <!-- Link to your CSS file -->
    <style>
        /* Style for the pop-up message */
        #popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #4CAF50;
            color: white;
            padding: 20px;
            border-radius: 5px;
            z-index: 1000;
        }
    </style>
    <script>
        function showPopup(message) {
            var popup = document.getElementById("popup");
            popup.textContent = message;
            popup.style.display = "block";
            setTimeout(function() {
                popup.style.display = "none";
            }, 3000); // Auto-close after 3 seconds
        }
    </script>
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
    <br>
    <main class="profile">
        <h1>User Profile</h1>
        <form method="POST">
            <section class="profile-info">
                <div>
                    <label for="username"><strong>Username:</strong></label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                </div>
                <div>
                    <label for="email"><strong>Email:</strong></label>
                    <span><?php echo htmlspecialchars($user['email']); ?></span> <!-- Display email as plain text -->
                </div>
            </section>
            <section class="profile-info">
                <div>
                    <label for="address"><strong>Address:</strong></label>
                    <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($user['address']); ?>" required>
                </div>
                <div>
                    <label for="city"><strong>City:</strong></label>
                    <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($user['city']); ?>" required>
                </div>
            </section>
            <section class="profile-info">
                <div>
                    <label for="state"><strong>State:</strong></label>
                    <input type="text" id="state" name="state" value="<?php echo htmlspecialchars($user['state']); ?>" required>
                </div>
                <div>
                    <label for="zip"><strong>Zip Code:</strong></label>
                    <input type="text" id="zip" name="zip" value="<?php echo htmlspecialchars($user['zip']); ?>" required>
                </div>
            </section>
            <div class="profile-info">
                <button type="submit" class="update-btn">Update Profile</button>
            </div>
        </form>
        <div class="profile-info">
            <strong>Account Created:</strong> <?php echo htmlspecialchars($user['created_at']); ?>
        </div>
    </main>

    <!-- Pop-up message -->
    <div id="popup"></div>

    <?php if ($updateSuccess): ?>
        <script>
            showPopup("Profile updated successfully!");
        </script>
    <?php endif; ?>
</body>
</html>
