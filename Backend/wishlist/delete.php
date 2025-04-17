<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Origin: http://localhost');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

$id = $_POST['id'] ?? null;
if (!$id) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input - missing wishlist ID']);
    exit;
}

$user_id = $_SESSION['user_id'];
$conn = new mysqli('localhost', 'root', '', 'listique');
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Ensure the wishlist belongs to the user
$stmt = $conn->prepare('DELETE FROM wishlists WHERE id = ? AND user_id = ?');
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Database query failed']);
    exit;
}
$stmt->bind_param('ii', $id, $user_id);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo json_encode([
        'success' => true,
        'message' => 'Wishlist deleted'
    ]);
} else {
    http_response_code(403);
    echo json_encode([
        'error' => 'Wishlist not found or permission denied'
    ]);
}

$stmt->close();
$conn->close();
?>
