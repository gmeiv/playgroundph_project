<?php
$player = htmlspecialchars($_GET['player'] ?? 'Player');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tic Tac Toe - Vs Robot</title>
    <link href="https://fonts.googleapis.com/css2?family=Segoe+UI&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="game.css">
</head>
<body>
    <h2>Welcome, <?= $player ?></h2>
    <h3>You are playing against the Robot (O)</h3>
    <h4>You are X. Start playing!</h4>

    <div id="status">Your Turn</div>
    <div class="board" id="board"></div>

    <div class="controls">
        <button onclick="resetGame()">Restart</button>
        <a href="index.php"><button>Home</button></a>
    </div>


    <script src="robot_game.js"></script>
</body>
</html>
