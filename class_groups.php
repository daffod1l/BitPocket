<?php

header('Content-Type: application/json');
require_once 'db.php'; 

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            getClassGroupById($pdo, $_GET['id']);
        } else {
            getAllClassGroups($pdo);
        }
        break;

    case 'POST':
        createClassGroup($pdo);
        break;

    case 'PUT':
        if (isset($_GET['id'])) {
            updateClassGroup($pdo, $_GET['id']);
        } else {
            http_response_code(400);
            echo json_encode(["error" => "Missing class group ID"]);
        }
        break;

    case 'DELETE':
        if (isset($_GET['id'])) {
            deleteClassGroup($pdo, $_GET['id']);
        } else {
            http_response_code(400);
            echo json_encode(["error" => "Missing class group ID"]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Method not allowed"]);
        break;
}

// Functions

function getAllClassGroups($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM class_groups ORDER BY id ASC");
        $rows = $stmt->fetchAll();
        echo json_encode($rows);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["error" => $e->getMessage()]);
    }
}

function getClassGroupById($pdo, $id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM class_groups WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) {
            echo json_encode($row);
        } else {
            http_response_code(404);
            echo json_encode(["error" => "Class group not found"]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["error" => $e->getMessage()]);
    }
}

function createClassGroup($pdo) {
    $data = json_decode(file_get_contents("php://input"), true);
    if (!$data || !isset($data['group_set_id']) || !isset($data['name'])) {
        http_response_code(400);
        echo json_encode(["error" => "Missing required fields (group_set_id, name)"]);
        return;
    }

    $groupSetId = $data['group_set_id'];
    $name = $data['name'];
    $leaderId = isset($data['leader_id']) ? $data['leader_id'] : null;

    try {
        $sql = "INSERT INTO class_groups (group_set_id, name, leader_id)
                VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$groupSetId, $name, $leaderId]);

        $newId = $pdo->lastInsertId();
        echo json_encode([
            "id" => $newId,
            "group_set_id" => $groupSetId,
            "name" => $name,
            "leader_id" => $leaderId
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["error" => $e->getMessage()]);
    }
}

function updateClassGroup($pdo, $id) {
    $data = json_decode(file_get_contents("php://input"), true);
    if (!$data) {
        http_response_code(400);
        echo json_encode(["error" => "No data to update"]);
        return;
    }

    $fields = [];
    $values = [];

    if (isset($data['group_set_id'])) {
        $fields[] = "group_set_id = ?";
        $values[] = $data['group_set_id'];
    }
    if (isset($data['name'])) {
        $fields[] = "name = ?";
        $values[] = $data['name'];
    }
    if (array_key_exists('leader_id', $data)) {
        // Could be null or numeric
        $fields[] = "leader_id = ?";
        $values[] = $data['leader_id'];
    }

    if (count($fields) === 0) {
        http_response_code(400);
        echo json_encode(["error" => "No valid fields to update"]);
        return;
    }

    $sql = "UPDATE class_groups SET " . implode(", ", $fields) . " WHERE id = ?";
    $values[] = $id;

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);
        echo json_encode(["message" => "Class group updated"]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["error" => $e->getMessage()]);
    }
}

function deleteClassGroup($pdo, $id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM class_groups WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(["message" => "Class group deleted"]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["error" => $e->getMessage()]);
    }
}
