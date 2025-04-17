<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Credentials: true'); // Allow credentials for cross-origin requests
header('Access-Control-Allow-Origin: http://localhost'); // Adjust based on your frontend's origin
header('Access-Control-Allow-Methods: GET, POST, OPTIONS'); // Allow necessary HTTP methods
header('Access-Control-Allow-Headers: Content-Type, Authorization'); // Allow necessary headers
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Handle preflight requests
    http_response_code(204);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

$conn = new mysqli('localhost', 'root', '', 'listique');
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$stmt = $conn->prepare('SELECT id, name, email FROM users WHERE id = ?');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo json_encode($user);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'User not found']);
}

$stmt->close();
$conn->close();
?>
