<?php
include '../user_var.php';

$lobbyFile = 'lobby.json';


// Load or initialize lobby
if (!file_exists($lobbyFile)) file_put_contents($lobbyFile, '{"rooms":{}}');
$lobby = json_decode(file_get_contents($lobbyFile), true);

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
  <meta charset="UTF-8" />
  <title>Chain Reaction Online - Lobby</title>
  <style>
    body {
      background: radial-gradient(circle at center, #081028 0%, #061024 100%);
      color: #eaf6ff;
      text-align: center;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    h1 {
      font-size: 3em;
      margin-bottom: 10px;
      color: #7ecbff;
      text-shadow: 0 0 12px #0056b3, 0 0 4px #00e1ff;
      letter-spacing: 2px;
    }

    .lobby-section, .room-list {
      margin: 30px auto 0 auto;
      max-width: 400px;
      background: rgba(20, 40, 90, 0.65);
      border-radius: 14px;
      padding: 32px 22px;
      box-shadow: 0 8px 32px 0 rgba(0, 40, 80, 0.25);
      backdrop-filter: blur(4px);
      -webkit-backdrop-filter: blur(4px);
      border: 1.5px solid rgba(120, 180, 255, 0.15);
    }

    .lobby-section h2, .room-list h3 {
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

    .lobby-section select {
      color: #0a183c; /* dark blue font */
      background: rgba(80, 120, 255, 0.18);
      font-weight: 600;
    }
    .lobby-section select option {
      color: #0a183c; /* dark blue font for options */
      background: #eaf6ff;
      font-weight: 600;
    }

    .lobby-section input:focus, .lobby-section select:focus {
      border-color: #00e1ff;
      background: rgba(80, 120, 255, 0.28);
      outline: none;
    }

    .lobby-section button {
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

    .lobby-section button:hover {
      background: linear-gradient(90deg, #0056b3 60%, #00bfff 100%);
      box-shadow: 0 0 18px #00e1ff;
    }

    .room-list {
      margin-top: 28px;
      text-align: left;
    }

    .room-list ul {
      list-style: none;
      padding: 0;
    }

    .room-list li {
      background: rgba(0, 60, 120, 0.22);
      margin: 10px 0;
      padding: 12px 18px;
      border-radius: 8px;
      color: #7ecbff;
      font-size: 1.13em;
      box-shadow: 0 1px 4px rgba(0,0,0,0.07);
      border-left: 4px solid #00e1ff;
    }
  </style>
</head>
<body>
  <h1>Chain Reaction Online Lobby</h1>
  <div class="lobby-section">
    <h2>Create a Room</h2>
    <!-- Create a Room -->
<form method="post">
  <input name="player" placeholder="Your name" value="<?= htmlspecialchars($username) ?>" readonly required>
  <button type="submit" name="create_room">Create Room</button>
</form>

  </div>
  <div class="lobby-section">
    <h2>Join a Room</h2>
    <!-- Join a Room -->
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
  <div class="room-list">
    <h3>Active Rooms</h3>
    <ul>
      <?php foreach ($lobby['rooms'] as $roomId => $room): ?>
        <li>
          Room <b><?= htmlspecialchars($roomId) ?></b>:
          <?= htmlspecialchars(implode(', ', $room['players'])) ?>
          (<?= htmlspecialchars($room['status']) ?>)
        </li>
      <?php endforeach; ?>
      <?php if (empty($lobby['rooms'])): ?>
        <li>No active rooms. Create one above!</li>
      <?php endif; ?>
    </ul>
  </div>
</body>
</html>