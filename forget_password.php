<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="styleR.css">
</head>
<body>
    <div class="container">
        <div class="form-box">
            <h1>Reset Password</h1>
            <?php
            if (isset($_GET['status'])) {
                if ($_GET['status'] == 'noaccount') {
                    echo '<p class="error-message">No account found with that email.</p>';
                } elseif ($_GET['status'] == 'error') {
                    echo '<p class="error-message">Error sending reset email. Please try again.</p>';
                }
            }
            ?>
            <form action="send_reset_link2.php" method="POST">
                <div class="input-group">
                    <div class="input-field">
                        <i class="fa-solid fa-envelope"></i>
                        <input type="email" name="email" placeholder="Enter your email" required>
                    </div>
                </div>
                <div class="btn-field">
                    <button type="submit">Send Reset Link</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
