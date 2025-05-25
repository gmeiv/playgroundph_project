<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login_sign.php?mode=login");
    exit();
}

$username = $_SESSION['user'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $confirmUsername = $_POST['confirmUsername'] ?? '';

    if ($confirmUsername !== $username) {
        $error = "Username does not match. Account deletion cancelled.";
    } else {
        // Use your actual database credentials
        $db = mysqli_connect("localhost", "root", "", "u778263593_playgroundph");
        if (!$db) die("Connection failed.");

        $delete = "DELETE FROM users WHERE username='$username'";
        if (mysqli_query($db, $delete)) {
            session_destroy();
            echo "<script>
                localStorage.setItem('actionGraphic', 'account_deleted');
                window.location.href = 'action.html?redirect=login_sign.html&msg=Account%20deleted%20successfully';
            </script>";
            exit();
        } else {
            $error = "Error deleting account. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Delete Account Confirmation</title>
<link rel="stylesheet" href="login_sign.css">
<link rel="stylesheet" href="change.css">
<style>
  /* Basic styles for popup */
  body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: url("../projik/IMAGES_GIF/moonpixel.gif") no-repeat center center fixed;
    background-size: cover;
    margin: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    overflow: hidden;
  }

</style>
</head>
<body>

<div class="change-container" role="dialog" aria-modal="true" aria-labelledby="heading">
  <h2 id="heading">Confirm Account Deletion</h2>
  <p>Are you sure you want to delete your account?</p>
  <p>Please enter your username <strong>to confirm</strong>:</p>
  <form method="POST" onsubmit="return validateForm()">
    <input 
      type="text" 
      id="confirmUsername" 
      name="confirmUsername" 
      placeholder="Enter username" 
      required 
      autofocus
      <?= (!empty($error)) ? 'class="error"' : '' ?>
    />
    <button type="submit" onclick="window.location.href='login_sign.html'">Delete Account</button>
    <button type="button" onclick="window.location.href='dashboard.php'" style="background:#dc3545; margin-top:10px;">Cancel</button>
  </form>
  <?php if (!empty($error)): ?>
    <div id="errorMsg"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
</div>

<script>
function validateForm() {
  const input = document.getElementById('confirmUsername').value.trim();
  if (!input) {
    alert('Please enter your username to confirm.');
    return false;
  }
  return true; // submit form
}
</script>

</body>
</html>
