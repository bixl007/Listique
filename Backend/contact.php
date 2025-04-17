<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name && $email && $subject && $message) {
        $stmt = $pdo->prepare("INSERT INTO contacts (name, email, subject, message) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$name, $email, $subject, $message])) {
            // Redirect or show a success message
            header("Location: ../Frontend/pages/contactUs/index.html?success=1");
            exit();
        } else {
            // Handle DB error
            header("Location: ../Frontend/pages/contactUs/index.html?error=1");
            exit();
        }
    } else {
        // Handle validation error
        header("Location: ../Frontend/pages/contactUs/index.html?error=1");
        exit();
    }
}
?>
