<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers for CORS and JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Origin: http://localhost');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Start the session
session_start();

// Log the session data for debugging
error_log('Session data in create.php: ' . print_r($_SESSION, true));
error_log('Request method: ' . $_SERVER['REQUEST_METHOD']);

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    error_log('Session not found: User not logged in');
    http_response_code(401);
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

// Get raw POST data and log it
$raw_data = file_get_contents('php://input');
error_log('Raw input data: ' . $raw_data);

// Parse JSON data
$data = json_decode($raw_data, true);
error_log('Parsed input data: ' . print_r($data, true));

// Validate input
if (!isset($data['title'])) {
    error_log('Invalid input: Missing title');
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input - missing title']);
    exit;
}

$title = $data['title'];
// Accept items as array, fallback to description if not present
$items = isset($data['items']) && is_array($data['items']) ? $data['items'] : [];
$description = !empty($items) ? json_encode($items) : ($data['description'] ?? '');

if (empty($items) && empty($description)) {
    error_log('Invalid input: Missing items or description');
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input - missing items or description']);
    exit;
}

// Get user ID from session
$user_id = $_SESSION['user_id'];

error_log('Creating wishlist for user ID: ' . $user_id . ', Title: ' . $title);

// Connect to the database
$conn = new mysqli('localhost', 'root', '', 'listique');
if ($conn->connect_error) {
    error_log('Database connection failed: ' . $conn->connect_error);
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Prevent duplicate insertions by checking for double submit (optional, but recommended)
// If you want to prevent double creation due to double form submit, you can check for a recent identical wishlist:
$check_stmt = $conn->prepare('SELECT id FROM wishlists WHERE user_id = ? AND title = ? AND description = ? ORDER BY id DESC LIMIT 1');
if ($check_stmt) {
    $check_stmt->bind_param('iss', $user_id, $title, $description);
    $check_stmt->execute();
    $check_stmt->store_result();
    if ($check_stmt->num_rows > 0) {
        error_log('Duplicate wishlist detected, skipping creation');
        echo json_encode([
            'success' => true,
            'message' => 'Wishlist already exists',
            // Optionally, return the existing ID
        ]);
        $check_stmt->close();
        $conn->close();
        exit;
    }
    $check_stmt->close();
}

// Prepare and execute query
$stmt = $conn->prepare('INSERT INTO wishlists (user_id, title, description) VALUES (?, ?, ?)');
if (!$stmt) {
    error_log('Prepare statement failed: ' . $conn->error);
    http_response_code(500);
    echo json_encode(['error' => 'Database query failed: ' . $conn->error]);
    exit;
}

$stmt->bind_param('iss', $user_id, $title, $description);

// Execute the query
if ($stmt->execute()) {
    error_log('Wishlist created successfully with ID: ' . $conn->insert_id);
    echo json_encode([
        'success' => true, 
        'message' => 'Wishlist created successfully', 
        'id' => $conn->insert_id,
        'items' => $items
    ]);
} else {
    error_log('Failed to create wishlist: ' . $stmt->error);
    http_response_code(500);
    echo json_encode(['error' => 'Failed to create wishlist: ' . $stmt->error]);
}

// Close database connection
$stmt->close();
$conn->close();
?>
