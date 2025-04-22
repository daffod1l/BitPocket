<?php
header('Content-Type: application/json');
require_once 'db.php';

// throwing erroe exceptions
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

session_start();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            getClassById($conn, $_GET['id']);
        } else {
            getAllClasses($conn);
        }
        break;

    case 'POST':
        createClass($conn);
        break;

    case 'PUT':
        if (isset($_GET['id'])) {
            updateClass($conn, $_GET['id']);
        } else {
            http_response_code(400);
            echo json_encode(["error" => "Missing class ID in query string"]);
        }
        break;

    case 'DELETE':
        if (isset($_GET['id'])) {
            deleteClass($conn, $_GET['id']);
        } else {
            http_response_code(400);
            echo json_encode(["error" => "Missing class ID in query string"]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Method not allowed"]);
        break;
}

// functions

function getAllClasses($conn) {
    try {
        $sql = "
          SELECT 
            c.*, 
            (SELECT COUNT(*) 
             FROM class_members cm 
             WHERE cm.class_id = c.id
               AND cm.role_in_class = 'student') AS student_count
          FROM classes c
          ORDER BY c.id ASC
        ";
        $result = $conn->query($sql);
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode($rows);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["error" => $e->getMessage()]);
    }
}

function getClassById($conn, $id) {
    try {
        $stmt = $conn->prepare("SELECT * FROM classes WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if ($row) {
            echo json_encode($row);
        } else {
            http_response_code(404);
            echo json_encode(["error" => "Class not found"]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["error" => $e->getMessage()]);
    }
}

function createClass($conn) {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(["error" => "Not logged in as a teacher"]);
        return;
    }

    $teacherId = $_SESSION['user_id']; 

    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data || !isset($data['name'])) {
        http_response_code(400);
        echo json_encode(["error" => "Missing 'name' field"]);
        return;
    }

    $name = $data['name'];
    $description = $data['description'] ?? '';
    $inviteHash = generateInviteHash();

    try {
        $sql = "INSERT INTO classes (name, description, invite_hash, teacher_id)
                VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $name, $description, $inviteHash, $teacherId);
        $stmt->execute();

        $newId = $conn->insert_id; 
        echo json_encode([
            "id" => $newId,
            "name" => $name,
            "description" => $description,
            "invite_hash" => $inviteHash
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["error" => $e->getMessage()]);
    }
}

function updateClass($conn, $id) {
    $data = json_decode(file_get_contents("php://input"), true);
    if (!$data) {
        http_response_code(400);
        echo json_encode(["error" => "No data sent for update"]);
        return;
    }

    $fields = [];
    $values = [];

    if (isset($data['name'])) {
        $fields[] = "name = ?";
        $values[] = $data['name'];
    }
    if (isset($data['description'])) {
        $fields[] = "description = ?";
        $values[] = $data['description'];
    }
    if (count($fields) === 0) {
        http_response_code(400);
        echo json_encode(["error" => "No valid fields provided"]);
        return;
    }

    $sql = "UPDATE classes SET " . implode(", ", $fields) . " WHERE id = ?";
    $values[] = $id;

    try {
        $stmt = $conn->prepare($sql);
        $types = str_repeat("s", count($fields)) . "i";
        $stmt->bind_param($types, ...$values);

        $stmt->execute();
        echo json_encode(["message" => "Class updated successfully"]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["error" => $e->getMessage()]);
    }
}

function deleteClass($conn, $id) {
    try {
        $stmt = $conn->prepare("DELETE FROM classes WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        echo json_encode(["message" => "Class deleted"]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["error" => $e->getMessage()]);
    }
}

function generateInviteHash($length = 6) {
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $hash = '';
    for ($i = 0; $i < $length; $i++) {
        $hash .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $hash;
}
