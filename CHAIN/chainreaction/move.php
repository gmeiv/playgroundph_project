<?php
$room = $_POST['room'];
$user = $_POST['user'];
$i = intval($_POST['i']);
$j = intval($_POST['j']);

$stateFile = "state_$room.json";
$state = json_decode(file_get_contents($stateFile), true);
$currentPlayer = $state['players'][$state['turn']];

if ($state['game_over']) exit;

if ($user !== $currentPlayer) exit;

// Validate cell
$cell = $state['board'][$i][$j];
if ($cell !== null && $cell['player'] !== $user) exit;

$state['board'][$i][$j] = $cell === null
    ? ['count' => 1, 'player' => $user]
    : ['count' => $cell['count'] + 1, 'player' => $user];

// Chain reaction
function explodeCells(&$board, $players) {
    $rows = count($board);
    $cols = count($board[0]);
    $changed = false;

    for ($i = 0; $i < $rows; $i++) {
        for ($j = 0; $j < $cols; $j++) {
            $cell = $board[$i][$j];
            if ($cell && $cell['count'] >= 4) {
                $board[$i][$j] = null;
                foreach ([[-1,0],[1,0],[0,-1],[0,1]] as [$di, $dj]) {
                    $ni = $i + $di;
                    $nj = $j + $dj;
                    if ($ni >= 0 && $ni < $rows && $nj >= 0 && $nj < $cols) {
                        $neighbor = $board[$ni][$nj];
                        if ($neighbor === null) {
                            $board[$ni][$nj] = ['count' => 1, 'player' => $cell['player']];
                        } else {
                            $board[$ni][$nj]['count'] += 1;
                            $board[$ni][$nj]['player'] = $cell['player'];
                        }
                        $changed = true;
                    }
                }
            }
        }
    }
    return $changed;
}

while (explodeCells($state['board'], $state['players'])) {}

// Elimination
$alive = [];
foreach ($state['board'] as $row) {
    foreach ($row as $cell) {
        if ($cell) {
            $alive[$cell['player']] = true;
        }
    }
}
if (count($alive) === 1 && count($state['players']) > 1) {
    $state['winner'] = array_keys($alive)[0];
    $state['game_over'] = true;
}

if (!$state['game_over']) {
    $state['turn'] = ($state['turn'] + 1) % count($state['players']);
}

file_put_contents($stateFile, json_encode($state));

// PUSHER
require 'vendor/autoload.php';
$pusher = new Pusher\Pusher('f19facd60b851f60a0e3', '595ce39f6c7ee69924c5', '1996214', ['cluster' => 'ap1']);
$pusher->trigger("room-$room", 'move', [
    'board' => $state['board'],
    'message' => $state['game_over'] ? "Game Over. Winner: {$state['winner']}" : "Next Turn: " . $state['players'][$state['turn']]
]);
