<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT bits_balance, first_name, last_name, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($bits_balance, $first_name, $last_name, $email);
$stmt->fetch();
$stmt->close();

// NOTE: For now, simulating the selection of an item from the Bazaar page with hardcoded data.
$selected_item = [
    "id" => 1, 
    "name" => "Lab Pass", 
    "bits_cost" => 50
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contact_first_name = $_POST['first_name'];
    $contact_last_name = $_POST['last_name'];
    $contact_email = $_POST['email'];

    if ($bits_balance >= $selected_item['bits_cost']) {
        $bits_balance -= $selected_item['bits_cost'];
        
        $update_stmt = $conn->prepare("UPDATE users SET bits_balance = ? WHERE id = ?");
        $update_stmt->bind_param("ii", $bits_balance, $user_id);
        $update_stmt->execute();
        $update_stmt->close();

        $message = "You successfully purchased: " . $selected_item['name'] . "!";
        $message_class = '';
    } 
    else {
        $message = "Insufficient bits to make this purchase.";
        $message_class = 'error';
    }
}
?>


<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body>

<div class="checkout-container">
    <div class="sidebar">
        <div class="logo">
            <h1>PrizeVersity</h1>
        </div>
    
        <div class="user-info">
            <p><i class="fas fa-wallet"></i> ฿ <span class="balance"><?php echo number_format($bits_balance, 2); ?></span></p>
        </div>

        <nav>
            <ul>
                <li><a href="welcome.php">Dashboard</a></li>
                <li><a href="cart.php">Cart</a></li>
                <li><a href="checkout.php" class="active">Checkout</a></li>
                <li><a href="transaction_history.php">Transaction History</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </div>

    <div class="content">
        <h2 class="page-title">Checkout</h2>

        <div class="checkout-wrapper">
            <div class="contact-information">
                <h3>Contact Information</h3>
                <form method="POST" action="checkout.php" id="checkout-form" class="checkout-form">
                    <label for="first_name">First Name <span>*</span></label>
                    <input type="text" name="first_name" id="first_name" value="<?php echo $first_name; ?>" required>

                    <label for="last_name">Last Name <span>*</span></label>
                    <input type="text" name="last_name" id="last_name" value="<?php echo $last_name; ?>" required>

                    <label for="email">Email Address <span>*</span></label>
                    <input type="email" name="email" id="email" value="<?php echo $email; ?>" required>
                </form>
            </div>

            <div class="order-summary">
                <h3>Order Summary</Summary></h3>
                <div class="order-details">
                    <p>Product: <strong><?php echo $selected_item['name']; ?></strong></p>
                    <p>Subtotal: <strong><?php echo number_format($selected_item['bits_cost'], 2); ?> Bits</strong></p>
                    <p>Total: <strong><?php echo number_format($selected_item['bits_cost'], 2); ?> Bits</strong></p>
                </div>
                <button type="submit" form="checkout-form" class="btn-checkout">Place Order</button>
            </div>
        </div>

        <?php if (isset($message)): ?>
            <div class="message <?php echo $message_class; ?>"><?php echo $message; ?></div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>