<?php
$servername = "localhost";
$username   = "root";
$password   = "Peepee624pee!#";
$database   = "teacher_dashboard";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->set_charset("utf8mb4");
