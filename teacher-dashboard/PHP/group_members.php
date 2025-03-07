<?php

header('Content-Type: application/json');
require_once 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        getGroupMembers($pdo);
        break;

    case 'POST':
        createGroupMember($pdo);
        break;

    case 'PUT':
        if (isset($_GET['user_id']) && isset($_GET['group_id'])) {
            updateGroupMember($pdo, $_GET['user_id'], $_GET['group_id']);
        } else {
            http_response_code(400);
            echo json_encode(["error" => "Missing user_id and/or group_id"]);
        }
        break;

    case 'DELETE':
        if (isset($_GET['user_id']) && isset($_GET['group_id'])) {
            deleteGroupMember($pdo, $_GET['user_id'], $_GET['group_id']);
        } else {
            http_response_code(400);
            echo json_encode(["error" => "Missing user_id and/or group_id"]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Method not allowed"]);
        break;
}

// Functions

function getGroupMembers($pdo) {
    try {
        if (isset($_GET['group_id'])) {
            $groupId = $_GET['group_id'];
            $stmt = $pdo->prepare("SELECT * FROM group_members WHERE group_id = ?");
            $stmt->execute([$groupId]);
            $rows = $stmt->fetchAll();
            echo json_encode($rows);
            return;
        }

        if (isset($_GET['user_id'])) {
            $userId = $_GET['user_id'];
            $stmt = $pdo->prepare("SELECT * FROM group_members WHERE user_id = ?");
            $stmt->execute([$userId]);
            $rows = $stmt->fetchAll();
            echo json_encode($rows);
            return;
        }

        $stmt = $pdo->query("SELECT * FROM group_members");
        $rows = $stmt->fetchAll();
        echo json_encode($rows);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["error" => $e->getMessage()]);
    }
}

function createGroupMember($pdo) {
    $data = json_decode(file_get_contents("php://input"), true);
    if (!$data || !isset($data['user_id']) || !isset($data['group_id'])) {
        http_response_code(400);
        echo json_encode(["error" => "Missing user_id or group_id"]);
        return;
    }

    $userId = $data['user_id'];
    $groupId = $data['group_id'];
    $isPending = isset($data['is_pending']) ? (bool)$data['is_pending'] : false;

    try {
        $stmt = $pdo->prepare("INSERT INTO group_members (user_id, group_id, is_pending) VALUES (?, ?, ?)");
        $stmt->execute([$userId, $groupId, $isPending]);

        echo json_encode([
            "user_id" => $userId,
            "group_id" => $groupId,
            "is_pending" => $isPending
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["error" => $e->getMessage()]);
    }
}

function updateGroupMember($pdo, $userId, $groupId) {
    $data = json_decode(file_get_contents("php://input"), true);
    if (!$data) {
        http_response_code(400);
        echo json_encode(["error" => "No data to update"]);
        return;
    }

    $fields = [];
    $values = [];

    if (isset($data['is_pending'])) {
        $fields[] = "is_pending = ?";
        $values[] = (bool)$data['is_pending'];
    }

    if (count($fields) === 0) {
        http_response_code(400);
        echo json_encode(["error" => "No valid fields"]);
        return;
    }

    $sql = "UPDATE group_members SET " . implode(", ", $fields) . " 
            WHERE user_id = ? AND group_id = ?";
    $values[] = $userId;
    $values[] = $groupId;

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);
        echo json_encode(["message" => "Group membership updated"]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["error" => $e->getMessage()]);
    }
}

function deleteGroupMember($pdo, $userId, $groupId) {
    try {
        $stmt = $pdo->prepare("DELETE FROM group_members WHERE user_id = ? AND group_id = ?");
        $stmt->execute([$userId, $groupId]);
        echo json_encode(["message" => "Group member deleted"]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["error" => $e->getMessage()]);
    }
}
