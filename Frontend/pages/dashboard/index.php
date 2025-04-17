<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
</head>
<body>
    This is the dashboard
</body>
</html>
