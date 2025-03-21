<?php
header('Content-Type: application/json');
require_once 'db.php'; 

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            getUserById($pdo, $_GET['id']);
        } else {
            getAllUsers($pdo);
        }
        break;

    case 'POST':
        createUser($pdo);
        break;

    case 'PUT':
        if (isset($_GET['id'])) {
            updateUser($pdo, $_GET['id']);
        } else {
            http_response_code(400);
            echo json_encode(["error" => "Missing user ID"]);
        }
        break;

    case 'DELETE':
        if (isset($_GET['id'])) {
            deleteUser($pdo, $_GET['id']);
        } else {
            http_response_code(400);
            echo json_encode(["error" => "Missing user ID"]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Method not allowed"]);
        break;
}

// Functions

function getAllUsers($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM users ORDER BY id ASC");
        $rows = $stmt->fetchAll();
        echo json_encode($rows);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["error" => $e->getMessage()]);
    }
}

function getUserById($pdo, $id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) {
            echo json_encode($row);
        } else {
            http_response_code(404);
            echo json_encode(["error" => "User not found"]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["error" => $e->getMessage()]);
    }
}

function createUser($pdo) {
    $data = json_decode(file_get_contents("php://input"), true);
    if (!$data || !isset($data['first_name']) || !isset($data['last_name']) 
                || !isset($data['email']) || !isset($data['password_hash'])) {
        http_response_code(400);
        echo json_encode(["error" => "Missing required fields"]);
        return;
    }

    $fname = $data['first_name'];
    $lname = $data['last_name'];
    $email = $data['email'];
    $passHash = $data['password_hash'];
    $role = $data['role'] ?? 'student';
    $status = $data['status'] ?? 'active';

    try {
        $stmt = $pdo->prepare("INSERT INTO users 
          (first_name, last_name, email, password_hash, role, status) 
          VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$fname, $lname, $email, $passHash, $role, $status]);

        $newId = $pdo->lastInsertId();
        echo json_encode([
            "id" => $newId,
            "first_name" => $fname,
            "last_name" => $lname,
            "email" => $email,
            "role" => $role,
            "status" => $status
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["error" => $e->getMessage()]);
    }
}

function updateUser($pdo, $id) {
    $data = json_decode(file_get_contents("php://input"), true);
    if (!$data) {
        http_response_code(400);
        echo json_encode(["error" => "No data to update"]);
        return;
    }

    $fields = [];
    $values = [];

    if (isset($data['first_name'])) {
        $fields[] = "first_name = ?";
        $values[] = $data['first_name'];
    }
    if (isset($data['last_name'])) {
        $fields[] = "last_name = ?";
        $values[] = $data['last_name'];
    }
    if (isset($data['email'])) {
        $fields[] = "email = ?";
        $values[] = $data['email'];
    }
    if (isset($data['password_hash'])) {
        $fields[] = "password_hash = ?";
        $values[] = $data['password_hash'];
    }
    if (isset($data['role'])) {
        $fields[] = "role = ?";
        $values[] = $data['role'];
    }
    if (isset($data['status'])) {
        $fields[] = "status = ?";
        $values[] = $data['status'];
    }

    if (count($fields) === 0) {
        http_response_code(400);
        echo json_encode(["error" => "No valid fields"]);
        return;
    }

    $sql = "UPDATE users SET " . implode(", ", $fields) . " WHERE id = ?";
    $values[] = $id;

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);
        echo json_encode(["message" => "User updated"]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["error" => $e->getMessage()]);
    }
}

function deleteUser($pdo, $id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(["message" => "User deleted"]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["error" => $e->getMessage()]);
    }
}
