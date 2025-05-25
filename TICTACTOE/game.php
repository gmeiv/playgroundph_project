<?php
require 'pusher_config.php';

// Utility: Show error page and stop execution
function showError($message, $redirect) {
    echo <<<HTML
    <!DOCTYPE html>
    <html>
    <head>
        <title>Error</title>
        <link rel="stylesheet" href="game.css">
        <style>
            body {
                margin: 0;
                padding: 0;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                background: url('../IMAGES_GIF/payrplay.gif') no-repeat center center/cover;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                color: #eee;
            }
            .popup {
                background: rgba(0, 0, 0, 0.6);
                border: 2px solid #00f0ff;
                padding: 30px 40px;
                border-radius: 15px;
                box-shadow: 0 0 30px #00f0ff;
                text-align: center;
                max-width: 400px;
                backdrop-filter: blur(8px);
            }
            .popup h2 {
                color: #ff4d4d;
                margin-bottom: 15px;
                font-size: 28px;
                text-shadow: 0 0 5px #ff0000;
            }
            .popup p {
                font-size: 16px;
                margin-bottom: 25px;
            }
            .popup a {
                padding: 12px 25px;
                border-radius: 8px;
                background: #007bff;
                color: white;
                text-decoration: none;
                font-weight: bold;
                box-shadow: 0 0 10px #007bff;
                transition: background 0.3s, box-shadow 0.3s;
            }
            .popup a:hover {
                background: #0056b3;
                box-shadow: 0 0 15px #00aaff;
            }
        </style>
    </head>
    <body>
        <div class="popup">
            <h2>Error</h2>
            <p>$message</p>
            <a href="$redirect">Go Back</a>
        </div>
    </body>
    </html>
    HTML;
    exit;
}

// Validate query parameters
$room = $_GET['room'] ?? null;
$player = $_GET['player'] ?? null;
$action = $_GET['action'] ?? null;

if (!$room || !$player || !$action) {
    showError("Missing room, player, or action.", "home.html");
}

$roomsDir = "rooms";
$file = "$roomsDir/$room.json";

if (!file_exists($roomsDir)) {
    mkdir($roomsDir, 0777, true);
}

if ($action === "create") {
    if (file_exists($file)) {
        $existing = json_decode(file_get_contents($file), true);
        if (!empty($existing['players'])) {
            showError("Room already exists and is in use.", "online_game.php");
        } else {
            unlink($file); // Reset broken room
        }
    }

    $newRoom = [
        "players" => [$player],
        "board" => array_fill(0, 9, ""),
        "turn" => $player,
        "winner" => null
    ];
    file_put_contents($file, json_encode($newRoom));

    $pusher->trigger('lobby', 'room-updated', [
        'room' => $room,
        'players' => $newRoom['players'],
        'status' => 'waiting'
    ]);

} elseif ($action === "join") {
    if (!file_exists($file)) {
        showError("Room not found.", "online_game.php");
    }

    $roomData = json_decode(file_get_contents($file), true);

    if (!in_array($player, $roomData['players']) && count($roomData['players']) < 2) {
        $roomData['players'][] = $player;
        file_put_contents($file, json_encode($roomData));

        $status = count($roomData['players']) === 2 ? 'full' : 'waiting';
        $pusher->trigger('lobby', 'room-updated', [
            'room' => $room,
            'players' => $roomData['players'],
            'status' => $status
        ]);

    } elseif (!in_array($player, $roomData['players'])) {
        showError("Room full or invalid player.", "online_game.php");
    }

} else {
    showError("Invalid action.", "home.html");
}
?>

<!-- ✅ Game Page -->
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
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
        <button onclick="restartGame()">Restart</button>
        <button onclick="leaveRoom()">Leave Room</button>
        <button onclick="window.location.href='home.html'">Home</button>
    </div>
    

    <script>
        const player = <?= json_encode($player) ?>;
        const room = <?= json_encode($room) ?>;

        function leaveRoom() {
            fetch("leave.php?room=" + room + "&player=" + player)
                .then(() => window.location.href = "online_game.php")
                .catch(err => {
                    alert("Failed to leave room. Please try again.");
                    console.error(err);
                });
        }

        window.addEventListener("beforeunload", function () {
            navigator.sendBeacon("leave.php?room=" + room + "&player=" + player);
        });
    </script>
    <script src="game.js"></script>
</body>
</html>
