<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

if (!isset($_SESSION['user'])) {
    header("Location: login_sign.php?mode=login");
    exit();
}

// Updated database credentials
$db = mysqli_connect("localhost", "u778263593_root", "PlaygroundPH00", "u778263593_playgroundph");
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <div class="button-container" style="position: absolute; top: 18px; left: 18px;">
        <form action="dashboard.php" method="get" style="margin:0;">
            <button type="submit" class="magic-button" style="padding:10px 18px; font-size:1.1em;">
                ← Back
            </button>
        </form>
    </div>
</body>
</html>