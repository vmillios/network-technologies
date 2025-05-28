<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'database.php'; // your DB handler

// Prefill variables from session if available
$first_name = $last_name = $email = '';
if (isset($_SESSION['google_user'])) {
    $first_name = htmlspecialchars($_SESSION['google_user']['first_name']);
    $last_name = htmlspecialchars($_SESSION['google_user']['last_name']);
    $email = htmlspecialchars($_SESSION['google_user']['email']);
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = new Database();

    // Collect and sanitize inputs
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);

    // Basic validation
    if (empty($first_name) || empty($last_name) || empty($username) || empty($email)) {
        $error = 'Please fill all fields.';
    } else {
        // Check if username or email already exists
        $db->query("SELECT id FROM users WHERE username = :username OR email = :email");
        $db->bind(':username', $username);
        $db->bind(':email', $email);
        $existingUser = $db->single();

        if ($existingUser) {
            $error = 'Username or email already taken.';
        } else {

            // Insert new user
            $db->query("INSERT INTO users (first_name, last_name, username, email) VALUES (:first_name, :last_name, :username, :email)");
            $db->bind(':first_name', $first_name);
            $db->bind(':last_name', $last_name);
            $db->bind(':username', $username);
            $db->bind(':email', $email);

            if ($db->execute()) {
                // Clear Google session info after signup
                unset($_SESSION['google_user']);

                // Redirect to dashboard or login page
                header('Location: login.php');
                exit;
            } else {
                $error = 'Something went wrong. Please try again.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Register</title>
  <style>
    body { font-family: Verdana, sans-serif; background-color: #39acc9; margin: 40px; }
    h2 { color: #333; }
    form { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); max-width: 400px; }
    label { display: block; margin-bottom: 10px; }
    input { width: 100%; padding: 8px; margin-top: 4px; border: 1px solid #ccc; border-radius: 4px; }
    button { background: #39acc9; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; }
    button:hover { background: #2a8da5; }
    .message { margin-top: 20px; padding: 10px; border-radius: 5px; }
    .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
  </style>
</head>
<body>
  <?php
  include 'navbar.php';
  ?>
  <h2>Register</h2>

  <?php if (!empty($error)): ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
  <?php endif; ?>

  <form method="POST" action="signup.php">
    <label>First Name:
      <input type="text" name="first_name" value="<?= $first_name ?>" required>
    </label><br><br>

    <label>Last Name:
      <input type="text" name="last_name" value="<?= $last_name ?>" required>
    </label><br><br>

    <label>Username:
      <input type="text" name="username" value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>" required>
    </label><br><br>

    <label>Email:
      <input type="email" name="email" value="<?= $email ?>" readonly>
    </label><br><br>

    <button type="submit">Register</button>
  </form>
</body>
</html>
