<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT balance, first_name, last_name, email FROM users WHERE id = ?");
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

        echo '<script>alert("You successfully purchased: ' . htmlspecialchars($selected_item['name']) . '!");</script>';
        $message_class = '';
    } 
    else if ($bits_balance <= $selected_item['bits_cost']) {
        echo '<script>alert("Insufficient bits to make this purchase.");</script>';
        $message_class = 'error';
    }
}

?>


<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Prizeversity</title>
    <link rel="stylesheet" href="studentStyle.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
    <script src="https://kit.fontawesome.com/003030085f.js" crossorigin="anonymous"></script>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg bg-body-tertiary">
    <div class="container-fluid">
        <a class="navbar-brand" href="student.php">Prizeversity</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link active" href="student.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Bit Bazaar</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Bit Fortune</a></li>
            </ul>
        </div>
        <div class="d-flex align-items-center gap-3 ms-auto"> 
            <i class="fa-solid fa-wallet"></i>
            <a href="cart.php" type="button">
                <i class="fa-solid fa-cart-shopping"></i>
            </a>
            <div class="dropstart">
            <a class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa-solid fa-user"></i>
            </a>           
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="studentProfile.php">Profile</a></li>
                <li><a class="dropdown-item" href="transaction_history.php">Transaction History</a></li>
                <li><a class="dropdown-item" href="logout.php">Logout</a></li>
            </ul>
            </div>
        </div>
    </div>
</nav>

    <div class="content">
        <h2 class="text-center m-4">Checkout</h2>

        <div class="checkout-wrapper m-4">
            <div class="contact-information mb-4">
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


            <?php if (!empty($_SESSION['cart'])): ?>
            <div class="order-summary">
            <h3>Order Summary</Summary></h3>
                <?php foreach ($_SESSION['cart'] as $item): ?>
                    <?php
                        $total = 0;
                        $item_total = $item['price'] * $item['quantity'];
                        $total += $item_total;
                    ?>
                <div class="card shadow-sm rounded p-3">
                            <h5 class="fw-bold"><?= htmlspecialchars($item['name']) ?></h5>
                            <p class="mb-1 text-muted">Class: <?= htmlspecialchars($item['class_name']) ?></p>
                            <p class="mb-1">Quantity: <?= htmlspecialchars($item['quantity']) ?></p>
                            <p class="mb-1">Price per item: $<?= number_format($item['price'], 2) ?></p>
                            <p class="fw-semibold">Subtotal: $<?= number_format($item_total, 2) ?></p>
                </div>
            </div>
                <?php endforeach; ?>

                <div class="text-end mt-4">
                    <h4>Total: $<?= number_format($total, 2) ?></h4>
                    <button type="submit" form="checkout-form" class="btn btn-success mt-2">Place Order</button>
                </div>


        </div>
        <?php endif; ?>

        <?php if (isset($message)): ?>
            <div class="message <?php echo $message_class; ?>"><?php echo $message; ?></div>
        <?php endif; ?>
    </div>
</div>

<footer class="p-3">
    <p>Prizeversity</p>
    <ul class="footerList">
        <li><a href="student.php">Dashboard</a></li>
        <li><a href="#">Bit Bazaar</a></li>
        <li><a href="#">Bit Fortune</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</footer>

</body>
</html>
