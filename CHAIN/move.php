<?php
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

require 'vendor/autoload.php';

// Initialize Pusher at the top
$pusher = new Pusher\Pusher(
    'f19facd60b851f60a0e3',
    'e5f6a7b8c9d0e1f2a3b4', // Your Pusher secret
    '1996214', // Your Pusher app_id
    ['cluster' => 'ap1', 'useTLS' => true]
);

$room = $_POST['room'] ?? $_GET['room'] ?? null;
$player = $_POST['player'] ?? null;
$row = isset($_POST['row']) ? intval($_POST['row']) : null;
$col = isset($_POST['col']) ? intval($_POST['col']) : null;
$action = $_POST['action'] ?? null;

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
            $state['scores'][$player] += 1; // +1 for placing
            $state['counts'][$row][$col]++;
            $state['owners'][$row][$col] = $player;
            $state['grid'][$row][$col] = str_repeat(getOrb($player), $state['counts'][$row][$col]);
            $limit = getLimit($row, $col);
            if ($state['counts'][$row][$col] >= $limit) {
                explodeOrb($state, $row, $col, $player);
            }
        }
        function explodeOrb(&$state, $row, $col, $player) {
            $state['scores'][$player] += 6; // +6 for explosion
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
        // In your POST move handling section, replace the Pusher trigger with:
        $pusher->trigger("room-$room", 'move-made', [
            'grid' => $state['grid'],
            'owners' => $state['owners'],
            'counts' => $state['counts'],
            'scores' => $state['scores'],
            'eliminated' => $state['eliminated'],
            'players' => $state['players'],
            'turn' => $state['turn'],
            'currentPlayer' => $state['players'][$state['turn'] % count($state['players'])],
            'winner' => isset($state['winner']) ? $state['winner'] : null
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

// Winner detection (after you set $state['winner'])
if (isset($state['winner'])) {
    // Prepare winner data to broadcast
    $winnerData = [
        'winner' => $state['winner'],
        'winnerName' => $state['players'][$state['winner']],
        'scores' => $state['scores'],
        'players' => $state['players']
    ];
    $pusher->trigger("room-$room", 'game-over', $winnerData);

    // Remove room from lobby (if you're using lobby.json)
    $lobbyFile = __DIR__ . '/lobby.json';
    if (file_exists($lobbyFile)) {
        $lobby = json_decode(file_get_contents($lobbyFile), true);
        if (isset($lobby['rooms'][$room])) {
            unset($lobby['rooms'][$room]);
            file_put_contents($lobbyFile, json_encode($lobby));
        }
    }
   
    // --- Save scores to database ---
   $db = new mysqli('localhost', 'u778263593_root', 'PlaygroundPH00', 'u778263593_playgroundph');
if ($db->connect_error) {
    file_put_contents('debug_db.log', "Connection failed: " . $db->connect_error . "\n", FILE_APPEND);
} else {
    foreach ($state['players'] as $i => $username) {
        $score = isset($state['scores'][$i]) ? intval($state['scores'][$i]) : 0;
        $isWinner = ($i == $state['winner']);

        $query = "INSERT INTO chain_scores (player, wins, losses, draws, total_score) VALUES (
            '" . $db->real_escape_string($username) . "',
            " . ($isWinner ? 1 : 0) . ",
            " . ($isWinner ? 0 : 1) . ",
            0,
            $score
        ) ON DUPLICATE KEY UPDATE 
            wins = wins + " . ($isWinner ? 1 : 0) . ",
            losses = losses + " . ($isWinner ? 0 : 1) . ",
            total_score = total_score + $score";

    }
    $db->close();
}
     register_shutdown_function(function() use ($stateFile) {
        sleep(5); // Give clients time to see the winner
        if (file_exists($stateFile)) unlink($stateFile);
    });

    header('Content-Type: application/json');
    echo json_encode(array_merge($state, ['gameOver' => true]));
    exit;
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

