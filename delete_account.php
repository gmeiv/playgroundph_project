<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login_sign.php?mode=login");
    exit();
}

$db = mysqli_connect("localhost", "root", "", "draw_game");
if (!$db) die("Connection failed.");

$username = $_SESSION['user'];

$delete = "DELETE FROM users WHERE username='$username'";
if (mysqli_query($db, $delete)) {
    session_destroy();
    echo "<script>
        localStorage.setItem('actionGraphic', 'account_deleted');
        window.location.href = 'action.html?redirect=login_sign.html&msg=Account%20deleted%20successfully';
    </script>";
} else {
    echo "<script>alert('Error deleting account.'); window.location.href='dashboard.php';</script>";
}
?>
