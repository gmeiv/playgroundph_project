<?php
$room = $_GET['room'] ?? null;
$player = $_GET['player'] ?? null;

if (!$room || !$player) {
    http_response_code(400);
    exit("Missing room or player.");
}

$file = "rooms/$room.json";

if (!file_exists($file)) {
    exit("Room does not exist.");
}

$data = json_decode(file_get_contents($file), true);
$data['players'] = array_filter($data['players'], fn($p) => $p !== $player);

if (empty($data['players'])) {
    unlink($file);
} else {
    file_put_contents($file, json_encode($data));
}

echo "Left room.";
?>
