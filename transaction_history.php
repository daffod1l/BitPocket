<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$start_date = isset($_POST['start_date']) ? $_POST['start_date'] : '';
$end_date = isset($_POST['end_date']) ? $_POST['end_date'] : '';
$transaction_id = isset($_POST['transaction_id']) ? $_POST['transaction_id'] : '';
$amount = isset($_POST['amount']) ? $_POST['amount'] : '';

if ($amount && $amount < 0) {
    $amount = 0;
}


$sql = "SELECT t.transaction_id, t.transaction_date, t.total_amount AS amount, t.status 
        FROM transactions t
        WHERE t.user_id = ?";


if ($start_date && $end_date) {
    $sql .= " AND t.transaction_date BETWEEN ? AND ?";
}


if ($transaction_id) {
    $sql .= " AND t.transaction_id LIKE ?";
}

if ($amount) {
    $sql .= " AND t.total_amount >= ?";
}

$stmt = $conn->prepare($sql);

if ($start_date && $end_date && $transaction_id && $amount) {
    $stmt->bind_param("issss", $_SESSION['user_id'], $start_date, $end_date, "%$transaction_id%", $amount);
} 
elseif ($start_date && $end_date && $transaction_id) {
    $stmt->bind_param("isss", $_SESSION['user_id'], $start_date, $end_date, "%$transaction_id%");
} 
elseif ($start_date && $end_date && $amount) {
    $stmt->bind_param("iss", $_SESSION['user_id'], $start_date, $end_date);
} 
elseif ($transaction_id && $amount) {
    $stmt->bind_param("iss", $_SESSION['user_id'], "%$transaction_id%", $amount);
} 
elseif ($transaction_id) {
    $stmt->bind_param("is", $_SESSION['user_id'], "%$transaction_id%");
} 
elseif ($amount) {
    $stmt->bind_param("ii", $_SESSION['user_id'], $amount);
} 
else {
    $stmt->bind_param("i", $_SESSION['user_id']);
}

$stmt->execute();
$result = $stmt->get_result();
?>


<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction History</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<div class="main-container">
    <div class="sidebar">
        <div class="logo">
            <h1>PrizeVersity</h1>
        </div>
        <nav>
            <ul>
                <li><a href="welcome.php">Dashboard</a></li>
                <li><a href="cart.php">Cart</a></li>
                <li><a href="checkout.php">Checkout</a></li>
                <li><a href="transaction_history.php" class="active">Transaction History</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </div>

    <div class="content">
        <h2 class="page-title">Your Transaction History</h2>

        <form method="POST" class="date-filter-form">
            <div class="form-group">
                <label for="start_date">Start Date:</label>
                <input type="date" id="start_date" name="start_date" value="<?php echo $start_date; ?>">
            </div>

            <div class="form-group">
                <label for="end_date">End Date:</label>
                <input type="date" id="end_date" name="end_date" value="<?php echo $end_date; ?>">
            </div>

            <div class="form-group">
                <label for="transaction_id">Transaction ID:</label>
                <input type="text" id="transaction_id" name="transaction_id" value="<?php echo $transaction_id; ?>" placeholder="Search by Transaction ID">
            </div>

            <div class="form-group">
                <label for="amount">Amount:</label>
                <input type="text" id="amount" name="amount" value="<?php echo $amount !== '' ? number_format($amount, 2, '.', '') : ''; ?>" 
                placeholder="Enter Amount (e.g., 50.00)">
            </div>

            <button type="submit" class="btn-filter">Filter</button>
        </form>

        <div class="table-container">
            <table class="transaction-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Transaction ID</th>
                        <th>Product</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo date('Y-m-d', strtotime($row['transaction_date'])); ?></td>
                                <td><?php echo htmlspecialchars($row['transaction_id']); ?></td>
                                <td><?php echo 'N/A'; ?></td> <!-- NOTE: The product names will be pulled from the Bit Bazaar page. -->
                                <td><?php echo '$' . number_format($row['amount'], 2); ?></td>
                                <td><?php echo htmlspecialchars($row['status']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No transactions found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
