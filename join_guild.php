<?php
    session_start();
    require_once 'db.php';

    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    $user_id = $_SESSION['user_id'];

// Check if the request is POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $student_id = intval($_POST["student_id"] ?? 0);
    $guild_id = intval($_POST["guild_id"] ?? 0);

    if (!$student_id || !$guild_id) {
        die("Error: Invalid data received.");
    }

    // Check if the student is already in a group
    $check_sql = "SELECT group_id FROM group_members WHERE user_id = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $stmt->bind_result($existing_guild);
    $stmt->fetch();
    $stmt->close();

    if ($existing_guild) {
        die("Error: You are already in a group.");
    }

    // Insert the student into the group
    $insert_sql = "INSERT INTO group_members (user_id, group_id) VALUES (?, ?)";
    $stmt = $conn->prepare($insert_sql);
    $stmt->bind_param("ii", $student_id, $guild_id);

    if ($stmt->execute()) {
        echo "Success! You have joined the group.";
    } else {
        echo "Error: Could not join the group.";
    }

    $stmt->close();
    $conn->close();

    // Redirect back to the page after joining (to refresh the UI)
    header("Location: student.php");
    exit();
}
?>
