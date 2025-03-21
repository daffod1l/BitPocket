<?php
    session_start();
    require_once 'db.php';

    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    $student_id = $_SESSION['user_id'];

    $sql = "SELECT 
    CONCAT(uStudent.first_name, ' ', uStudent.last_name) AS student_name,
    c.id AS class_id,
    c.name AS class_name
    FROM 
        users uStudent
    JOIN 
        class_students cs ON cs.Student_ID = uStudent.ID
    JOIN 
        classes c ON cs.Class_ID = c.id
    WHERE  uStudent.id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();

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

<?php echo htmlspecialchars($_SESSION['student_name'])?>

<?php while ($row = $result->fetch_assoc()): ?>
    <p>Enrolled Classes</p>
<?php endwhile; ?>

<!--i would like to add 
the users enrolled classes 
guilds theyre apart if in each class
previous 3 transactions
-->

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