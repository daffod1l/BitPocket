<?php
session_start();

if (!isset($_SESSION['reset_email'], $_SESSION['hashed_answer'], $_SESSION['security_question'])) {
    die("Unauthorized access.");
}

$incorrectAnswerMessage = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['verify-answer'])) {
    if (!isset($_POST['security-answer'], $_POST['csrf_token'])) {
        die("Invalid request.");
    }

    if ($_SESSION['csrf_token'] !== $_POST['csrf_token']) {
        die("CSRF token mismatch!");
    }

    $userAnswer = trim($_POST['security-answer']);
    $hashedAnswer = $_SESSION['hashed_answer'];

    if (password_verify($userAnswer, $hashedAnswer)) {
        $_SESSION['allow_reset'] = true;
        header("Location: resetpassword.php");
        exit;
    } 
    else {
        $incorrectAnswerMessage = 'Incorrect answer. Please try again.';
    }
}
?>


<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Question</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="security-question-container">
        <h2>Security Question</h2>
        <form action="securityquestion.php" method="POST">
            <div class="form-group">
                <p><strong><?php echo $_SESSION['security_question']; ?></strong></p>
                <label for="security-answer">Answer:</label>
                <input type="text" name="security-answer" required placeholder="Type your answer" />
            </div>

            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <?php if ($incorrectAnswerMessage): ?>
                <div class="error-message"><?php echo $incorrectAnswerMessage; ?></div>
            <?php endif; ?>
            
            <div class="form-group">
                <button type="submit" name="verify-answer">Verify Answer</button>
            </div>
        </form>
    </div>
</body>
</html>