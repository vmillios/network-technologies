<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$current = basename($_SERVER["PHP_SELF"]);
?>
<nav>
  <div class="nav-links">
    <a href="index.php" class="nav-logo <?= $current === 'index.php' ? 'active' : '' ?>">
      <i class="fas fa-clover"></i> <span>Rigganada</span>
    </a>
    <?php if (isset($_SESSION["user_id"])): ?>
      <a href="my_lists.php" class="<?= $current === 'my_lists.php' ? 'active' : '' ?>">
        <i class="fas fa-clipboard-list"></i> Οι λίστες μου
      </a>
      <a href="public_lists.php" class="<?= $current === 'public_lists.php' ? 'active' : '' ?>">
        <i class="fas fa-globe"></i> Δημόσιες λίστες
      </a>
      <a href="youtube_search.php" class="<?= $current === 'youtube_search.php' ? 'active' : '' ?>">
        <i class="fab fa-youtube"></i> YouTube
      </a>
      <a href="open_data.php" class="<?= $current === 'open_data.php' ? 'active' : '' ?>">
        <i class="fas fa-file-export"></i> Open Data
      </a>
    <?php endif; ?>
    <a href="search.php" class="<?= $current === 'search.php' ? 'active' : '' ?>">
      <i class="fas fa-search"></i> Αναζήτηση
    </a>

  </div>

  <div class="nav-user">
    <button id="theme-toggle" onclick="toggleTheme()" title="Εναλλαγή θέματος">
      <i class="fas fa-moon"></i>
    </button>
    <?php if (isset($_SESSION["user_id"])): ?>
      <a href="profile.php" class="<?= $current === 'profile.php' ? 'active' : '' ?>" >
        <i class="fas fa-user"></i> <?= htmlspecialchars($_SESSION["username"]) ?>
      </a>
      <a href="logout.php">
        <i class="fas fa-sign-out-alt"></i> Αποσύνδεση
      </a>
    <?php else: ?>
      <a href="register.php" class="<?= $current === 'register.php' ? 'active' : '' ?>">
        <i class="fas fa-user-plus"></i> Εγγραφή
      </a>
      <a href="login.php" class="<?= $current === 'login.php' ? 'active' : '' ?>">
        <i class="fas fa-sign-in-alt"></i> Σύνδεση
      </a>
    <?php endif; ?>
  </div>
</nav>
