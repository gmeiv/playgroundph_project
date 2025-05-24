<?php

require 'vendor/autoload.php';

$room = $_POST['room'] ?? $_GET['room'] ?? null;
$player = $_POST['player'] ?? null;
$row = isset($_POST['row']) ? intval($_POST['row']) : null;
$col = isset($_POST['col']) ? intval($_POST['col']) : null;

$stateFile = "state_$room.json";
if (!file_exists($stateFile)) {
    http_response_code(404);
    exit('Room not found');
}   
$state = json_decode(file_get_contents($stateFile), true);

// --- After loading $state from file, ensure scores array exists ---
if (!isset($state['scores'])) {
    $state['scores'] = array_fill(0, count($state['players']), 0);
}

// Only process move if POST and valid player/turn
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $player !== null && $row !== null && $col !== null) {
    $players = $state['players'];
    $playerIdx = array_search($player, $players);
    if ($playerIdx === false) exit(json_encode(['error' => 'Invalid player']));
    $currentPlayerIdx = $state['turn'] % count($players);

    // Only allow move if it's this player's turn, and cell is empty or owned by them
    if (
        $playerIdx === $currentPlayerIdx &&
        ($state['owners'][$row][$col] == -1 || $state['owners'][$row][$col] == $playerIdx) &&
        !isset($state['winner']) && empty($state['eliminated'][$playerIdx])
    ) {
        // --- Chain Reaction Logic ---
        function getOrb($player) {
            $colors = ["🔴", "🟢", "🔵", "🟡"];
            return $colors[$player % 4];
        }
        function getLimit($row, $col) {
            $corners = [[0,0],[0,8],[5,0],[5,8]];
            $sides = array_merge(
                [[0,1],[0,2],[0,3],[0,4],[0,5],[0,6],[0,7]],
                [[1,0],[2,0],[3,0],[4,0]],
                [[5,1],[5,2],[5,3],[5,4],[5,5],[5,6],[5,7]],
                [[1,8],[2,8],[3,8],[4,8]]
            );
            if (in_array([$row, $col], $corners)) return 2;
            if (in_array([$row, $col], $sides)) return 3;
            return 4;
        }
        function addOrb(&$state, $row, $col, $player) {
            $state['counts'][$row][$col]++;
            $state['owners'][$row][$col] = $player;
            $state['grid'][$row][$col] = str_repeat(getOrb($player), $state['counts'][$row][$col]);
            $limit = getLimit($row, $col);
            if ($state['counts'][$row][$col] >= $limit) {
                explodeOrb($state, $row, $col, $player);
            }
        }
        function explodeOrb(&$state, $row, $col, $player) {
            $dirs = [[-1,0],[1,0],[0,-1],[0,1]];
            $state['counts'][$row][$col] = 0;
            $state['owners'][$row][$col] = -1;
            $state['grid'][$row][$col] = "";
            foreach ($dirs as [$dr, $dc]) {
                $nr = $row + $dr;
                $nc = $col + $dc;
                if ($nr >= 0 && $nr < 6 && $nc >= 0 && $nc < 9) {
                    // INVASION: Always set owner to current player!
                    addOrb($state, $nr, $nc, $player);
                }
            }
          
        }
        // --- End Chain Reaction Logic ---

        addOrb($state, $row, $col, $playerIdx);

        // --- Recalculate scores for each player ---
        foreach ($state['scores'] as $i => $_) {
            $state['scores'][$i] = 0;
        }
        foreach ($state['owners'] as $r) {
            foreach ($r as $owner) {
                if ($owner !== -1) $state['scores'][$owner]++;
            }
        }

        // Recalculate active players
        $activePlayers = [];
        $orbPresence = array_fill(0, count($players), 0);
        foreach ($state['owners'] as $r) {
            foreach ($r as $owner) {
                if ($owner !== -1) $orbPresence[$owner]++;
            }
        }
        foreach ($orbPresence as $p => $count) {
            if ($count === 0 && $state['turn'] >= count($players)) {
                $state['eliminated'][$p] = true;
            } elseif ($count > 0) {
                $activePlayers[$p] = true;
            }
        }

        // Next turn: skip eliminated
        do {
            $state['turn']++;
            $nextIdx = $state['turn'] % count($players);
        } while (!empty($state['eliminated'][$nextIdx]) && count($activePlayers) > 1);

        // Winner
        if (count($activePlayers) === 1 && $state['turn'] >= count($players)) {
            $state['winner'] = array_keys($activePlayers)[0];
        }

        file_put_contents($stateFile, json_encode($state));

        // Pusher
        $pusher = new Pusher\Pusher('f19facd60b851f60a0e3', '595ce39f6c7ee69924c5', '1996214', [
            'cluster' => 'ap1',
            'useTLS' => true
        ]);
        $pusher->trigger("room-$room", 'move-made', [
            'board' => $state['grid'],
            'next' => $players[$state['turn'] % count($players)],
            'winner' => isset($state['winner']) ? $players[$state['winner']] : null,
            'scores' => $state['scores'],
            'eliminated' => $state['eliminated'],
            'players' => $players
        ]);
        echo json_encode([
            'success' => true,
            'scores' => $state['scores'],
            'eliminated' => $state['eliminated'],
            'players' => $players
        ]);
        exit;
    }
}

// GET: return state as JSON
header('Content-Type: application/json');
echo json_encode([
    'grid' => $state['grid'],
    'owners' => $state['owners'],
    'scores' => $state['scores'],
    'eliminated' => $state['eliminated'],
    'players' => $state['players'],
    'turn' => $state['turn'],
    'winner' => isset($state['winner']) ? $state['winner'] : null
]);

// Winner detection (after you set $state['winner'])
if (isset($state['winner'])) {
    // Notify clients of game over
    $pusher->trigger("room-$room", 'move-made', [
        'board' => $state['grid'],
        'next' => null,
        'winner' => $state['players'][$state['winner']]
    ]);

    // Wait a few seconds to let the winner be displayed
    sleep(2);

    // Remove the room from the lobby.json
    $lobbyFile = __DIR__ . '/lobby.json';
    if (file_exists($lobbyFile)) {
        $lobby = json_decode(file_get_contents($lobbyFile), true);
        if (isset($lobby['rooms'][$room])) {
            unset($lobby['rooms'][$room]);
            file_put_contents($lobbyFile, json_encode($lobby));
        }
    }

    // Optionally, delete the state file for this room
    if (file_exists($stateFile)) {
        unlink($stateFile);
    }
    exit;
}