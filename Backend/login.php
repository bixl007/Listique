<?php
require 'config.php';
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Credentials: true'); // Allow credentials for cross-origin requests

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    try {
        $stmt = $pdo->prepare("SELECT id, name, password FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];

            // Return success response
            echo json_encode(['success' => true, 'message' => 'Login successful']);
        } else {
            // Return error response for invalid credentials
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Invalid email or password!']);
        }
    } catch (Exception $e) {
        error_log($e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'An error occurred during login. Please try again.']);
    }
}
?>
