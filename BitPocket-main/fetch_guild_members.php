<?php
session_start();
$host = "localhost";
$username = "root";
$password = "";
$database = "perksway";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Database connection failed."]));
}

// Get the guild_id from the request
$guild_id = intval($_GET['guild_id'] ?? 0);
if (!$guild_id) {
    die(json_encode(["success" => false, "message" => "Invalid guild ID."]));
}

// Fetch students in the guild
$sql = "SELECT uStudent.Name FROM student_guilds sg
        JOIN users uStudent ON sg.student_id = uStudent.ID
        WHERE sg.guild_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $guild_id);
$stmt->execute();
$result = $stmt->get_result();

$members = [];
while ($row = $result->fetch_assoc()) {
    $members[] = $row['Name'];
}

$stmt->close();
$conn->close();

// Return JSON response
echo json_encode(["success" => true, "members" => $members]);
?>
