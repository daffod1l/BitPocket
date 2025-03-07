<?php

header('Content-Type: application/json');
require_once 'db.php'; 

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            getGroupSetById($pdo, $_GET['id']);
        } else {
            getAllGroupSets($pdo);
        }
        break;

    case 'POST':
        createGroupSet($pdo);
        break;

    case 'PUT':
        if (isset($_GET['id'])) {
            updateGroupSet($pdo, $_GET['id']);
        } else {
            http_response_code(400);
            echo json_encode(["error" => "Missing group set ID"]);
        }
        break;

    case 'DELETE':
        if (isset($_GET['id'])) {
            deleteGroupSet($pdo, $_GET['id']);
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

// Functions

function getAllGroupSets($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM group_sets ORDER BY id ASC");
        $rows = $stmt->fetchAll();
        echo json_encode($rows);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["error" => $e->getMessage()]);
    }
}

function getGroupSetById($pdo, $id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM group_sets WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
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

function createGroupSet($pdo) {
    $data = json_decode(file_get_contents("php://input"), true);
    if (!$data || !isset($data['class_id']) || !isset($data['name'])) {
        http_response_code(400);
        echo json_encode(["error" => "Missing required fields (class_id, name)"]);
        return;
    }

    $classId = $data['class_id'];
    $name = $data['name'];
    $allowSelfSignup = isset($data['allow_self_signup']) ? (bool)$data['allow_self_signup'] : false;
    $requireApproval = isset($data['require_approval']) ? (bool)$data['require_approval'] : false;
    $teacherApproval = isset($data['require_teacher_approval']) ? (bool)$data['require_teacher_approval'] : false;
    $leaderApproval = isset($data['require_leader_approval']) ? (bool)$data['require_leader_approval'] : false;

    try {
        $sql = "INSERT INTO group_sets
                (class_id, name, allow_self_signup, require_approval, require_teacher_approval, require_leader_approval)
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$classId, $name, $allowSelfSignup, $requireApproval, $teacherApproval, $leaderApproval]);
        $newId = $pdo->lastInsertId();

        echo json_encode([
            "id" => $newId,
            "class_id" => $classId,
            "name" => $name,
            "allow_self_signup" => $allowSelfSignup,
            "require_approval" => $requireApproval,
            "require_teacher_approval" => $teacherApproval,
            "require_leader_approval" => $leaderApproval
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["error" => $e->getMessage()]);
    }
}

function updateGroupSet($pdo, $id) {
    $data = json_decode(file_get_contents("php://input"), true);
    if (!$data) {
        http_response_code(400);
        echo json_encode(["error" => "No data to update"]);
        return;
    }

    $fields = [];
    $values = [];

    // If user passed in any of these fields, we'll update them
    if (isset($data['class_id'])) {
        $fields[] = "class_id = ?";
        $values[] = $data['class_id'];
    }
    if (isset($data['name'])) {
        $fields[] = "name = ?";
        $values[] = $data['name'];
    }
    if (isset($data['allow_self_signup'])) {
        $fields[] = "allow_self_signup = ?";
        $values[] = (bool)$data['allow_self_signup'];
    }
    if (isset($data['require_approval'])) {
        $fields[] = "require_approval = ?";
        $values[] = (bool)$data['require_approval'];
    }
    if (isset($data['require_teacher_approval'])) {
        $fields[] = "require_teacher_approval = ?";
        $values[] = (bool)$data['require_teacher_approval'];
    }
    if (isset($data['require_leader_approval'])) {
        $fields[] = "require_leader_approval = ?";
        $values[] = (bool)$data['require_leader_approval'];
    }

    if (count($fields) === 0) {
        http_response_code(400);
        echo json_encode(["error" => "No valid fields provided"]);
        return;
    }

    $sql = "UPDATE group_sets SET " . implode(", ", $fields) . " WHERE id = ?";
    $values[] = $id;

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);
        echo json_encode(["message" => "Group set updated"]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["error" => $e->getMessage()]);
    }
}

function deleteGroupSet($pdo, $id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM group_sets WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(["message" => "Group set deleted"]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["error" => $e->getMessage()]);
    }
}
