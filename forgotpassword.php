<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['email'])) {
    $email = trim($_POST['email']);

    $conn = new mysqli('localhost', 'root', '', 'prizeversity_db');
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    
    $sql = "SELECT id, security_question, security_answer FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($userId, $securityQuestion, $hashedAnswer);
        $stmt->fetch();

        $_SESSION['reset_email'] = $email;
        $_SESSION['hashed_answer'] = $hashedAnswer;
        $_SESSION['security_question'] = $securityQuestion;
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        header("Location: securityquestion.php");
        exit;

    } 
    else {
        echo "Email not found.";
    }

    $stmt->close();
    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="fp-container">
        <div class="fp-form-container">
            <h2 class="fp-title">Forgot Your Password?</h2>
            <p class="fp-instructions">Enter your email below to answer your security question and reset your password.</p>

            <form action="forgotpassword.php" method="POST">
                <div class="fp-form-group">
                    <label for="reset-email">Email Address</label>
                    <input type="email" id="reset-email" name="email" placeholder="Enter your email" required>
                </div>

                <div class="fp-form-group">
                    <button type="submit" class="fp-reset-btn">Next</button>
                </div>

                <div class="fp-back-to-login">
                    <p>Remember your password? <a href="login.php">Return to Login</a></p>
                </div>
            </form>
        </div>
    </div>
</body>
</html>