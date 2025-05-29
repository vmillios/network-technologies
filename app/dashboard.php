<?php

// Start session.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in.
if (!isset($_SESSION['user_id']) and !isset($_SESSION['user_email'])) {
    // If not, redirect to login page.
// Add a session variable called redirect with the value dashboard.php.
    $_SESSION['redirect'] = 'dashboard.php';
    header('Location: login.php');

    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
</head>
<head>
    <title>YouTube Search via User Account</title>
</head>
<body>
    <?php 
        include 'navbar.php';
    ?>

    <h1>under construction</h1>
</body>
</html>