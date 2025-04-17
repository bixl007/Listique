<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers for CORS and JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Origin: http://localhost');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Start the session
session_start();

// Log the session data for debugging
error_log('Session data in get.php: ' . print_r($_SESSION, true));

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    error_log('User not logged in - no session ID found in get.php');
    http_response_code(401);
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

// Get user ID from session instead of GET parameter
$user_id = $_SESSION['user_id'];
error_log('Fetching wishlists for user ID: ' . $user_id);

// If GET parameter is provided, validate it matches the session user
if (isset($_GET['user_id']) && $_GET['user_id'] != $user_id) {
    error_log('Mismatch between GET user_id and session user_id');
    // Allow the session user_id to take precedence
}

// Connect to the database
$conn = new mysqli('localhost', 'root', '', 'listique');
if ($conn->connect_error) {
    error_log('Database connection failed: ' . $conn->connect_error);
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Prepare and execute query
$stmt = $conn->prepare('SELECT id, title, description, created_at FROM wishlists WHERE user_id = ?');
if (!$stmt) {
    error_log('Prepare statement failed: ' . $conn->error);
    http_response_code(500);
    echo json_encode(['error' => 'Database query failed']);
    exit;
}

$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Process results
$wishlists = [];
while ($row = $result->fetch_assoc()) {
    $row['created_at'] = date('Y-m-d H:i:s', strtotime($row['created_at'])); // Ensure proper formatting
    // Try to decode description as JSON array for items
    $desc = $row['description'];
    $items = json_decode($desc, true);
    if (is_array($items)) {
        $row['items'] = $items;
    }
    $wishlists[] = $row;
}

error_log('Found ' . count($wishlists) . ' wishlists for user ID: ' . $user_id);
echo json_encode($wishlists);

// Close database connection
$stmt->close();
$conn->close();
?>
