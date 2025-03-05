<?php
session_start();

$successMessage = "";
$errorMessage = "";

if (!isset($_SESSION['allow_reset'])) {
    die("Unauthorized access.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_SESSION['reset_email'], $_POST['new_password'], $_POST['confirm_password'])) {
        die("Unauthorized request.");
    }

    $email = $_SESSION['reset_email'];
    $newPassword = trim($_POST['new_password']);
    $confirmPassword = trim($_POST['confirm_password']);

    if ($newPassword !== $confirmPassword) {
        $errorMessage = "Passwords do not match!";
    } 
    else {
        $passwordRegex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';
        
        if (!preg_match($passwordRegex, $newPassword)) {
            $errorMessage = "Password must be at least 8 characters long, contain one uppercase letter, one lowercase letter, one number, and one special character.";
        } 
        else {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $conn = new mysqli('localhost', 'root', '', 'prizeversity_db');
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            $sql = "UPDATE users SET password = ? WHERE email = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ss', $hashedPassword, $email);
            $stmt->execute();

            unset($_SESSION['reset_email']);
            unset($_SESSION['allow_reset']);
            unset($_SESSION['hashed_answer']);
            unset($_SESSION['csrf_token']);

            $successMessage = "Password reset successful! <a href='login.php'>Go to Login</a>";

            $stmt->close();
            $conn->close();
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">

    <style>
        .error-message {
            color: red;
            font-size: 14px;
        }
        .success-message {
            color: green;
            font-size: 16px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-input {
            padding: 10px;
            width: 100%;
            font-size: 16px;
            margin-top: 5px;
        }
        .password-container {
            position: relative;
        }
        .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-0%);
            cursor: pointer;
            color: #555;
        }
    </style>
</head>

<body>
    <div class="reset-password-container">
        <h2>Reset Your Password</h2>

        <form action="resetpassword.php" method="POST">

            <div class="form-group password-container">
                <label for="new_password">Enter your new password:</label>
                <input type="password" name="new_password" id="new_password" required minlength="8" placeholder="Enter new password" class="form-input">
                <i id="eye-icon-new-password" class="fa fa-eye toggle-password"></i>
            </div>

            <div class="form-group password-container">
                <label for="confirm_password">Confirm your new password:</label>
                <input type="password" name="confirm_password" id="confirm_password" required minlength="8" placeholder="Confirm new password" class="form-input">
                <i id="eye-icon-confirm-password" class="fa fa-eye toggle-password"></i>
            </div>
            
            <div class="form-group">
                <button type="submit" class="reset-btn">Reset Password</button>
            </div>
        </form>

        <?php if ($successMessage != ""): ?>
            <p class="success-message"><?php echo $successMessage; ?></p>
        <?php endif; ?>

        <?php if ($errorMessage != ""): ?>
            <p class="error-message"><?php echo $errorMessage; ?></p>
        <?php endif; ?>
    </div>

    <script>
        const togglePassword = (inputId, iconId) => {
            const passwordInput = document.getElementById(inputId);
            const eyeIcon = document.getElementById(iconId);
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } 
            else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        };

        document.getElementById('eye-icon-new-password').addEventListener('click', () => togglePassword('new_password', 'eye-icon-new-password'));
        document.getElementById('eye-icon-confirm-password').addEventListener('click', () => togglePassword('confirm_password', 'eye-icon-confirm-password'));

    </script>
</body>
</html>