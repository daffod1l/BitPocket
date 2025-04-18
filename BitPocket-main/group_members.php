<?php
ob_start();
header('Content-Type: application/json');
require_once 'db.php'; 

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        getGroupMembers($conn);
        break;

    case 'POST':
        createGroupMember($conn);
        break;

    case 'PUT':
        if (isset($_GET['user_id']) && isset($_GET['group_id'])) {
            updateGroupMember($conn, $_GET['user_id'], $_GET['group_id']);
        } else {
            http_response_code(400);
            echo json_encode(["error" => "Missing user_id and/or group_id"]);
        }
        break;

    case 'DELETE':
        if (isset($_GET['user_id']) && isset($_GET['group_id'])) {
            deleteGroupMember($conn, $_GET['user_id'], $_GET['group_id']);
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

function getGroupMembers($conn) {
    if (isset($_GET['group_id'])) {
        $groupId = $_GET['group_id'];

        // Get class_id based on group_id (group_sets.id)
        $stmt = $conn->prepare("SELECT class_id FROM group_sets WHERE id = ?");
        $stmt->bind_param("i", $groupId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if (!$row) {
            http_response_code(404);
            echo json_encode(["error" => "Group not found."]);
            return;
        }

        $classId = $row['class_id'];

        // Available students in class who are NOT in the group
        $stmt = $conn->prepare("
            SELECT u.id, u.first_name, u.last_name
            FROM class_members cm
            JOIN users u ON cm.user_id = u.id
            WHERE cm.class_id = ?
              AND u.id NOT IN (
                  SELECT user_id FROM group_members WHERE group_id = ?
              )
        ");
        $stmt->bind_param("ii", $classId, $groupId);
        $stmt->execute();
        $available = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        // Students already in the group
        $stmt = $conn->prepare("
            SELECT u.id, u.first_name, u.last_name, gm.is_pending
            FROM group_members gm
            JOIN users u ON gm.user_id = u.id
            WHERE gm.group_id = ?
        ");
        $stmt->bind_param("i", $groupId);
        $stmt->execute();
        $enrolled = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        echo json_encode(["enrolled" => $enrolled, "available" => $available]);
        return;
    }

    http_response_code(400);
    echo json_encode(["error" => "Missing group_id"]);
}

function createGroupMember($conn) {
    $data = json_decode(file_get_contents("php://input"), true);
    if (!$data || !isset($data['user_id']) || !isset($data['group_id'])) {
        http_response_code(400);
        echo json_encode(["error" => "Missing user_id or group_id"]);
        return;
    }

    $userId = $data['user_id'];
    $groupId = $data['group_id'];
    $isPending = isset($data['is_pending']) ? (int)$data['is_pending'] : 0;

    try {
        $stmt = $conn->prepare("INSERT INTO group_members (user_id, group_id, is_pending) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $userId, $groupId, $isPending);
        $stmt->execute();

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

function updateGroupMember($conn, $userId, $groupId) {
    $data = json_decode(file_get_contents("php://input"), true);
    if (!$data || !isset($data['is_pending'])) {
        http_response_code(400);
        echo json_encode(["error" => "Missing fields"]);
        return;
    }

    $isPending = (int)$data['is_pending'];

    try {
        $stmt = $conn->prepare("UPDATE group_members SET is_pending = ? WHERE user_id = ? AND group_id = ?");
        $stmt->bind_param("iii", $isPending, $userId, $groupId);
        $stmt->execute();
        echo json_encode(["message" => "Group membership updated"]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["error" => $e->getMessage()]);
    }
}

function deleteGroupMember($conn, $userId, $groupId) {
    try {
        $stmt = $conn->prepare("DELETE FROM group_members WHERE user_id = ? AND group_id = ?");
        $stmt->bind_param("ii", $userId, $groupId);
        $stmt->execute();
        echo json_encode(["message" => "Group member deleted"]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["error" => $e->getMessage()]);
    }
}

// Cleanup any unexpected output
if (ob_get_length()) {
    ob_end_flush();
}
