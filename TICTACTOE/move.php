<?php
require 'pusher_config.php';

$data = json_decode(file_get_contents('php://input'), true);
$room = $data['room'];
$player = $data['player'];
$index = $data['index'];

$file = "rooms/$room.json";
$state = json_decode(file_get_contents($file), true);

if ($state['turn'] !== $player || $state['board'][$index]) {
    exit;
}

$mark = $player === $state['players'][0] ? 'X' : 'O';
$state['board'][$index] = $mark;

// Check winner
function checkWinner($b) {
    $lines = [[0,1,2],[3,4,5],[6,7,8],[0,3,6],[1,4,7],[2,5,8],[0,4,8],[2,4,6]];
    foreach ($lines as [$a,$b1,$c]) {
        if ($b[$a] && $b[$a] === $b[$b1] && $b[$a] === $b[$c]) return $b[$a];
    }
    return in_array("", $b) ? null : "draw";
}


$winner = checkWinner($state['board']);
$state['winner'] = $winner;

if (!$winner) {
    $next = $state['players'][0] === $player ? $state['players'][1] : $state['players'][0];
    $state['turn'] = $next;
}

file_put_contents($file, json_encode($state));

$pusher->trigger('room-' . $room, 'move-made', $state);
