<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$id = $_POST['id'] ?? null; 

if (!$id) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

$conn = new mysqli('localhost', 'root', '', 'listique');
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$stmt = $conn->prepare('UPDATE wishlists SET shared = 1 WHERE id = ?');
$stmt->bind_param('i', $id);

if ($stmt->execute()) {
    $shareableLink = "http://localhost/Listique/Frontend/pages/wishlist/view.php?id=" . $id; // Generate shareable link
    echo json_encode(['success' => true, 'link' => $shareableLink]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to share wishlist']);
}

$stmt->close();
$conn->close();
?>
