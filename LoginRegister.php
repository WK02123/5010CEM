<?php
session_start();
if (!isset($_SESSION['username'])) {
    // Optionally handle this case
}

// Database connection
$servername = "localhost"; // replace with your DB server
$username = "root"; // replace with your DB username
$password = ""; // replace with your DB password
$dbname = "enterprise"; // replace with your DB name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
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
    <title>Login and Register</title>
    <link rel="stylesheet" href="login1.css">
    <script src="https://kit.fontawesome.com/a60351983b.js" crossorigin="anonymous"></script>
</head>

<body>
    <!-- Header Section -->
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

    <div class="container">
        <div class="form-box">
            <h1 id="title">Sign Up</h1>

            <!-- Display messages based on the 'status' parameter in the URL -->
            <?php
            if (isset($_GET['status'])) {
                if ($_GET['status'] == 'verification_email_sent') {
                    echo '<p class="success-message">A verification email has been sent. Please check your inbox.</p>';
                } elseif ($_GET['status'] == 'mail_error') {
                    echo '<p class="error-message">There was an error sending the verification email. Please try again.</p>';
                } elseif ($_GET['status'] == 'db_error') {
                    echo '<p class="error-message">There was an error processing your registration. Please try again later.</p>';
                } elseif ($_GET['status'] == 'not_verified') {
                    echo '<p class="error-message">Your email is not verified. Please check your inbox for the verification link.</p>';
                } elseif ($_GET['status'] == 'invalid_password') {
                    echo '<p class="error-message">Incorrect password. Please try again.</p>';
                } elseif ($_GET['status'] == 'no_account') {
                    echo '<p class="error-message">No account found with that email. Please sign up first.</p>';
                } elseif ($_GET['status'] == 'success') {
                    echo '<p class="success-message">Password reset link has been sent to your email.</p>';
                } elseif ($_GET['status'] == 'error') {
                    echo '<p class="error-message">There was an error sending the email. Please try again.</p>';
                } elseif ($_GET['status'] == 'noaccount') {
                    echo '<p class="error-message">No account is associated with that email.</p>';
                }
            }
            ?>

            <form action="auth.php" method="POST">
                <div class="input-group">
                    <div class="input-field" id="nameField" style="max-height: 60px;">
                        <i class="fa-solid fa-user"></i>
                        <input type="text" name="username" placeholder="Username" id="username" required>
                    </div>
                    <div class="input-field">
                        <i class="fa-solid fa-envelope"></i>
                        <input type="email" name="email" placeholder="Email" required>
                    </div>
                    <div class="input-field">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" name="password" placeholder="Password" required>
                    </div>
                    <input type="hidden" id="formType" name="formType" value="signup">
                    <p>Forgot Password? <a href="forget_password.php">Click Here!</a></p>
                </div>

                <div class="btn-field">
                    <button type="submit" id="signupBtn">Sign Up</button>
                    <button type="button" id="signinBtn" class="disable">Login</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Loading Message -->
    <div id="loadingMessage" class="hidden">
        <p>Loading, please wait...</p>
    </div>

    <style>
        .hidden {
            display: none;
        }

        #loadingMessage {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: rgba(0, 0, 0, 0.8);
            color: #fff;
            padding: 20px;
            border-radius: 5px;
            font-size: 18px;
            text-align: center;
            z-index: 1000;
        }

        /* Style for success and error messages */
        .success-message {
            color: green;
            font-weight: bold;
            text-align: center;
        }

        .error-message {
            color: red;
            font-weight: bold;
            text-align: center;
        }
    </style>

    <script>
        const signinBtn = document.getElementById('signinBtn');
        const signupBtn = document.getElementById('signupBtn');
        const formType = document.getElementById('formType');
        const nameField = document.getElementById('nameField');
        const title = document.getElementById('title');
        const loadingMessage = document.getElementById('loadingMessage');

        signupBtn.onclick = function () {
            nameField.classList.remove('hidden');
            title.innerHTML = "Sign Up";
            signupBtn.classList.remove("disable");
            signinBtn.classList.add("disable");
            formType.value = "signup";

            signupBtn.setAttribute('type', 'submit');
            signinBtn.setAttribute('type', 'button');
            username.setAttribute('required', 'required');

            document.querySelector('form').onsubmit = function() {
                showLoadingMessage(3000);
            };
        };

        signinBtn.onclick = function () {
            nameField.classList.add('hidden');
            title.innerHTML = "Login";
            signupBtn.classList.add("disable");
            signinBtn.classList.remove("disable");
            formType.value = "login";

            signupBtn.setAttribute('type', 'button');
            signinBtn.setAttribute('type', 'submit');
            username.removeAttribute('required');

            document.querySelector('form').onsubmit = function() {
                showLoadingMessage(3000);
            };
        };
    </script>
</body>

</html>
