<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login_sign.php?mode=login");
    exit();
}

$db = mysqli_connect("localhost", "root", "", "draw_game");
if (!$db) die("Connection failed.");

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
          echo "<script>
              localStorage.setItem('actionGraphic', 'username_changed');
              window.location.href = 'action.html?redirect=dashboard.php&msg=Username%20updated%20successfully!';
          </script>";
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
<title>Change Username</title>
<link rel="stylesheet" href="change.css">
<link rel="stylesheet" href="login_sign.css">
</head>
<body>

<div class="change-container">
  <h2>Change Username</h2>
  <form method="post" action="change_username.php">
    <label for="new_username">New Username</label>
    <input type="text" id="new_username" name="new_username" required minlength="3" maxlength="20" pattern="[A-Za-z0-9_]+" title="Only letters, numbers, and underscores allowed" />

     <div class="button-container">

          <button type="submit" class="magic-button" id="magicButton">Change Username</button>
        </div>
  </form>
</div>

<script>
  window.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('new_username');

    if (sessionStorage.getItem('usernameTaken') === 'true') {
      input.style.borderColor = 'red';
      input.value = '';
      input.placeholder = "Username already taken";
      input.classList.add('error');
      sessionStorage.removeItem('usernameTaken');
    }
  });
</script>


</body>
</html>
