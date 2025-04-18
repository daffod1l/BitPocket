<?php
header('Content-Type: application/json');
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Teacher') {
    http_response_code(403);
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}

$teacherId = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            getGroupSetById($conn, $_GET['id'], $teacherId);
        } else {
            getAllGroupSets($conn, $teacherId);
        }
        break;

    case 'POST':
        createGroupSet($conn, $teacherId);
        break;

    case 'PUT':
        if (isset($_GET['id'])) {
            updateGroupSet($conn, $_GET['id'], $teacherId);
        } else {
            http_response_code(400);
            echo json_encode(["error" => "Missing group set ID"]);
        }
        break;

    case 'DELETE':
        if (isset($_GET['id'])) {
            deleteGroupSet($conn, $_GET['id'], $teacherId);
        } else {
            http_response_code(400);
            echo json_encode(["error" => "Missing group set ID"]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Method not allowed"]);
        break;
}

// get all groups
function getAllGroupSets($conn, $teacherId) {
    try {
        $stmt = $conn->prepare("SELECT gs.* FROM group_sets gs JOIN classes c ON gs.class_id = c.id WHERE c.teacher_id = ? ORDER BY gs.id ASC");
        $stmt->bind_param("i", $teacherId);
        $stmt->execute();
        $result = $stmt->get_result();
        echo json_encode($result->fetch_all(MYSQLI_ASSOC));
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["error" => $e->getMessage()]);
    }
}

function getGroupSetById($conn, $id, $teacherId) {
    try {
        $stmt = $conn->prepare("SELECT gs.* FROM group_sets gs JOIN classes c ON gs.class_id = c.id WHERE gs.id = ? AND c.teacher_id = ?");
        $stmt->bind_param("ii", $id, $teacherId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        if ($row) {
            echo json_encode($row);
        } else {
            http_response_code(404);
            echo json_encode(["error" => "Group set not found"]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["error" => $e->getMessage()]);
    }
}

function createGroupSet($conn, $teacherId) {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data || !isset($data['name']) || !isset($data['class_id'])) {
        http_response_code(400);
        echo json_encode(["error" => "Missing required fields"]);
        return;
    }

    $classId = $data['class_id'];
    $name = $data['name'];
    $allowSelfSignup = !empty($data['allow_self_signup']) ? 1 : 0;
    $requireApproval = !empty($data['require_approval']) ? 1 : 0;
    $teacherApproval = !empty($data['require_teacher_approval']) ? 1 : 0;
    $leaderApproval = !empty($data['require_leader_approval']) ? 1 : 0;

    $stmt = $conn->prepare("SELECT id FROM classes WHERE id = ? AND teacher_id = ?");
    $stmt->bind_param("ii", $classId, $teacherId);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows === 0) {
        http_response_code(403);
        echo json_encode(["error" => "Unauthorized to create group set in this class"]);
        return;
    }

    try {
        $stmt = $conn->prepare("INSERT INTO group_sets (class_id, name, allow_self_signup, require_approval, require_teacher_approval, require_leader_approval) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isiiii", $classId, $name, $allowSelfSignup, $requireApproval, $teacherApproval, $leaderApproval);
        $stmt->execute();
        echo json_encode(["id" => $stmt->insert_id, "message" => "Group set created"]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["error" => $e->getMessage()]);
    }
}

function updateGroupSet($conn, $id, $teacherId) {
    $data = json_decode(file_get_contents("php://input"), true);
    if (!$data) {
        http_response_code(400);
        echo json_encode(["error" => "No data provided"]);
        return;
    }

    $stmt = $conn->prepare("SELECT gs.id FROM group_sets gs JOIN classes c ON gs.class_id = c.id WHERE gs.id = ? AND c.teacher_id = ?");
    $stmt->bind_param("ii", $id, $teacherId);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows === 0) {
        http_response_code(403);
        echo json_encode(["error" => "Unauthorized"]);
        return;
    }

    $fields = [];
    $types = "";
    $values = [];

    foreach (['class_id', 'name', 'allow_self_signup', 'require_approval', 'require_teacher_approval', 'require_leader_approval'] as $field) {
        if (isset($data[$field])) {
            $fields[] = "$field = ?";
            $values[] = $data[$field];
            $types .= is_int($data[$field]) ? "i" : "s";
        }
    }

    if (empty($fields)) {
        http_response_code(400);
        echo json_encode(["error" => "No valid fields to update"]);
        return;
    }

    $sql = "UPDATE group_sets SET " . implode(", ", $fields) . " WHERE id = ?";
    $types .= "i";
    $values[] = $id;

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$values);
    $stmt->execute();
    echo json_encode(["message" => "Group set updated"]);
}

function deleteGroupSet($conn, $id, $teacherId) {
    $stmt = $conn->prepare("SELECT gs.id FROM group_sets gs JOIN classes c ON gs.class_id = c.id WHERE gs.id = ? AND c.teacher_id = ?");
    $stmt->bind_param("ii", $id, $teacherId);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows === 0) {
        http_response_code(403);
        echo json_encode(["error" => "Unauthorized"]);
        return;
    }

    $stmt = $conn->prepare("DELETE FROM group_sets WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    echo json_encode(["message" => "Group set deleted"]);
}
