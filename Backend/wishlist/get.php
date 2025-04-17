<?php
header('Content-Type: application/json');

$user_id = $_GET['user_id'];

$conn = new mysqli('localhost', 'root', '', 'listique');
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$stmt = $conn->prepare('SELECT id, title, description, created_at FROM wishlists WHERE user_id = ?');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

$wishlists = [];
while ($row = $result->fetch_assoc()) {
    $row['created_at'] = date('Y-m-d H:i:s', strtotime($row['created_at'])); // Ensure proper formatting
    $wishlists[] = $row;
}

echo json_encode($wishlists);

$stmt->close();
$conn->close();
?>
