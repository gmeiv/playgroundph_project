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
