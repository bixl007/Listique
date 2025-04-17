<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Credentials: true'); // Allow credentials for cross-origin requests
session_start();

// Destroy the session
session_unset();
session_destroy();

// Return a success response
echo json_encode(['success' => true, 'message' => 'Logout successful']);
?>
