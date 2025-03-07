<?php

header('Content-Type: application/json'); 
require_once 'db.php'; 

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            getClassById($pdo, $_GET['id']);
        } else {
            getAllClasses($pdo);
        }
        break;

    case 'POST':
        createClass($pdo);
        break;

    case 'PUT':
        if (isset($_GET['id'])) {
            updateClass($pdo, $_GET['id']);
        } else {
            http_response_code(400);
            echo json_encode(["error" => "Missing class ID in query string"]);
        }
        break;

    case 'DELETE':
        if (isset($_GET['id'])) {
            deleteClass($pdo, $_GET['id']);
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

// Functions

function getAllClasses($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM classes ORDER BY id ASC");
        $rows = $stmt->fetchAll();
        echo json_encode($rows);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["error" => $e->getMessage()]);
    }
}

function getClassById($pdo, $id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM classes WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
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

function createClass($pdo) {
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
        $sql = "INSERT INTO classes (name, description, invite_hash) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$name, $description, $inviteHash]);

        $newId = $pdo->lastInsertId();
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

function updateClass($pdo, $id) {
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
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);
        echo json_encode(["message" => "Class updated successfully"]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["error" => $e->getMessage()]);
    }
}

function deleteClass($pdo, $id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM classes WHERE id = ?");
        $stmt->execute([$id]);
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
