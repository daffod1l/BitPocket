<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$first_name = isset($_SESSION['first_name']) ? $_SESSION['first_name'] : 'User';
?>


<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to PrizeVersity</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .logout-btn {
            position: absolute;
            top: 10px;
            right: 20px;
            background-color: #FF4D4D;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .logout-btn:hover {
            background-color: #cc0000;
        }
    </style>
</head>

<body class="welcome-page">
    <form action="logout.php" method="POST">
        <button type="submit" class="logout-btn">Logout</button>
    </form>

    <div class="container">
        <div class="welcome-container">
            <h2 class="page-title">Welcome to PrizeVersity, <?php echo htmlspecialchars($first_name); ?>!</h2>
            <p class="greeting">We are excited to have you on board! Ready to start your journey?</p>
            <button class="get-started-btn">Get Started</button>
        </div>
    </div>
</body>
</html>