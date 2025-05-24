<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
} // Only needed once

?>

<!-- Navbar styles -->
<style>
    .navbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background-color: #333;
        padding: 10px 20px;
        color: white;
        font-family: Arial, sans-serif;
    }
    .navbar .left {
        /* your left side content */
    }
    .navbar .right {
        display: flex;
        align-items: center;
        gap: 15px;
    }
    .navbar a {
        color: white;
        text-decoration: none;
        font-weight: bold;
    }
    .navbar a:hover {
        text-decoration: underline;
    }
</style>

<!-- Navbar HTML -->
<nav>
    <div class="navbar">
    <?php if (isset($_SESSION['user_id'])): ?>
        <div class='left'>
            <a href="dashboard.php">Dashboard</a>
            <a href="profile.php">Profile</a>
        </div>
        <div class='right'>
            <span><?php echo $_SESSION['username']?></span>
            <a href="logout.php">Logout</a>
        </div>
    <?php else: ?>
        <div class='left'>
            <a href="index.php">Home</a>
        </div>
        <div class='right'>
            <a href="signup.php">Register</a>
            <a href="login.php">Login</a>
        </div>
    <?php endif; ?>
    </div>
</nav>