<?php
session_start();
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
    header("Location: game.php?room=$room");
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
        header("Location: game.php?room=$room");
        exit();
    }
}

//$_SESSION['user'] = $row['username'];
$username = isset($_SESSION['user']) ? $_SESSION['user'] : '';
if (!$username) {
    // Redirect to login if not logged in
    header("Location: ../login_sign.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Chain Reaction Online - Lobby</title>
</head>
<body>
    <h1>Chain Reaction Online</h1>
    <form method="post">
        <input type="hidden" name="player" value="<?= htmlspecialchars($username) ?>">
        <div>Player: <b><?= htmlspecialchars($username) ?></b></div>
        <button type="submit" name="create_room">Create Room</button>
    </form>
    <h2>Or Join a Room</h2>
    <form method="post">
        <input type="hidden" name="player" value="<?= htmlspecialchars($username) ?>">
        <div>Player: <b><?= htmlspecialchars($username) ?></b></div>
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
</body>
</html>