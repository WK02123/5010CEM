<?php
session_start();
require 'db_connection.php'; // Include your database connection
require 'vendor/autoload.php'; // Load Composer's autoloader

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Set the MySQL time zone for the current session
$conn->query("SET time_zone = '+08:00'");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];

    // Check if the email exists in the users table
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        // Email exists, generate a unique reset token
        $token = bin2hex(random_bytes(50)); // Generate a random token
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour')); // Token expires in 1 hour

        // Store the token and expiry in the database
        $stmt = $conn->prepare("UPDATE users SET reset_token=?, token_expiry=? WHERE email=?");
        $stmt->bind_param("sss", $token, $expiry, $email);
        $stmt->execute();

        // Prepare reset link and email content
        $resetLink = "http://localhost/IntiShuttle/enterprise/reset_password.php?token=$token";
        $subject = "Password Reset Request";
        $message = "Click the following link to reset your password: <a href='$resetLink'>$resetLink</a>";

        // Initialize PHPMailer
        $mail = new PHPMailer(true);
        try {
            // Server settings
            $mail->isSMTP(); // Set mailer to use SMTP
            $mail->Host = 'smtp.gmail.com'; // Specify main and backup SMTP servers
            $mail->SMTPAuth = true; // Enable SMTP authentication
            $mail->Username = 'wongbiu627@gmail.com'; // SMTP username
            $mail->Password = 'kpzl strz yrnc ntlz'; // SMTP password
            $mail->SMTPSecure = 'tls'; // Enable TLS encryption
            $mail->Port = 587; // TCP port to connect to

            // Recipients
            $mail->setFrom('no-reply@yourwebsite.com', 'No Reply');
            $mail->addAddress($email); // Add a recipient

            // Content
            $mail->isHTML(true); // Set email format to HTML
            $mail->Subject = $subject;
            $mail->Body = $message;

            // Send email
            $mail->send();
            header("Location: LoginRegister.php?status=success");
            exit; // Make sure to exit after redirecting
        } catch (Exception $e) {
            error_log("Mailer Error: {$mail->ErrorInfo}");
            header("Location: forget_password.php?status=error");
            exit; // Make sure to exit after redirecting
        }
    } else {
        // Email not found
        header("Location: forget_password.php?status=noaccount");
        exit; // Make sure to exit after redirecting
    }
}
?>
