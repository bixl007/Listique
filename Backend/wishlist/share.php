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
error_log('Session data in share.php: ' . print_r($_SESSION, true));

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log('Invalid method: ' . $_SERVER['REQUEST_METHOD']);
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    error_log('Session not found: User not logged in');
    http_response_code(401);
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

// Get wishlist ID from POST data
$id = $_POST['id'] ?? null; 
error_log('Wishlist ID from POST: ' . $id);

// Validate input
if (!$id) {
    error_log('Invalid input: Missing wishlist ID');
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input - missing wishlist ID']);
    exit;
}

// Connect to the database
$conn = new mysqli('localhost', 'root', '', 'listique');
if ($conn->connect_error) {
    error_log('Database connection failed: ' . $conn->connect_error);
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// First verify the wishlist belongs to the current user
$user_id = $_SESSION['user_id'];
$verify_stmt = $conn->prepare('SELECT id FROM wishlists WHERE id = ? AND user_id = ?');
if (!$verify_stmt) {
    error_log('Prepare verification statement failed: ' . $conn->error);
    http_response_code(500);
    echo json_encode(['error' => 'Database query failed']);
    exit;
}

$verify_stmt->bind_param('ii', $id, $user_id);
$verify_stmt->execute();
$verify_result = $verify_stmt->get_result();

if ($verify_result->num_rows == 0) {
    error_log('Wishlist not found or does not belong to user');
    http_response_code(403);
    echo json_encode(['error' => 'You do not have permission to share this wishlist']);
    $verify_stmt->close();
    $conn->close();
    exit;
}
$verify_stmt->close();

// Update the wishlist to mark it as shared
$stmt = $conn->prepare('UPDATE wishlists SET shared = 1 WHERE id = ?');
if (!$stmt) {
    error_log('Prepare update statement failed: ' . $conn->error);
    http_response_code(500);
    echo json_encode(['error' => 'Database query failed']);
    exit;
}

$stmt->bind_param('i', $id);

if ($stmt->execute()) {
    $shareableLink = "http://localhost/Listique/Frontend/pages/wishlist/view.php?id=" . $id;
    error_log('Wishlist shared successfully with link: ' . $shareableLink);
    echo json_encode(['success' => true, 'link' => $shareableLink]);
} else {
    error_log('Failed to share wishlist: ' . $stmt->error);
    http_response_code(500);
    echo json_encode(['error' => 'Failed to share wishlist']);
}

// Close database connection
$stmt->close();
$conn->close();
?>
