<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT id, first_name, last_name, email, password_hash, role FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        header("Location: ../login.html?error=Invalid+email+or+password");
        exit;
    }

    if (!password_verify($password, $user['password_hash'])) {
        header("Location: ../login.html?error=Invalid+email+or+password");
        exit;
    }

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];

    header("Location: ../index.html");
    exit;
} else {
    header("Location: ../login.html");
    exit;
}
