<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$id) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing wishlist ID']);
    exit;
}

$conn = new mysqli('localhost', 'root', '', 'listique');
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Fetch wishlist and user name
$stmt = $conn->prepare('SELECT w.id, w.title, w.description, w.created_at, u.name as user_name FROM wishlists w JOIN users u ON w.user_id = u.id WHERE w.id = ? AND w.shared = 1');
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Database query failed']);
    exit;
}
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $wishlist = $result->fetch_assoc();
    // Try to decode description as JSON array for items
    $desc = $wishlist['description'];
    $items = json_decode($desc, true);
    if (is_array($items)) {
        $wishlist['items'] = $items;
    }
    // Add a field for the main heading (name's wishlist)
    $wishlist['main_title'] = $wishlist['user_name'] . "'s Wishlist";
    // The wishlist title remains as is
    echo json_encode($wishlist);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Wishlist not found or not shared']);
}

$stmt->close();
$conn->close();
?>
