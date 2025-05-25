<?php
include 'user_var.php';
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login_sign.php?mode=login");
    exit();
}

$db = mysqli_connect("localhost", "root", "", "u778263593_playgroundph");
if (!$db) die("Connection failed.");

$successMessage = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $current = $_SESSION['user'];
    $new = $_POST['new_username'];

    $check = mysqli_query($db, "SELECT * FROM users WHERE username='$new'");
    if (mysqli_num_rows($check) > 0) {
        echo "<script>
            sessionStorage.setItem('usernameTaken', 'true');
            window.location.href = 'change_username.php';
        </script>";
    } else {
        $update = "UPDATE users SET username='$new' WHERE username='$current'";
        if (mysqli_query($db, $update)) {
            $_SESSION['user'] = $new;
            header("Location: change_username.php?msg=Username updated successfully!");
            exit();
        } else {
            echo "<script>alert('Error updating username.'); history.back();</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>PLAYGROUNDPH</title>
<link rel="stylesheet" href="login_sign.css">
<link rel="stylesheet" href="change.css">
<style>
  body {
    background: url('IMAGES_GIF/moonpixel.gif') no-repeat center center fixed;
    background-size: cover;
  }
  .success {
    color: #00ff99;
    background: rgba(0, 0, 0, 0.6);
    padding: 10px;
    margin-bottom: 15px;
    border-radius: 5px;
    text-align: center;
  }
  .error {
    border: 2px solid red;
  }
</style>
</head>
<body>

<div class="change-container">
  <h2>Change Username</h2>

  <?php if (isset($_GET['msg'])): ?>
    <div class="success"><?= htmlspecialchars($_GET['msg']) ?></div>
  <?php endif; ?>

  <form method="post" action="change_username.php">
    <label for="new_username">New Username</label>
    <input type="text" id="new_username" name="new_username" required minlength="3" maxlength="20"
           pattern="[A-Za-z0-9_]+" title="Only letters, numbers, and underscores allowed" />

    <div class="button-container">
      <button type="submit" class="magic-button" id="magicButton">Change Username</button>
    </div>
  </form>
</div>

<script>
  window.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('new_username');

    if (sessionStorage.getItem('usernameTaken') === 'true') {
      input.classList.add('error');
      input.value = '';
      input.placeholder = "Username already taken";
      sessionStorage.removeItem('usernameTaken');
    }
  });
</script>

</body>
</html>
