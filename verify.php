<?php
include 'db_connection.php';

$message = ""; // Variable to hold feedback messages
$isError = false; // Flag to track if an error occurs

if (isset($_GET['email']) && isset($_GET['token'])) {
    $email = $_GET['email'];
    $token = $_GET['token'];

    // Check if the email and token match
    $sql = "SELECT * FROM users WHERE email = ? AND verification_token = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Update email_verified status
        $sql_update = "UPDATE users SET email_verified = 1, verification_token = NULL WHERE email = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("s", $email);

        if ($stmt_update->execute()) {
            $message = "Your email has been verified. You can now log in.";
        } else {
            $message = "Verification failed. Please try again.";
            $isError = true;
        }
    } else {
        $message = "Invalid verification link.";
        $isError = true;
    }

    $stmt->close();
    $stmt_update->close();
    $conn->close();
} else {
    $message = "Missing email or token.";
    $isError = true;
}

// Display feedback to the user
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification</title>
    <link rel="stylesheet" href="styles.css"> <!-- Link to a CSS file -->
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #525252;
            color: #333;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            text-align: center;
        }
        .message {
            padding: 20px;
            border-radius: 5px;
            background-color: <?php echo $isError ? '#f8d7da' : '#d4edda'; ?>; /* Red for error, green for success */
            color: <?php echo $isError ? '#721c24' : '#155724'; ?>;
            border: 1px solid <?php echo $isError ? '#f5c6cb' : '#c3e6cb'; ?>;
            width: 300px;
        }
    </style>
</head>
<body>
    <div class="message">
        <h2><?php echo $message; ?></h2>
        <a href="LoginRegister.php">Go to Login</a>
    </div>
</body>
</html>
