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
        <style>
            * {
                margin: 0;
                padding: 0;
                font-family: 'Poppins', sans-serif;
                box-sizing: border-box;
            }

            .logo img {
                width: 300px;
                height: auto;
            }

            .container {
                width: 100%;
                height: 85vh;
                background-image: linear-gradient(rgba(230, 230, 230, 0.8), rgba(0, 0, 50, 0.8)), url(loginbackground.jpg);
                background-position: center;
                background-size: cover;
                position: relative;
            }

            .form-box {
                width: 90%;
                max-width: 450px;
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background: rgba(255, 255, 255, 0.9);
                padding: 50px 60px 70px;
                text-align: center;
                border-radius: 15px;
                box-shadow: 0 4px 30px rgba(0, 0, 0, 0.2);
            }

            .form-box h1 {
                font-size: 30px;
                margin-bottom: 30px;
                color: #333;
            }

            .input-field {
                background: #eaeaea;
                margin: 15px 0;
                border-radius: 3px;
                display: flex;
                align-items: center;
                height: 65px;
                padding: 10px;
                transition: height 0.5s ease, padding 0.5s ease;
                overflow: hidden;
            }

            .input-field i {
                margin-left: 10px;
                color: #555;
            }

            .input-field input {
                width: 100%;
                background: transparent;
                border: 0;
                outline: 0;
                padding: 10px 15px;
                font-size: 16px;
            }

            .input-field.hidden {
                height: 0;
                padding: 0;
            }

            .btn-field {
                width: 100%;
                display: flex;
                justify-content: space-between;
                margin-top: 20px;
            }

            .btn-field button {
                flex-basis: 48%;
                background: #007bff;
                color: #fff;
                height: 45px;
                border-radius: 20px;
                border: none;
                cursor: pointer;
                transition: background 0.3s, transform 0.3s;
            }

            .btn-field button:hover {
                background: #0056b3;
                transform: translateY(-2px);
            }

            .btn-field button.disable {
                background: #eaeaea;
                color: #999;
                cursor: not-allowed;
            }

            .success-message, .error-message {
                margin-top: 10px;
                color: green; /* Set the color for success message */
                text-align: center;
            }

            .error-message {
                color: red; /* Set the color for error message */
            }

            @media (max-width: 768px) {
                .form-box {
                    padding: 30px;
                }
                .btn-field button {
                    flex-basis: 100%;
                    margin-bottom: 10px;
                }
            }
        </style>
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
