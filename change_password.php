<?php
include 'user_var.php';
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login_sign.php?mode=login");
    exit();
}

$db = mysqli_connect("localhost", "u778263593_root", "PlaygroundPH00", "u778263593_playgroundph");
if (!$db) die("Connection failed.");

$successMessage = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_SESSION['user'];
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    $query = "SELECT password FROM users WHERE username='$username'";
    $result = mysqli_query($db, $query);
    $row = mysqli_fetch_assoc($result);

    if (password_verify($current, $row['password'])) {
        if ($new === $confirm) {
            $hashed = password_hash($new, PASSWORD_DEFAULT);
            mysqli_query($db, "UPDATE users SET password='$hashed' WHERE username='$username'");
            header("Location: change_password.php?msg=Password changed successfully");
            exit();
        } else {
            echo "<script>
                sessionStorage.setItem('passwordMismatch', 'true');
                window.location.href = 'change_password.php';
            </script>";
        }
    } else {
        echo "<script>
            sessionStorage.setItem('incorrectPassword', 'true');
            window.location.href = 'change_password.php';
        </script>";
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
  <h2>Change Password</h2>

  <?php if (isset($_GET['msg'])): ?>
    <div class="success"><?= htmlspecialchars($_GET['msg']) ?></div>
  <?php endif; ?>

  <form method="post" action="change_password.php" novalidate>
    <label for="current_password">Current Password</label>
    <input type="password" id="current_password" name="current_password" required autocomplete="current-password" />

    <label for="new_password">New Password</label>
    <input type="password" id="new_password" name="new_password" required autocomplete="new-password" />

    <label for="confirm_password">Confirm New Password</label>
    <input type="password" id="confirm_password" name="confirm_password" required autocomplete="new-password" />

    <div class="button-container">
      <button type="submit" class="magic-button" id="magicButton">Change Password</button>
    </div>
  </form>
</div>

<script src="login_sign.js"></script>

<script>
  window.addEventListener('DOMContentLoaded', () => {
    const currentField = document.getElementById('current_password');
    const newPass = document.getElementById('new_password');
    const confirmPass = document.getElementById('confirm_password');

    if (sessionStorage.getItem('incorrectPassword') === 'true') {
      currentField.classList.add('error');
      currentField.value = '';
      currentField.placeholder = "Incorrect password!";
      sessionStorage.removeItem('incorrectPassword');
    }

    if (sessionStorage.getItem('passwordMismatch') === 'true') {
      newPass.classList.add('error');
      confirmPass.classList.add('error');
      confirmPass.value = '';
      confirmPass.placeholder = "Passwords do not match";
      sessionStorage.removeItem('passwordMismatch');
    }
  });
</script>

</body>
</html>
