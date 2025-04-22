<?php
    session_start();
    require_once 'db.php';

    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    $student_id = $_SESSION['user_id'];

    //GET STUDENT NAME
    $sql_username = "SELECT 
    CONCAT(uStudent.first_name, ' ', uStudent.last_name) AS student_name
    FROM 
        users uStudent    
    WHERE  uStudent.id = ?";

    $stmt_username = $conn->prepare($sql_username);
    $stmt_username->bind_param("i", $student_id);
    $stmt_username->execute();
    $stmt_username->bind_result($student_name);
    $stmt_username->fetch();

    $stmt_username->close();

//GET TRANSACTION HISTORY
    $sql_transactions = "SELECT t.transaction_id AS TransactionID, t.transaction_date AS Transaction_Date, t.total_amount AS Transaction_amount, t.status AS Transaction_Status
                            FROM transactions t
                            WHERE t.user_id = ?
                            ORDER BY t.transaction_date DESC;";

    $stmt_transactions = $conn->prepare($sql_transactions);
    $stmt_transactions->bind_param("i", $student_id);
    $stmt_transactions->execute();
    $result_transactions = $stmt_transactions->get_result();
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
            <i class="fa-solid fa-cart-shopping"></i>
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


<div class="container mt-5 mb-5 w-75">
    <div class="mb-5 text-center">
            <h3><?php echo htmlspecialchars($student_name); ?></h3>
    </div>
    <div class="card">
        <div class="card-header text-center">
            <h4>All Transactions</h4>
        </div>
        <div class="card-body text-center">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Transaction Amount</th>
                        <th>Purchase Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($transaction = $result_transactions->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($transaction["Transaction_amount"]); ?></td>
                        <td><?php echo htmlspecialchars($transaction["Transaction_Date"]); ?></td>
                        <td><?php echo htmlspecialchars($transaction["Transaction_Status"]); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<footer class="p-3">
    <p>Perksway</p>
    <ul class="footerList">
        <li><a href="student.php">Dashboard</a></li>
        <li><a href="#">Bit Bazaar</a></li>
        <li><a href="#">Bit Fortune</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</footer>

</body>
</html>