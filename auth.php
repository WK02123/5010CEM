<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db_connection.php';
session_start();

// Include PHPMailer for sending emails
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';  // Make sure PHPMailer is installed via Composer

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $formType = $_POST['formType'];  
    $email = $_POST['email'];
    $password = $_POST['password'];

    if ($formType == 'signup') {
        $username = $_POST['username'];
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);  // Hash the password

        // Generate a unique verification token
        $verification_token = bin2hex(random_bytes(50));

        // Insert user data into the database with the verification token and set email_verified to 0
        $sql = "INSERT INTO users (username, email, password, verification_token, email_verified) VALUES (?, ?, ?, ?, 0)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $username, $email, $hashed_password, $verification_token);

        if ($stmt->execute()) {
            // Prepare the verification link for the user to verify their email
            $verification_link = "http://localhost/IntiShuttle/enterprise/verify.php?email=$email&token=$verification_token";

            // Send verification email
            $mail = new PHPMailer(true);
            try {
                // Server settings for PHPMailer
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com'; 
                $mail->SMTPAuth = true;
                $mail->Username = 'wongbiu627@gmail.com';  // Your Gmail address
                $mail->Password = 'kpzl strz yrnc ntlz';   // Your Gmail password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                // Set sender and recipient
                $mail->setFrom('no-reply@yourdomain.com', 'Game4Life'); 
                $mail->addAddress($email);

                // Email content
                $mail->isHTML(true);
                $mail->Subject = 'Verify your email address';
                $mail->Body = "Hi $username,<br><br>Please click the link below to verify your email address:<br><a href='$verification_link'>$verification_link</a>";

                // Send the email
                $mail->send();
                
                // After sending the verification email, redirect the user to the login page with a success message
                header("Location: LoginRegister.php?status=verification_email_sent");
                exit();
            } catch (Exception $e) {
                // If email sending fails, redirect with an error message
                header("Location: LoginRegister.php?status=mail_error");
                exit();
            }

        } else {
            // If there is an error inserting into the database, redirect with a DB error message
            header("Location: LoginRegister.php?status=db_error");
            exit();
        }

    } else if ($formType == 'login') {
        // Check if the user exists in the database
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Check if the user's email has been verified
            if ($user['email_verified'] == 0) {
                header("Location: LoginRegister.php?status=not_verified");
                exit();
            }

            // Verify the password entered by the user
            if (password_verify($password, $user['password'])) {
                // If the password is correct, start a session for the user
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $email;

                // Redirect the user to the homepage after successful login
                header("Location: homepage.php");
                exit();
            } else {
                // If the password is incorrect, redirect with an error message
                header("Location: LoginRegister.php?status=invalid_password");
                exit();
            }
        } else {
            // If the email is not found, redirect with a no account message
            header("Location: LoginRegister.php?status=no_account");
            exit();
        }
    }

    $stmt->close();
    $conn->close();
}
?>
