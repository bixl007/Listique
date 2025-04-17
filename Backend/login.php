<?php
require 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    try {
        $stmt = $pdo->prepare("SELECT id, password FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];

            if (!isset($_SESSION['user_id'])) {
                throw new Exception("Failed to set session for user ID.");
            }

            header("Location: ../Frontend/pages/dashboard/index.html");
            exit();
        } else {
            echo "Invalid email or password!";
        }
    } catch (Exception $e) {
        error_log($e->getMessage());
        echo "An error occurred during login. Please try again.";
    }
}
?>
