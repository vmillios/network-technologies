<?php
session_start();
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
    $password = $_POST['password'];

    // Basic validation
    if (empty($first_name) || empty($last_name) || empty($username) || empty($email) || empty($password)) {
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
            // Hash password
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            // Insert new user
            $db->query("INSERT INTO users (first_name, last_name, username, email, password) VALUES (:first_name, :last_name, :username, :email, :password)");
            $db->bind(':first_name', $first_name);
            $db->bind(':last_name', $last_name);
            $db->bind(':username', $username);
            $db->bind(':email', $email);
            $db->bind(':password', $passwordHash);

            if ($db->execute()) {
                // Clear Google session info after signup
                unset($_SESSION['google_user']);

                // Redirect to dashboard or login page
                header('Location: dashboard.php');
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
</head>
<body>
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
      <input type="email" name="email" value="<?= $email ?>" required>
    </label><br><br>

    <label>Password:
      <input type="password" name="password" required>
    </label><br><br>

    <button type="submit">Register</button>
  </form>
</body>
</html>
