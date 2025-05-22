<?php
session_start();
require_once 'database.php';

// Get redirect URL from GET or session
$redirect = $_GET['redirect'] ?? $_SESSION['redirect'] ?? 'dashboard.php';

// Save redirect URL in session for POST requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $_SESSION['redirect'] = $redirect;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = new Database();

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        die('Please fill all fields.');
    } else {
        $db->query("SELECT * FROM users WHERE email = :email");
        $db->bind(':email', $email);
        $user = $db->single();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];

            // Redirect to original page or dashboard
            header("Location: " . $_SESSION['redirect']);
            unset($_SESSION['redirect']); // clear redirect after use
            exit;
        } else {
            echo "Invalid email or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Login</title>
    <style>
    body { font-family: Verdana, sans-serif; margin: 40px; background-color: #39acc9; }
    h1 { color: #0b0a0a; }
    p { line-height: 1.6; }
    </style>
</head>
<body>
  <h2>Login with email</h2>
  <form method="POST" action="login.php">
    <label>email <input type="email" name="email" required></label><br><br>
    <label>Password: <input type="password" name="password" required></label><br><br>
    <button type="submit">Login</button>
  </form>
</body>
</html>