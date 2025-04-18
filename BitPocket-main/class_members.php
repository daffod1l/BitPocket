<?php
header('Content-Type: application/json');
require_once 'db.php'; 
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
  case 'GET':
    getClassMembership($conn);
    break;
  case 'POST':
    addClassMember($conn);
    break;
  case 'DELETE':
    removeClassMember($conn);
    break;
  default:
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
}

function getClassMembership($conn) {
  if (!isset($_GET['class_id'])) {
    http_response_code(400);
    echo json_encode(["error" => "Missing class_id"]);
    return;
  }
  $classId = (int)$_GET['class_id'];

  // 1) find all enrolled
  $sqlEnrolled = "
    SELECT u.id, u.first_name, u.last_name
    FROM class_members cm
    JOIN users u ON u.id = cm.user_id
    WHERE cm.class_id = ?
  ";
  $stmt = $conn->prepare($sqlEnrolled);
  $stmt->bind_param("i", $classId);
  $stmt->execute();
  $resultEnrolled = $stmt->get_result();
  $enrolled = $resultEnrolled->fetch_all(MYSQLI_ASSOC);

  // 2) find all not enrolled (role='student', or maybe all users if you want to allow teacher membership)
  // e.g. if you only want to add users with role='student':
  $sqlNotEnrolled = "
    SELECT u.id, u.first_name, u.last_name
    FROM users u
    WHERE u.role = 'student'
      AND u.id NOT IN (
        SELECT user_id FROM class_members WHERE class_id = ?
      )
    ORDER BY u.id ASC
  ";
  $stmt2 = $conn->prepare($sqlNotEnrolled);
  $stmt2->bind_param("i", $classId);
  $stmt2->execute();
  $resultNotEnrolled = $stmt2->get_result();
  $notEnrolled = $resultNotEnrolled->fetch_all(MYSQLI_ASSOC);

  echo json_encode([
    "enrolled" => $enrolled,
    "not_enrolled" => $notEnrolled
  ]);
}

function addClassMember($conn) {
  // e.g. { "class_id":2, "user_id":7 }
  $data = json_decode(file_get_contents("php://input"), true);
  if (!$data || !isset($data['class_id']) || !isset($data['user_id'])) {
    http_response_code(400);
    echo json_encode(["error" => "Missing class_id or user_id"]);
    return;
  }
  $classId = (int)$data['class_id'];
  $userId = (int)$data['user_id'];

  try {
    $sql = "INSERT IGNORE INTO class_members (class_id, user_id) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $classId, $userId);
    $stmt->execute();
    echo json_encode(["message" => "User added to class"]);
  } catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
  }
}

function removeClassMember($conn) {
  if (!isset($_GET['class_id']) || !isset($_GET['user_id'])) {
    http_response_code(400);
    echo json_encode(["error" => "Missing class_id or user_id"]);
    return;
  }
  $classId = (int)$_GET['class_id'];
  $userId = (int)$_GET['user_id'];

  try {
    $sql = "DELETE FROM class_members WHERE class_id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $classId, $userId);
    $stmt->execute();
    echo json_encode(["message" => "User removed from class"]);
  } catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
  }
}
