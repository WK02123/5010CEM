<?php
session_start();
require 'db_connection.php'; // Include your database connection

// Set the MySQL time zone for the current session
$conn->query("SET time_zone = '+08:00'");

// Set the PHP default time zone to Kuala Lumpur
date_default_timezone_set('Asia/Kuala_Lumpur');

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Verify the token
    $stmt = $conn->prepare("SELECT * FROM users WHERE reset_token = ? AND token_expiry > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        $email = $user['email'];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Hash the new password
            $new_password = password_hash($_POST['password'], PASSWORD_BCRYPT);

            // Update the user's password and clear the reset token
            $stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, token_expiry = NULL WHERE email = ?");
            $stmt->bind_param("ss", $new_password, $email);
            $stmt->execute();

            // Redirect to the login page with a success message
            header("Location: LoginRegister.php?status=password_reset_success");
            exit; // Make sure to exit after redirecting
        }
    } else {
        // Invalid or expired token
        echo "<p style='color: red;'>Invalid or expired token.</p>";
        exit;
    }
} else {
    // No token provided
    echo "<p style='color: red;'>No token provided.</p>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="styleR.css">
</head>
<body>
    <div class="container">
        <div class="form-box">
            <h1>Reset Your Password</h1>
            <form action="" method="POST">
                <div class="input-group">
                    <div class="input-field">
                        <i class="fa-solid fa-lock"></i>
                        <input type="password" name="password" placeholder="Enter your new password" required>
                    </div>
                </div>
                <div class="btn-field">
                    <button type="submit">Reset Password</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
