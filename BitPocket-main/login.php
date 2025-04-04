<?php
session_start();
require 'db.php';

$error_message = '';
if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']); // Clear the error message after displaying it
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="login-container">
            <h2 class="page-title">Please log in to your account</h2>

            <form action="login_check.php" method="POST">
                <div class="form-group">
                    <label for="email">Email Address <span class="required">*</span></label>
                    <input type="email" id="email" name="email" required placeholder="Enter your email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>

                <div class="form-group password-container">
                    <label for="password">Password <span class="required">*</span></label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    <i id="eye-icon-password" class="fa fa-eye toggle-password"></i>
                </div>

                <div class="checkbox-group">
                    <input type="checkbox" id="keep-me-signed-in" name="keep-me-signed-in" <?php echo isset($_POST['keep-me-signed-in']) ? 'checked' : ''; ?>>
                    <label for="keep-me-signed-in">Keep me signed in</label>
                </div>

                <?php if (!empty($error_message)): ?>
                    <div class="error-message" style="color: red;">
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>

                <div class="buttons-group">
                    <button type="submit" class="login-btn">Login</button>
                    <a href="register.php" class="register-btn">Register</a>
                </div>

                <div class="forgot-password">
                    <a href="forgotpassword.php">Forgot your password?</a>
                </div>
            </form>
        </div>

        <div class="image-container">
            <img src="logo.png" alt="Login Image" class="image">
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const passwordField = document.getElementById("password");
            const togglePassword = document.getElementById("eye-icon-password");

            if (passwordField && togglePassword) {
                togglePassword.addEventListener("click", function () {
                    if (passwordField.type === "password") {
                        passwordField.type = "text";
                        togglePassword.classList.remove("fa-eye");
                        togglePassword.classList.add("fa-eye-slash");
                    } else {
                        passwordField.type = "password";
                        togglePassword.classList.remove("fa-eye-slash");
                        togglePassword.classList.add("fa-eye");
                    }
                });
            }
        });
    </script>
</body>
</html>