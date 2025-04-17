<?php
header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['user_id'], $data['title'], $data['description'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

$user_id = $data['user_id'];
$title = $data['title'];
$description = $data['description'];

$conn = new mysqli('localhost', 'root', '', 'listique');
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$stmt = $conn->prepare('INSERT INTO wishlists (user_id, title, description) VALUES (?, ?, ?)');
$stmt->bind_param('iss', $user_id, $title, $description);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to create wishlist']);
}

$stmt->close();
$conn->close();
?>
