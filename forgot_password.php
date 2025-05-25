<?php
require 'vendor/autoload.php'; 
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();
$db = new mysqli("localhost", "u778263593_root", "PlaygroundPH00", "u778263593_playgroundph");

$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);

    $query = "SELECT * FROM users WHERE username=?";
    $stmt = $db->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_assoc()) {
        if (!filter_var($row["email"], FILTER_VALIDATE_EMAIL)) {
            $msg = "Invalid email address in database.";
        } else {
            $token = bin2hex(random_bytes(32));
            $expiry = date("Y-m-d H:i:s", time() + 3600); 

            $update = $db->prepare("UPDATE users SET reset_token=?, token_expiry=? WHERE username=?");
            $update->bind_param("sss", $token, $expiry, $username);
            $update->execute();

            // Email setup
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'gabclstn13@gmail.com';    
                $mail->Password = 'pqjd rdyi hkzs jzcz';     
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;


                $mail->setFrom('support@playgroundph.com', 'Playgroundph Game Support');
                $mail->addAddress($row["email"], $username);

                $mail->isHTML(true);
                $mail->Subject = 'Reset your password';

                $baseUrl = ($_SERVER['HTTP_HOST'] === 'localhost')
                    ? "http://localhost/projik"
                    : "https://playgroundph.com";

                $link = "$baseUrl/reset_password.php?token=$token";

                $mail->Body = "
                <html>
                <head>
                    <style>
                        body { 
                        font-family: Arial, sans-serif; 
                        background-color: #f4f4f4; 
                        padding: 20px; 
                        }
                        .email-content { 
                        background: #fff; 
                        padding: 30px; 
                        border-radius: 10px; 
                        max-width: 600px; 
                        margin: auto; 
                        box-shadow: 0 2px 8px rgba(0,0,0,0.1); 
                        }
                        .btn { 
                        background-color: #007bff;
                        color: #ffffff; 
                        padding: 12px 20px; 
                        border-radius: 5px; 
                        text-decoration: none; 
                        display: inline-block; 
                        }
                        .footer { 
                        margin-top: 30px; 
                        font-size: 12px; 
                        color: #777; 
                        }
                    </style>
                </head>
                <body>
                    <div class='email-content'>
                        <h2>Password Reset Request</h2>
                        <p>Hi <strong>$username</strong>,</p>
                        <p>We received a request to reset your password. Click the button below to proceed. This link will expire in 1 hour.</p>
                        <p style='text-align: center;'><a href='$link' class='btn'>Reset Password</a></p>
                        <p>If you did not request this, you can safely ignore this email.</p>
                        <div class='footer'>
                            <p>Need help? Contact us at support@playgroundph.com</p>
                            <p>&copy; " . date('Y') . " playgroundPH. All rights reserved.</p>
                        </div>
                    </div>
                </body>
                </html>";
                $mail->send();
                $msg = "Reset link sent to your email.";
            } catch (Exception $e) {
                $msg = "Email failed to send. Mailer Error: {$mail->ErrorInfo}";
            }
        }
    } else {
        $msg = "Username not found.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Forgot Password</title>
  <link rel="stylesheet" href="forgot_password.css" />
</head>
<body>
<div class="form-container">
  <h2>Forgot Password</h2>
  <form method="POST">
    <label for="username">Enter your username:</label>
    <input type="text" id="username" name="username" required placeholder="Your username" />
    <button type="submit">Send Reset Link</button>
  </form>

  <?php if (!empty($msg)): ?>
  <div class="message <?= strpos($msg, 'sent') !== false ? 'success' : 'error' ?>">
    <?= htmlspecialchars($msg) ?>
  </div>

  <?php if (strpos($msg, 'sent') !== false): ?>
    <script>
      setTimeout(() => {
        window.close();
      }, 10000);
    </script>
  <?php endif; ?>
<?php endif; ?>
</div>
</body>
</html>
