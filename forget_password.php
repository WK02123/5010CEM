<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Forgot Password</title>
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
