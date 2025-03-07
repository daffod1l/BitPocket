<?php
header('Content-Type: application/json');
require_once 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        getRewardAssignments($pdo);
        break;

    case 'POST':
        createRewardAssignment($pdo);
        break;

    case 'PUT':
        if (isset($_GET['id'])) {
            updateRewardAssignment($pdo, $_GET['id']);
        } else {
            http_response_code(400);
            echo json_encode(["error" => "Missing id param"]);
        }
        break;

    case 'DELETE':
        if (isset($_GET['id'])) {
            deleteRewardAssignment($pdo, $_GET['id']);
        } else {
            http_response_code(400);
            echo json_encode(["error" => "Missing id param"]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Method not allowed"]);
        break;
}

// Functions

function getRewardAssignments($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM reward_assignments ORDER BY id ASC");
        $rows = $stmt->fetchAll();
        echo json_encode($rows);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["error" => $e->getMessage()]);
    }
}

function createRewardAssignment($pdo) {
    $data = json_decode(file_get_contents("php://input"), true);
    if (!$data || !isset($data['reward_id']) || !isset($data['user_id']) || !isset($data['assigned_by'])) {
        http_response_code(400);
        echo json_encode(["error" => "Missing required fields (reward_id, user_id, assigned_by)"]);
        return;
    }

    $rewardId = $data['reward_id'];
    $userId = $data['user_id'];
    $assignedBy = $data['assigned_by'];

    try {
        $sql = "INSERT INTO reward_assignments (reward_id, user_id, assigned_by)
                VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$rewardId, $userId, $assignedBy]);

        $newId = $pdo->lastInsertId();
        echo json_encode([
            "id" => $newId,
            "reward_id" => $rewardId,
            "user_id" => $userId,
            "assigned_by" => $assignedBy
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["error" => $e->getMessage()]);
    }
}

function updateRewardAssignment($pdo, $id) {
    $data = json_decode(file_get_contents("php://input"), true);
    if (!$data) {
        http_response_code(400);
        echo json_encode(["error" => "No data to update"]);
        return;
    }

    $fields = [];
    $values = [];

    if (isset($data['reward_id'])) {
        $fields[] = "reward_id = ?";
        $values[] = $data['reward_id'];
    }
    if (isset($data['user_id'])) {
        $fields[] = "user_id = ?";
        $values[] = $data['user_id'];
    }
    if (isset($data['assigned_by'])) {
        $fields[] = "assigned_by = ?";
        $values[] = $data['assigned_by'];
    }

    if (count($fields) === 0) {
        http_response_code(400);
        echo json_encode(["error" => "No valid fields"]);
        return;
    }

    $sql = "UPDATE reward_assignments SET " . implode(", ", $fields) . " WHERE id = ?";
    $values[] = $id;

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);
        echo json_encode(["message" => "Reward assignment updated"]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["error" => $e->getMessage()]);
    }
}

function deleteRewardAssignment($pdo, $id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM reward_assignments WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(["message" => "Reward assignment deleted"]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["error" => $e->getMessage()]);
    }
}
