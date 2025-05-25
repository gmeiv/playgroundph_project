<?php
include '../db_naming.php';

$lobbyFile = 'lobby.json';

// Load or initialize lobby
if (!file_exists($lobbyFile)) file_put_contents($lobbyFile, '{"rooms":{}}');
$lobby = json_decode(file_get_contents($lobbyFile), true);

// --- Auto-remove finished rooms ---
foreach ($lobby['rooms'] as $roomId => $room) {
    // Remove room if status is 'finished' or if no players are left
    if (
        (isset($room['status']) && $room['status'] === 'finished') ||
        (empty($room['players']) || (is_array($room['players']) && count($room['players']) === 0))
    ) {
        unset($lobby['rooms'][$roomId]);
    }
}
file_put_contents($lobbyFile, json_encode($lobby));

// Handle room creation
if (isset($_POST['create_room'])) {
    $room = uniqid();
    $player = trim($_POST['player']);
    $lobby['rooms'][$room] = [
        'players' => [$player],
        'status' => 'waiting',
        'created' => date('c')
    ];
    file_put_contents($lobbyFile, json_encode($lobby));
    $_SESSION['room'] = $room;
    $_SESSION['player'] = $player;
    header("Location: game_online.php?room=$room");
    exit();
}

// Handle joining a room
if (isset($_POST['join_room'])) {
    $room = $_POST['room'];
    $player = trim($_POST['player']);
    if (isset($lobby['rooms'][$room]) && !in_array($player, $lobby['rooms'][$room]['players'])) {
        $lobby['rooms'][$room]['players'][] = $player;
        $lobby['rooms'][$room]['status'] = 'started';
        file_put_contents($lobbyFile, json_encode($lobby));
        $_SESSION['room'] = $room;
        $_SESSION['player'] = $player;
        header("Location: game_online.php?room=$room");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Chain Reaction - Lobby</title>
    <link href="https://fonts.googleapis.com/css2?family=Rubik&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: url('../IMAGES_GIF/payrplay.gif') no-repeat center center fixed;
            background-size: cover;
            color: #eaf6ff;
            text-align: center;
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }
        h1 {
            font-size: 2.8em;
            color: #00eaff;
            text-shadow: 0 0 10px #0077ff, 0 0 20px #0033ff;
            margin-top: 50px;
            margin-bottom: 40px;
            letter-spacing: 2px;
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
        .lobby-wrapper {
            display: flex;
            justify-content: center;
            gap: 30px;
            flex-wrap: wrap;
            margin-top: 30px;
            max-width: 900px;
            margin-left: auto;
            margin-right: auto;
        }
        .lobby-section {
            width: 380px;
            background: rgba(20, 40, 90, 0.65);
            border-radius: 14px;
            padding: 32px 22px;
            box-shadow: 0 8px 32px 0 rgba(0, 40, 80, 0.25);
            backdrop-filter: blur(4px);
            border: 1.5px solid rgba(120, 180, 255, 0.15);
            transition: transform 0.3s ease;
        }
        .lobby-section h2 {
            color: #b3e0ff;
            margin-bottom: 22px;
            text-shadow: 0 2px 8px #003366;
        }
        .lobby-section input, .lobby-section select {
            padding: 12px;
            margin: 12px 0;
            border-radius: 8px;
            border: 1.5px solid #4fa3ff;
            width: 80%;
            font-size: 1em;
            background: rgba(80, 120, 255, 0.18);
            color: #eaf6ff;
            transition: border-color 0.3s, background 0.3s;
        }
        .lobby-section input:focus, .lobby-section select:focus {
            border-color: #00e1ff;
            background: rgba(80, 120, 255, 0.28);
            outline: none;
        }
        .lobby-section button, .magicbutton {
            background: linear-gradient(90deg, #007bff 60%, #00e1ff 100%);
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-size: 1.1em;
            cursor: pointer;
            margin-top: 10px;
            transition: background 0.3s, box-shadow 0.3s;
            box-shadow: 0 2px 8px rgba(0, 120, 255, 0.15);
            font-weight: 600;
        }
        .lobby-section button:hover, .magicbutton:hover {
            background: linear-gradient(90deg, #0056b3 60%, #00bfff 100%);
            box-shadow: 0 0 18px #00e1ff;
        }
        .active-rooms {
            margin: 30px auto 0;
            width: 800px;
            background: rgba(15, 15, 40, 0.65);
            border-radius: 14px;
            padding: 25px;
            box-shadow: 0 8px 32px 0 rgba(0, 40, 80, 0.25);
            backdrop-filter: blur(4px);
            border: 1.5px solid rgba(120, 180, 255, 0.15);
            max-height: 500px;
            overflow-y: auto;
        }
        .active-rooms h2 {
            color: #b3e0ff;
            margin-bottom: 20px;
            text-align: center;
            text-shadow: 0 2px 8px #003366;
        }
        .rooms-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 15px;
        }
        .room-card {
            background: rgba(0, 60, 120, 0.22);
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #00e1ff;
            transition: all 0.3s ease;
        }
        .room-card:hover {
            background: rgba(0, 80, 160, 0.3);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 225, 255, 0.2);
        }
        .room-name {
            color: #00e1ff;
            margin-bottom: 10px;
            font-size: 1.1em;
        }
        .players-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .player-tag {
            background: rgba(0, 150, 255, 0.15);
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.9em;
        }
        .no-rooms {
            text-align: center;
            color: #7ecbff;
            padding: 20px;
            grid-column: 1 / -1;
        }
        .leaderboard-link {
            margin-top: 40px;
        }
        #leaderboard-link {
            display: inline-block;
            background: linear-gradient(45deg, #00f0ff, #0080ff);
            color: white;
            border: none;
            padding: 14px 36px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: bold;
            font-size: 1.2em;
            transition: all 0.3s ease;
            margin: 0 10px;
            box-shadow: 0 2px 8px rgba(0, 120, 255, 0.15);
        }
        #leaderboard-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 240, 255, 0.4);
            background: linear-gradient(45deg, #00e1ff, #0056b3);
        }
        @media (max-width: 900px) {
            .active-rooms, .lobby-section { width: 98vw; }
            .rooms-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <a href="home.php" class="back-button">← Back</a>
    <h1>Chain Reaction Online Lobby</h1>
    <div class="lobby-wrapper">
        <div class="lobby-section">
            <h2>Create a Room</h2>
            <form method="post">
              <input name="player" placeholder="Your name" value="<?= htmlspecialchars($username) ?>" readonly required>
              <button type="submit" name="create_room">Create Room</button>
            </form>
        </div>
        <div class="lobby-section">
            <h2>Join a Room</h2>
            <form method="post">
              <input name="player" placeholder="Your name" value="<?= htmlspecialchars($username) ?>" readonly required>
              <select name="room" required>
                <option value="">Select Room</option>
                <?php foreach ($lobby['rooms'] as $roomId => $room): ?>
                  <option value="<?= htmlspecialchars($roomId) ?>">
                    <?= htmlspecialchars($roomId) ?> (<?= implode(', ', $room['players']) ?>)
                  </option>
                <?php endforeach; ?>
              </select>
              <button type="submit" name="join_room">Join Room</button>
            </form>
        </div>
    </div>
    <div class="active-rooms">
        <h2>Active Rooms</h2>
        <div class="rooms-grid">
          <?php foreach ($lobby['rooms'] as $roomId => $room): ?>
            <div class="room-card">
              <div class="room-name">Room <b><?= htmlspecialchars($roomId) ?></b></div>
              <div class="players-list">
                <?php foreach ($room['players'] as $p): ?>
                  <div class="player-tag"><?= htmlspecialchars($p) ?></div>
                <?php endforeach; ?>
              </div>
              <div class="room-status"><?= htmlspecialchars($room['status']) ?></div>
            </div>
          <?php endforeach; ?>
          <?php if (empty($lobby['rooms'])): ?>
            <div class="no-rooms">No active rooms. Create one above!</div>
          <?php endif; ?>
        </div>
    </div>
    <div class="leaderboard-link">
        <a href="leaderboard.php" id="leaderboard-link">🏆 View Leaderboards</a>
    </div>
</body>
</html>