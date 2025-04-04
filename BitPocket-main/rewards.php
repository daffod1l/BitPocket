<?php
header('Content-Type: application/json');
require_once 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            getRewardById($pdo, $_GET['id']);
        } else {
            getAllRewards($pdo);
        }
        break;

    case 'POST':
        createReward($pdo);
        break;

    case 'PUT':
        if (isset($_GET['id'])) {
            updateReward($pdo, $_GET['id']);
        } else {
            http_response_code(400);
            echo json_encode(["error" => "Missing reward ID"]);
        }
        break;

    case 'DELETE':
        if (isset($_GET['id'])) {
            deleteReward($pdo, $_GET['id']);
        } else {
            http_response_code(400);
            echo json_encode(["error" => "Missing reward ID"]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Method not allowed"]);
        break;
}

// Functions

function getAllRewards($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM rewards ORDER BY id ASC");
        $rows = $stmt->fetchAll();
        echo json_encode($rows);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["error" => $e->getMessage()]);
    }
}

function getRewardById($pdo, $id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM rewards WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) {
            echo json_encode($row);
        } else {
            http_response_code(404);
            echo json_encode(["error" => "Reward not found"]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["error" => $e->getMessage()]);
    }
}

function createReward($pdo) {
    $data = json_decode(file_get_contents("php://input"), true);
    if (!$data || !isset($data['name'])) {
        http_response_code(400);
        echo json_encode(["error" => "Missing 'name' field"]);
        return;
    }

    $name = $data['name'];
    $description = $data['description'] ?? '';
    $cost = isset($data['cost']) ? (int)$data['cost'] : 0;

    try {
        $stmt = $pdo->prepare("INSERT INTO rewards (name, description, cost) VALUES (?, ?, ?)");
        $stmt->execute([$name, $description, $cost]);

        $newId = $pdo->lastInsertId();
        echo json_encode([
            "id" => $newId,
            "name" => $name,
            "description" => $description,
            "cost" => $cost
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["error" => $e->getMessage()]);
    }
}

function updateReward($pdo, $id) {
    $data = json_decode(file_get_contents("php://input"), true);
    if (!$data) {
        http_response_code(400);
        echo json_encode(["error" => "No data to update"]);
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
    if (isset($data['cost'])) {
        $fields[] = "cost = ?";
        $values[] = (int)$data['cost'];
    }

    if (count($fields) === 0) {
        http_response_code(400);
        echo json_encode(["error" => "No valid fields"]);
        return;
    }

    $sql = "UPDATE rewards SET " . implode(", ", $fields) . " WHERE id = ?";
    $values[] = $id;

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);
        echo json_encode(["message" => "Reward updated"]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["error" => $e->getMessage()]);
    }
}

function deleteReward($pdo, $id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM rewards WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(["message" => "Reward deleted"]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["error" => $e->getMessage()]);
    }
}
