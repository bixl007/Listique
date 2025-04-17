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

// Start the session - this is critical!
session_start();

// Log the session data for debugging
error_log('Session data: ' . print_r($_SESSION, true));

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    error_log('User not logged in - no session ID found');
    http_response_code(401);
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

// Get the user ID from the session
$user_id = $_SESSION['user_id'];
error_log('Fetching user data for ID: ' . $user_id);

// Connect to the database
$conn = new mysqli('localhost', 'root', '', 'listique');
if ($conn->connect_error) {
    error_log('Database connection failed: ' . $conn->connect_error);
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Prepare and execute query
$stmt = $conn->prepare('SELECT id, name, email, created_at, join_date FROM users WHERE id = ?');
if (!$stmt) {
    error_log('Prepare statement failed: ' . $conn->error);
    http_response_code(500);
    echo json_encode(['error' => 'Database query failed']);
    exit;
}

$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Check for results
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    error_log('User found: ' . print_r($user, true));
    echo json_encode($user);
} else {
    error_log('User not found for ID: ' . $user_id);
    http_response_code(404);
    echo json_encode(['error' => 'User not found']);
}

// Close database connection
$stmt->close();
$conn->close();
?>
