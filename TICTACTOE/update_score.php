<?php
$pdo = new PDO('mysql:host=localhost;dbname=ttt_scores', 'root', '');

$player = $_POST['player'] ?? '';
$result = $_POST['result'] ?? '';

if (!$player || !$result) {
    http_response_code(400);
    exit('Missing data');
}

// Sanitize player name
$player = trim($player);

// Fetch or create player
$stmt = $pdo->prepare("SELECT * FROM scores WHERE player = ?");
$stmt->execute([$player]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    // New player
    $stmt = $pdo->prepare("INSERT INTO scores (player, wins, losses, draws, streak) VALUES (?, 0, 0, 0, 0)");
    $stmt->execute([$player]);
    $row = ['wins' => 0, 'losses' => 0, 'draws' => 0, 'streak' => 0];
}

// Update stats
switch ($result) {
    case 'win':
        $row['wins']++;
        $row['streak'] = $row['streak'] >= 0 ? $row['streak'] + 1 : 1;
        break;
    case 'loss':
        $row['losses']++;
        $row['streak'] = $row['streak'] <= 0 ? $row['streak'] - 1 : -1;
        break;
    case 'draw':
        $row['draws']++;
        // streak stays the same
        break;
    default:
        http_response_code(400);
        exit('Invalid result');
}

// Save updates
$stmt = $pdo->prepare("UPDATE scores SET wins = ?, losses = ?, draws = ?, streak = ? WHERE player = ?");
$stmt->execute([$row['wins'], $row['losses'], $row['draws'], $row['streak'], $player]);

echo "Score updated";
?>
