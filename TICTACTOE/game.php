<?php
$room = $_GET['room'];
$player = $_GET['player'];
$file = "rooms/$room.json";

if (!file_exists($file)) {
    file_put_contents($file, json_encode([
        "players" => [$player],
        "board" => array_fill(0, 9, ""),
        "turn" => $player,
        "winner" => null
    ]));
} else {
    $data = json_decode(file_get_contents($file), true);
    if (!in_array($player, $data["players"]) && count($data["players"]) < 2) {
        $data["players"][] = $player;
        file_put_contents($file, json_encode($data));
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Tic Tac Toe - <?= htmlspecialchars($room) ?></title>
    <link rel="stylesheet" href="game.css">
    <script src="https://js.pusher.com/8.2/pusher.min.js"></script>
</head>
<body>
    <h2>Room: <?= htmlspecialchars($room) ?></h2>
    <h3>You are: <?= htmlspecialchars($player) ?></h3>
    <h4 id="status">Waiting for opponent...</h4>

    <div class="board" id="board"></div>
    <div class="controls">
        <button onclick="restartGame()">🔄 Restart</button>
        <button onclick="window.location.href='index.php'">🏠 Home</button>
    </div>
    <script>
        const player = "<?= $player ?>";
        const room = "<?= $room ?>";
    </script>
    <script src="game.js"></script>
</body>
</html>
