<?php
header('Content-Type: application/json');
require_once 'db.php'; 

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            getUserById($conn, $_GET['id']);
        } else {
            getAllUsers($conn);
        }
        break;

    case 'POST':
        createUser($conn);
        break;

    case 'PUT':
        if (isset($_GET['id'])) {
            updateUser($conn, $_GET['id']);
        } else {
            http_response_code(400);
            echo json_encode(["error" => "Missing user ID"]);
        }
        break;

    case 'DELETE':
        if (isset($_GET['id'])) {
            deleteUser($conn, $_GET['id']);
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

// functions

function getAllUsers($conn) {
    try {
        $sql = "
            SELECT 
              u.id, 
              u.first_name, 
              u.last_name, 
              u.email, 
              u.role, 
              u.status,
              u.balance,
             GROUP_CONCAT(CONCAT(c.id, ':', c.name) SEPARATOR '|') AS enrolled_classes
            FROM users u
            LEFT JOIN class_members cm ON cm.user_id = u.id
            LEFT JOIN classes c ON c.id = cm.class_id
            GROUP BY u.id
            ORDER BY u.id ASC
        ";
        $result = $conn->query($sql);
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode($rows);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["error" => $e->getMessage()]);
    }
}


function getUserById($conn, $id) {
    try {
        $stmt = $conn->prepare("SELECT id, first_name, last_name, email, role, status
                                FROM users
                                WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

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

function createUser($conn) {
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
        $sql = "INSERT INTO users (first_name, last_name, email, password_hash, role, status)
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssss", $fname, $lname, $email, $passHash, $role, $status);
        $stmt->execute();

        $newId = $conn->insert_id;
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

function updateUser($conn, $id) {
    $data = json_decode(file_get_contents("php://input"), true);
    if (!$data) {
        http_response_code(400);
        echo json_encode(["error" => "No data to update"]);
        return;
    }

    if (isset($data['balance_increment'])) {
        // This can be positive or negative
        $increment = (int)$data['balance_increment'];
        $sql = "UPDATE users SET balance = balance + ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $increment, $id);
        $stmt->execute();

        echo json_encode(["message" => "User balance updated"]);
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
        $stmt = $conn->prepare($sql);
        $types = str_repeat("s", count($fields)) . "i"; 
        $stmt->bind_param($types, ...$values);
        $stmt->execute();

        echo json_encode(["message" => "User updated"]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["error" => $e->getMessage()]);
    }
}

function deleteUser($conn, $id) {
    try {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        echo json_encode(["message" => "User deleted"]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["error" => $e->getMessage()]);
    }
}
