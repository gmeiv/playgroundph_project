<?php
require 'pusher_config.php';

session_start();

// Redirect if not logged in
if (!isset($_SESSION["user"])) {
    header("Location: login_sign.html");
    exit;
}

$username = $_SESSION["user"];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tic Tac Toe - Lobby</title>
    <link href="https://fonts.googleapis.com/css2?family=Rubik&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="index.css">
    <style>
         .magic_button {
            position: relative;
            padding: 12px 20px;
            font-size: 18px;
            border: 1px solid #555;
            border-radius: 6px;
            cursor: pointer;
            overflow: hidden;
            color: lightgray;
            background-color: rgba(80, 120, 255, 0.2);
            transition: box-shadow 0.4s ease, transform 0.3s ease;
            z-index: 1;
            width: 180px;
            text-decoration: none;
            text-align: center;
        }

        .magic_button::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(45deg, #2c187d, #6f6abe, #007bff);
            border-radius: 6px;
            filter: blur(10px);
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
            z-index: -1;
        }

        .magic_button:hover::before {
            opacity: 1;
        }

        .magic_button:hover {
            box-shadow: 0 0 20px #00f0ff;
        }

        .back-button {
            position: absolute;
            top: 20px;
            left: 20px;
            font-size: 18px;
            padding: 10px 16px;
            border-radius: 6px;
            border: 1px solid #555;
            background-color: rgba(80, 120, 255, 0.2);
            color: lightgray;
            text-decoration: none;
            transition: box-shadow 0.4s ease;
            z-index: 100;
        }

        .back-button::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(45deg, #2c187d, #6f6abe, #007bff);
            border-radius: 6px;
            filter: blur(10px);
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
            z-index: -1;
        }

        .back-button:hover::before {
            opacity: 1;
        }

        .back-button:hover {
            box-shadow: 0 0 15px #00f0ff;
        }

        .star {
            position: absolute;
            color: cyan;
            font-size: 16px;
            animation: sparkle 1s ease-out forwards;
            pointer-events: none;
        }

        @keyframes sparkle {
            0% {
                transform: translateY(0) scale(1);
                opacity: 1;
            }
            100% {
                transform: translateY(-40px) scale(0.5);
                opacity: 0;
            }
        }
    </style>
</head>
<body>
<!-- offline_game.php -->
<div class="container">
    <h1>Vs Robot</h1>
    <p>Play against a computer opponent</p>
    <form action="robot_game.php" method="get">
        <input type="text" name="player" placeholder="Your name" value="<?= htmlspecialchars($username) ?>" required>
        <button class="magicbutton" type="submit">Play vs Robot</button>
    </form>


</div>
</body>
</html>
