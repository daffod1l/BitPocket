<?php
    session_start();
    require_once 'db.php';

    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
// Fetch enrolled classes
$sql = "SELECT c.id AS class_id, c.name AS class_name, uStudent.last_name as student_name, uAdmin.last_name as admin_name,
        (SELECT COUNT(*) FROM class_students WHERE class_id = c.id) AS student_count
        from classes c
        join class_students cs on cs.Class_ID = c.id
        join users uStudent on cs.Student_ID = uStudent.ID
        join users uAdmin on c.teacher_id = uAdmin.ID
        WHERE cs.student_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Handle class join request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['joinClass'])) {
    $classCode = trim($_POST['classCode']);
    
    // Check if class exists
    $stmt_check = $conn->prepare("SELECT id FROM classes WHERE name = ?");
    $stmt_check->bind_param("s", $classCode);
    $stmt_check->execute();
    $stmt_check->store_result();
    
    if ($stmt_check->num_rows > 0) {
        $stmt_check->bind_result($class_id);
        $stmt_check->fetch();
        
        // Check if user is already enrolled
        $stmt_check_enrollment = $conn->prepare("SELECT * FROM class_students WHERE class_id = ? AND student_id = ?");
        $stmt_check_enrollment->bind_param("ii", $class_id, $user_id);
        $stmt_check_enrollment->execute();
        $stmt_check_enrollment->store_result();
        
        if ($stmt_check_enrollment->num_rows == 0) {
            // Enroll user in the class
            $stmt_insert = $conn->prepare("INSERT INTO class_students (class_id, student_id) VALUES (?, ?)");
            $stmt_insert->bind_param("ii", $class_id, $user_id);
            $stmt_insert->execute();
            echo "<script>alert('Successfully joined $classCode'); window.location.href = window.location.href;</script>";
        } else {
            echo "<script>alert('You are already enrolled in this class.');</script>";
        }
    } else {
        echo "<script>alert('Class not found');</script>";
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['leaveClass'])) {

    $classCode = trim($_POST['classCode']);

    // Retrieve class ID using class name
    $stmt_check = $conn->prepare("SELECT id FROM classes WHERE name = ?");
    $stmt_check->bind_param("s", $classCode);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        $stmt_check->bind_result($leaveClassID);
        $stmt_check->fetch();

        // Check if user is enrolled
        $stmt_check_enrollment = $conn->prepare("SELECT * FROM class_students WHERE class_id = ? AND student_id = ?");
        $stmt_check_enrollment->bind_param("ii", $leaveClassID, $user_id);
        $stmt_check_enrollment->execute();
        $stmt_check_enrollment->store_result();

        if ($stmt_check_enrollment->num_rows == 1) {
            // Delete user from class
            $stmt_delete = $conn->prepare("DELETE FROM class_students WHERE class_id = ? AND student_id = ?");
            $stmt_delete->bind_param("ii", $leaveClassID, $user_id);

            if ($stmt_delete->execute()) {
                echo "<script>alert('You have left $classCode'); window.location.href = window.location.href;</script>";
                exit();
            } else {
                echo "<script>alert('Error: Could not leave class.');</script>";
            }
        } else {
            echo "<script>alert('You are not enrolled in this class.');</script>";
        }
    } else {
        echo "<script>alert('Class not found');</script>";
    }
}

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

<div class="container mt-5">
    <?php if ($result->num_rows === 0): ?>
        <div class="container mt-5 mb-5 w-50">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="card-title">You are not enrolled in any classes.</h5>
                    <p>Enter a class code to join.</p>
                    <form method="POST">
                        <input class="form-control mb-3" type="text" placeholder="Enter Class Code" name="classCode" required>
                        <button type="submit" class="btn btn-success" name="joinClass">Join Class</button>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php while ($row = $result->fetch_assoc()): ?>
        <div class="card w-75 mx-auto mb-4">
            <div class="card-body">
                <div class="card-header">
                    <h3><?php echo htmlspecialchars($row['class_name']); ?></h3>
                    <div class="d-flex justify-content-between align-items-center">
                        <p class="mb-0"><strong>Admin:</strong> <?php echo htmlspecialchars($row['admin_name']); ?></p>
                        <p class="mb-0"><strong>Students:</strong> <?php echo $row['student_count']; ?></p>
                        <script>
                            function confirmLeave(className, formId) {
                                if (confirm("Are you sure you want to leave " + className + "?")) {
                                    document.getElementById(formId).submit();
                                }
                            }
                        </script>

                    <form id="leaveForm-<?php echo $row['class_name']; ?>" method="POST">
                        <input type="hidden" name="classCode" value="<?php echo htmlspecialchars($row['class_name']); ?>">
                        <button type="button" class="btn btn-danger" name="leaveClass"
                            onclick="confirmLeave('<?php echo htmlspecialchars($row['class_name']); ?>', 'leaveForm-<?php echo $row['class_name']; ?>')">
                            Leave Class
                        </button>
                    </form>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <h5 class="card-title">Join a new class</h5>
                <form method="POST" class="w-25 p-3">
                    <input class="form-control" type="text" placeholder="Enter Class Code" name="classCode" required>
                    <button type="submit" class="btn btn-success w-100 mt-2" name="joinClass">Join Class</button>
                </form>
                
                <ul class="nav nav-tabs w-100 justify-content-between">
                    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#students-<?php echo $row['class_id']; ?>">Students</a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#guilds-<?php echo $row['class_id']; ?>">Guilds</a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#bitbazaar-<?php echo $row['class_id']; ?>">Bit Bazaar</a></li>
                </ul>

                <div class="tab-content mt-3">
                    <!-- Students Tab -->
                    <div id="students-<?php echo $row['class_id']; ?>" class="tab-pane fade show active">
                        <form method="POST" class="w-50 p-3 d-flex justify-content-between">
                            <input class="form-control me-2" type="search" placeholder="Search Students" name="searchStudents" value="<?php echo isset($_POST['searchStudents']) ? htmlspecialchars($_POST['searchStudents']) : ''; ?>">
                            <button type="submit" name="studentSearch" class="btn btn-secondary">Search</button>
                        </form>

                        <div class="container">
                            <div class="row">
                                <?php
                                $class_id = $row['class_id'];
                                $search = isset($_POST['searchStudents']) ? trim($_POST['searchStudents']) : '';

                                // Use prepared statement to prevent SQL injection
                                if (!empty($search)) {
                                    $students_sql = "SELECT u.email FROM class_students cs
                                                    JOIN users u ON cs.student_id = u.id
                                                    WHERE cs.class_id = ? AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)";
                                    $searchParam = "%$search%";
                                    $stmt_students = $conn->prepare($students_sql);
                                    $stmt_students->bind_param("isss", $class_id, $searchParam, $searchParam, $searchParam);
                                } else {
                                    $students_sql = "SELECT u.email FROM class_students cs
                                                    JOIN users u ON cs.student_id = u.id
                                                    WHERE cs.class_id = ?";
                                    $stmt_students = $conn->prepare($students_sql);
                                    $stmt_students->bind_param("i", $class_id);
                                }

                                $stmt_students->execute();
                                $students_result = $stmt_students->get_result();

                                if ($students_result->num_rows > 0) {
                                    while ($student = $students_result->fetch_assoc()) {
                                        echo '<div class="col-md-4 mb-2">' . htmlspecialchars($student['email']) . '</div>';
                                    }
                                } else {
                                    echo '<div class="col-md-12 text-center text-muted">No students found.</div>';
                                }
                                ?>
                            </div>
                        </div>
                    </div>

                    <!-- Guilds Tab -->
                    <div id="guilds-<?php echo $row['class_id']; ?>" class="tab-pane fade">
                        <div class="w-25 p-3">
                            <input class="form-control me-2" type="search" placeholder="Search Guilds" aria-label="Search" name="searchGuilds">
                        </div>
                        <?php
                            $class_id = $row['class_id'];
                            $student_id = $user_id; //$_SESSION['user_id']; -- CHANGE IN MASTER

                            // Check if the student has already joined a group
                            $joined_guild = null;
                            $guild_sql = "SELECT guild_id FROM student_guilds WHERE student_id = ?";
                            $stmt_guild = $conn->prepare($guild_sql); // Use $guild_sql here
                            if (!$stmt_guild) {
                                die("Error preparing statement: " . $conn->error);
                            }
                            $stmt_guild->bind_param("i", $student_id);
                            $stmt_guild->execute();
                            $stmt_guild->bind_result($guild_id);
                            $stmt_guild->fetch();
                            $stmt_guild->close();

                            if ($guild_id) {
                                $joined_guild = $guild_id; // Student has already joined a group
                            }

                            // Fetch all guilds for the class
                            $guild_sql = "SELECT guild_id, guild_name FROM guilds WHERE class_id = ?";
                            $stmt_guilds = $conn->prepare($guild_sql);
                            if (!$stmt_guilds) {
                                die("Error preparing statement: " . $conn->error);
                            }
                            $stmt_guilds->bind_param("i", $class_id); 
                            $stmt_guilds->execute();
                            $result_guilds = $stmt_guilds->get_result(); 
                            $guilds = [];
                            while ($guild_row = $result_guilds->fetch_assoc()) {
                                $guilds[] = $guild_row;
                            }
                            $stmt_guilds->close();
                        ?>
                        <div class="guild-container d-flex flex-row flex-wrap">
                        <?php foreach ($guilds as $guild): ?>
                            <div class="guild-card">
                                <div class="ms-5 mt-3 card group-card <?php echo ($joined_guild && $joined_guild != $guild['guild_id']) ? 'disabled' : ''; ?>">
                                    <div class="card-body">
                                    <h5 class="card-title">
                                        <a href="#" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#guildMembersModal-<?php echo $guild['guild_id']; ?>">
                                            <?php echo htmlspecialchars($guild['guild_name']); ?>
                                        </a>
                                    </h5>
                                        <!-- If the student hasn't joined any group, show the join button -->
                                        <?php if (!$joined_guild): ?>
                                            <form action="join_guild.php" method="POST">
                                                <input type="hidden" name="student_id" value="<?php echo $student_id; ?>">
                                                <input type="hidden" name="class_id" value="<?php echo $class_id; ?>">
                                                <input type="hidden" name="guild_id" value="<?php echo $guild['guild_id']; ?>">
                                                <button type="submit" class="mt-2 h-50 btn btn-primary">Join Group</button>
                                            </form>
                                        <?php elseif ($joined_guild == $guild['guild_id']): ?>
                                            <p class="text-success">You have joined this group.</p>
                                        <?php else: ?>
                                            <p class="text-muted">You cannot join this group.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Guild Members Modal -->
                    <?php foreach ($guilds as $guild): ?>
                        <!-- Modal for this specific guild -->
                        <div class="modal fade" id="guildMembersModal-<?php echo $guild['guild_id']; ?>" tabindex="-1" aria-labelledby="guildModalLabel-<?php echo $guild['guild_id']; ?>" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="guildModalLabel-<?php echo $guild['guild_id']; ?>">
                                            Members of <?php echo htmlspecialchars($guild['guild_name']); ?>
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <ul class="list-group">
                                            <?php
                                            $guild_id = $guild['guild_id'];

                                            $sql = "SELECT CONCAT(uStudent.First_Name, ' ', uStudent.Last_Name) as Name FROM student_guilds sg
                                                    JOIN users uStudent ON sg.student_id = uStudent.ID
                                                    WHERE sg.guild_id = ?";
                                            $stmt = $conn->prepare($sql);
                                            $stmt->bind_param("i", $guild_id);
                                            $stmt->execute();
                                            $result = $stmt->get_result();

                                            if ($result->num_rows > 0) {
                                                while ($row = $result->fetch_assoc()) {
                                                    echo "<li class='list-group-item'>" . htmlspecialchars($row['Name']) . "</li>";
                                                }
                                            } else {
                                                echo "<li class='list-group-item text-muted'>No members found.</li>";
                                            }

                                            $stmt->close();
                                            ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>




                    <!-- Bit Bazaar Tab -->
                    <div id="bitbazaar-<?php echo $row['class_id']; ?>" class="tab-pane fade">
                        <div class="w-50 p-3">
                            <input class="form-control me-2" type="search" placeholder="Search Bazaar" aria-label="Search" name="searchBazaar">
                        </div>
                        <?php
                        // Fetch all rewards from the database
                            $sql = "SELECT name, description, price FROM rewards";
                            $result = $conn->query($sql);
                            $rewards = [];
                            while ($row = $result->fetch_assoc()) {
                                $rewards[] = $row;
                            }
                        ?>

                        <div class="container mt-5">
                                <h2>Rewards</h2>
                                <div class="row" id="rewards-container">
                                    <?php foreach ($rewards as $reward): ?>
                                        <div class="col-md-4 mb-4">
                                            <div class="reward-card">
                                                <h5><?php echo htmlspecialchars($reward['name']); ?></h5>
                                                <p><?php echo htmlspecialchars($reward['description']); ?></p>
                                                <p><strong>Price:</strong> $<?php echo htmlspecialchars($reward['price']); ?></p>
                                                <div class="input-group">
                                                    <input type="number" class="form-control quantity" value="1" min="1" max="5">
                                                    <button class="btn btn-success add-to-cart-btn" data-reward-id="<?php echo $reward['id']; ?>">Add to Cart</button>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                    </div>
                </div>
            </div>
        </div>

    <?php endwhile; ?>
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
