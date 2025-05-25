<?php

include 'navabar.php';
session_start();

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
header("Pragma: no-cache");

// Only redirect to dashboard if this is a GET request and the user is logged in
if ($_SERVER["REQUEST_METHOD"] !== "POST" && isset($_SESSION["user"])) {
    if (!isset($_GET["redirect"])) {
        header("Location: dashboard.php");
        exit;
    }
}

// Database connection
$dbhost = "localhost";
$dbuser = "u778263593_root";
$dbpass = "PlaygroundPH00";
$dbname = "u778263593_playgroundph";

$conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Handle POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];
    $action = $_POST["action"];

    // During registration
    if ($action == "register") {
        $email = trim($_POST["email"]);
        $confirm = $_POST["confirm_password"];

        if ($password !== $confirm) {
            header("Location: login_sign.html?mode=register&error=nomatch");
            exit;
        }

        $check_query = "SELECT * FROM users WHERE username=? OR email=?";
        $stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($stmt, "ss", $username, $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {
            header("Location: login_sign.html?mode=register&error=exists");
            exit;
        }

        // Register new user
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $insert_query = "INSERT INTO users (username, password, email) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param($stmt, "sss", $username, $hashed, $email);

        if (mysqli_stmt_execute($stmt)) {
            echo "<script>
                localStorage.setItem('actionGraphic', 'registered');
                window.location.href = 'action.html?redirect=login_sign.html';
            </script>";
            exit;
        } else {
            header("Location: login_sign.html?mode=register&error=fail");
            exit;
        }
    }

    // During login
    if ($action == "login") {
        $query = "SELECT * FROM users WHERE username=?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            if (password_verify($password, $row["password"])) {
                $_SESSION["user"] = $row["username"];
                header("Location: dashboard.php");
                exit;
            } else {
                header("Location: login_sign.html?mode=login&error=wrongpass");
                exit;
            }
        } else {
            header("Location: login_sign.html?mode=login&error=nouser");
            exit;
        }
    }
}

mysqli_close($conn);
?>
