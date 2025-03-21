<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']) ?? '';
    $password = $_POST['password'] ?? '';
    $keep_me_signed_in = isset($_POST['keep-me-signed-in']) ? true : false;

    // Prepare the SQL statement
    $sql = "SELECT id, first_name, last_name, email, password, role FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Error preparing statement: " . $conn->error);
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id, $first_name, $last_name, $email, $hashed_password, $role);
    $stmt->fetch();

    if ($stmt->num_rows > 0) {
        if (password_verify($password, $hashed_password)) {
            // Set session variables
            $_SESSION['user_id'] = $id;
            $_SESSION['first_name'] = $first_name;
            $_SESSION['last_name'] = $last_name;
            $_SESSION['role'] = $role;

            // Set cookies if "Keep me signed in" is checked
            if ($keep_me_signed_in) {
                $cookie_expiration = time() + (30 * 24 * 60 * 60); // 30 days
                setcookie('user_id', $id, $cookie_expiration, '/', '', true, true);
                setcookie('user_email', $email, $cookie_expiration, '/', '', true, true);
            }

            // Redirect based on role
            if ($role === 'Student') {
                header("Location: student.php");
                exit();
            } elseif ($role === 'Teacher') {
                header("Location: teacherDashboard.php");
                exit();
            } else {
                header("Location: login.php"); // Default fallback
                exit();
            }
        } else {
            $_SESSION['error_message'] = "Incorrect password.";
            header("Location: login.php");
            exit();
        }
    } else {
        $_SESSION['error_message'] = "No user found with this email.";
        header("Location: login.php");
        exit();
    }
} else {
    header("Location: login.php");
    exit();
}
?>