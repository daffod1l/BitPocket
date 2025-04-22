<?php
session_start();

// Handle remove
if (isset($_GET['action']) && $_GET['action'] === "remove" && isset($_GET['id']) && isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $key => $value) {
        if ($value['id'] == $_GET['id']) {
            unset($_SESSION['cart'][$key]);
            $_SESSION['cart'] = array_values($_SESSION['cart']); // Reindex
            break;
        }
    }
}

$total = 0;
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Perksway</title>
    <link rel="stylesheet" href="studentStyle.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
    <script src="https://kit.fontawesome.com/003030085f.js" crossorigin="anonymous"></script>
</head>

<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg bg-body-tertiary">
    <div class="container-fluid">
        <a class="navbar-brand" href="student.php">Perksway</a>
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
                <li><a class="dropdown-item" href="#">Transaction History</a></li>
                <li><a class="dropdown-item" href="logout.php">Logout</a></li>
            </ul>
            </div>
        </div>
    </div>
</nav>

<body class="bg-light">
    <div class="container my-5">
        <h2 class="mb-4">Your Cart</h2>

        <?php if (!empty($_SESSION['cart'])): ?>
            <div class="row">
                <?php foreach ($_SESSION['cart'] as $item): ?>
                    <?php
                        $item_total = $item['price'] * $item['quantity'];
                        $total += $item_total;
                    ?>
                    <div class="col-md-6 mb-4">
                        <div class="card shadow-sm rounded p-3">
                            <h5 class="fw-bold"><?= htmlspecialchars($item['name']) ?></h5>
                            <p class="mb-1 text-muted">Class: <?= htmlspecialchars($item['class_name']) ?></p>
                            <p class="mb-1">Quantity: <?= htmlspecialchars($item['quantity']) ?></p>
                            <p class="mb-1">Price per item: $<?= number_format($item['price'], 2) ?></p>
                            <p class="fw-semibold">Subtotal: $<?= number_format($item_total, 2) ?></p>

                            <a href="cart.php?action=remove&id=<?= $item['id'] ?>" class="btn btn-danger btn-sm mt-2">Remove</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="text-end mt-4">
                <h4>Total: $<?= number_format($total, 2) ?></h4>
                <a href="checkout.php" class="btn btn-success mt-2">Checkout</a>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                Your cart is empty.
            </div>
        <?php endif; ?>
    </div>

    <footer class="p-3">
    <p>Perksway</p>
    <ul class="footerList">
        <li><a href="student.php">Dashboard</a></li>
        <li><a href="#">Bit Bazaar</a></li>
        <li><a href="#">Bit Fortune</a></li>
        <li><a href="checkout.php">Checkout</a></li>
        <li><a href="transaction_history.php">Transaction History</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</footer>

</body>
</html>
