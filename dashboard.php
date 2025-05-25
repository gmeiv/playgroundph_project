<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

if (!isset($_SESSION['user'])) {
    header("Location: login_sign.php?mode=login");
    exit();
}

$db = mysqli_connect("localhost", "root", "", "u778263593_playgroundph");
if (!$db) {
    die("Database connection failed.");
}

$usernameFromSession = $_SESSION['user'];
$query = "SELECT username FROM users WHERE username = ?";
$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, "s", $usernameFromSession);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    $username = htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8');
} else {
    $username = "User";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>PLAYGROUNDPH</title>
  <link rel="stylesheet" href="dashboard.css"/>
</head>
<body>

  <header>
    <div class="hamburger" id="hamburgerBtn" aria-label="Open menu" tabindex="0">
      <div></div>
      <div></div>
      <div></div>
    </div>
    <h1>ELECTIVE</h1>
  </header>

  <aside id="sidePanel" aria-label="User settings panel">
    <div class="user-info">
      <div class="avatar" role="img" aria-label="User Avatar"></div>
      <div class="username-text" id="usernamePanel"><?= $username ?></div>
    </div>
    <button onclick="openAvatarPopup()">Change Avatar</button>
    <button onclick="location.href='change_password.php'">Change Password</button>
    <button onclick="location.href='change_username.php'">Change Name</button>
    <button onclick="location.href='delete_account.php'">Delete Account</button>
    <button onclick="location.href='logout.php'">Logout</button>


    <div id="avatarPopup" class="avatar-popup" hidden>
    <h3>Select Avatar</h3>
    <div class="avatar-options">
      <img src="avatars/avatar1.jpg" alt="Avatar 1" onclick="selectAvatar('avatar1.jpg')">
      <img src="avatars/avatar2.jpg" alt="Avatar 2" onclick="selectAvatar('avatar2.jpg')">
      <img src="avatars/avatar3.jpg" alt="Avatar 3" onclick="selectAvatar('avatar3.jpg')">
      <img src="avatars/avatar4.jpg" alt="Avatar 4" onclick="selectAvatar('avatar4.jpg')">

    </div>
    <button onclick="closeAvatarPopup()">Cancel</button>
  </div>


  </aside>

  <main>
  <div class="welcome-msg" id="usernameMain">Welcome, <?= $username ?>!</div>
  <div class="games-menu">
    <div class="game-card" onclick="location.href='TICTACTOE/home.html'" style="cursor:pointer;">
      <img src="../nodbchain/IMAGES_GIF/ttt.png" alt="Tic Tac Toe Preview" class="game-image">
      <h2>Tic Tac Toe</h2>
      <p>Play with X and O and get three in a row!</p>
    </div>
    <div class="game-card" onclick="location.href='CHAIN/home.php'" style="cursor:pointer;">
      <img src="../nodbchain/IMAGES_GIF/chain.png" alt="Chain Reaction Preview" class="game-image">
      <h2>Chain Reaction</h2>
      <p>Make the orbs explode to be a survivor!</p>
    </div>
  </div>
</main>


  <script>
    const username = "<?= addslashes($username) ?>";
    document.getElementById('usernamePanel').textContent = username;
    document.getElementById('usernameMain').textContent = "Welcome, " + username + "!";

    const avatarDiv = document.querySelector('.avatar');

    // Use default if none is selected
    const savedAvatar = localStorage.getItem('avatarImage') || 'avatars/avatar1.jpg';
    document.querySelectorAll('.avatar').forEach(avatar => {
    avatar.style.backgroundImage = `url(${savedAvatar})`;
    });


    function openAvatarPopup() {
      document.getElementById('avatarPopup').hidden = false;
    }

    function closeAvatarPopup() {
      document.getElementById('avatarPopup').hidden = true;
    }

    function selectAvatar(imageName) {
      const fullPath = `avatars/${imageName}`;
      localStorage.setItem('avatarImage', fullPath);

      document.querySelectorAll('.avatar').forEach(avatar => {
        avatar.style.backgroundImage = `url(${fullPath})`;
      });

      closeAvatarPopup();
    }


    const sidePanel = document.getElementById('sidePanel');
    const hamburgerBtn = document.getElementById('hamburgerBtn');

    hamburgerBtn.addEventListener('click', () => {
      const isOpen = sidePanel.classList.toggle('open');
      document.body.classList.toggle('panel-open', isOpen);
    });

    hamburgerBtn.addEventListener('keypress', (e) => {
      if (e.key === 'Enter' || e.key === ' ') {
        hamburgerBtn.click();
      }
    });

    function confirmDelete() {
      if (confirm('Are you sure you want to DELETE your account? This action cannot be undone.')) {
        location.href = 'delete_account.php';
      }
    }
  </script>
</body>
</html>
