<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
session_unset();    // Clear all session variables
session_destroy();  // Destroy the session
header("Location: index.php");
exit;