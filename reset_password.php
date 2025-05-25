<?php
session_start();
$db = new mysqli("localhost", "u778263593_root", "PlaygroundPH00", "u778263593_playgroundph");

$msg = "";
$showForm = false;

// Check if token is provided
if (empty($_GET['token'])) {
    $msg = "Invalid password reset link.";
} else {
    $token = $_GET['token'];

    // Validate token & check expiry
    $stmt = $db->prepare("SELECT username, token_expiry FROM users WHERE reset_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_assoc()) {
        $expiry = strtotime($row['token_expiry']);
        $now = time();

        if ($now > $expiry) {
            $msg = "This password reset link has expired.";
        } else {
            $showForm = true;
            $username = $row['username'];
        }
    } else {
        $msg = "Invalid password reset link.";
    }
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['token'])) {
    $token = $_POST['token'];
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if ($password !== $password_confirm) {
        $msg = "Passwords do not match.";
        $showForm = true;
    } elseif (strlen($password) < 6) {
        $msg = "Password must be at least 6 characters.";
        $showForm = true;
    } else {
        // Verify token again before update
        $stmt = $db->prepare("SELECT username, token_expiry FROM users WHERE reset_token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($row = $res->fetch_assoc()) {
            $expiry = strtotime($row['token_expiry']);
            if (time() > $expiry) {
                $msg = "This password reset link has expired.";
            } else {
                // Update password (hash it)
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $update = $db->prepare("UPDATE users SET password=?, reset_token=NULL, token_expiry=NULL WHERE reset_token=?");
                $update->bind_param("ss", $hashed_password, $token);
                $update->execute();

                if ($update->affected_rows > 0) {
                    $msg = "Password reset successful. You can now <a href='login_sign.html'>login</a>.";
                    $showForm = false;
                } else {
                    $msg = "Failed to reset password. Please try again.";
                    $showForm = true;
                }
            }
        } else {
            $msg = "Invalid password reset link.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Reset Password</title>
<style>
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

  .change-container {
    background: rgba(20, 20, 60, 0.6);
    padding: 40px;
    border-radius: 10px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    width: 100%;
    max-width: 500px;
    box-sizing: border-box;
    animation: fadeIn 0.4s ease-in-out;
    color: white;
  }

  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
  }

  h2 {
    text-align: center;
    margin-bottom: 30px;
    color: white;
  }

  label {
    display: block;
    margin-bottom: 6px;
    font-weight: 600;
    color: white;
  }

  input {
    width: 100%;
    padding: 12px 14px;
    margin-bottom: 20px;
    border: 1.5px solid #ccc;
    border-radius: 6px;
    font-size: 15px;
    transition: border-color 0.3s;
    box-sizing: border-box;
  }

  input:focus {
    border-color: #007bff;
    outline: none;
  }

  .error {
    border-color: #dc3545 !important;
    background: #fff5f5;
  }

  .error::placeholder {
    color: #dc3545;
    font-weight: 500;
  }

  button {
    width: 100%;
    padding: 12px;
    font-size: 16px;
    font-weight: 600;
    background: #007bff;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: background 0.3s;
    box-sizing: border-box;
  }

  button:hover {
    background: #0056b3;
  }

  .message {
    text-align: center;
    font-weight: 600;
    margin-bottom: 20px;
    font-size: 15px;
  }

  .message.success {
    color: #00f0ff;
    text-shadow: 0 0 6px #00f0ff, 0 0 12px #00f0ff;
  }

  .message.success a {
    color: #00f0ff;
    font-weight: bold;
    text-decoration: underline;
  }

  .message.error {
    color: #dc3545;
  }
</style>
</head>
<body>
<div class="change-container">
  <h2>Reset Password</h2>
  <?php if ($msg): ?>
    <div class="message <?= strpos($msg, 'success') !== false ? 'success' : 'error' ?>">
      <?= $msg ?>
    </div>
  <?php endif; ?>

  <?php if ($showForm): ?>
  <form method="POST">
    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>" />
    <label for="password">New Password:</label>
    <input type="password" name="password" id="password" required placeholder="Enter new password" />
    <label for="password_confirm">Confirm Password:</label>
    <input type="password" name="password_confirm" id="password_confirm" required placeholder="Confirm new password" />
    <button type="submit">Reset Password</button>
  </form>
  <?php endif; ?>
</div>
</body>
</html>
