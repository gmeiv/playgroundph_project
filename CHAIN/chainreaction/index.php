<?php session_start(); ?>
<!DOCTYPE html>
<html>
<head>
    <title>Chain Reaction - Home</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background: radial-gradient(circle at center, #111 0%, #000 100%);
            color: white;
            text-align: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 40px;
        }

        h1 {
            font-size: 3em;
            margin-bottom: 10px;
            color: #FFD700;
            text-shadow: 0 0 4px #ffa500;
        }

        h3 {
            color: #FF6347;
        }

        .description {
            max-width: 800px;
            margin: 0 auto 30px auto;
            font-size: 1.2em;
            line-height: 1.6;
        }

        .btn-container {
            margin-top: 30px;
        }

        .home-button {
            background-color: #333;
            color: white;
            padding: 15px 30px;
            margin: 15px;
            border: none;
            border-radius: 10px;
            font-size: 1.2em;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .home-button:hover {
            background-color: #555;
            transform: scale(1.05);
        }

        .emoji {
            font-size: 1.5em;
        }
    </style>
</head>
<body>
    <h1>Chain Reaction</h1>

    <div class="description">
        <h3>What is Chain Reaction?</h3>
        <p>
            <span class="emoji">💥</span> Chain Reaction is a strategic, turn-based multiplayer game. 
            Players take turns placing orbs in cells. Each cell has a limit, and when exceeded, it causes an explosion, spreading orbs to neighboring cells — potentially triggering more explosions!
        </p>

        <h3>How to Play</h3>
        <p>
            <span class="emoji">🧠</span> You and your friends take turns placing colored orbs in a 6×9 grid.  
            A cell can hold only a certain number of orbs before it explodes.  
            When it does, it sends orbs to neighboring cells — capturing them if they were previously owned by another player.
        </p>
        <p>
            <span class="emoji">🏆</span> The goal? Be the last player standing by eliminating your opponents through chain reactions!
        </p>
    </div>

    <div class="btn-container">
        <a href="index.php" class="home-button">🏠 Home</a>
        <a href="index.html" class="home-button">🌐 Online Play with Friends</a>
        <a href="game.php?offline=1" class="home-button">👥 Offline Play with Friends</a>
    </div>
</body>
</html>
