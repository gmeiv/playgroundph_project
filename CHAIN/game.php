// game.php
<?php
session_start();

$saveFile = 'save_game.json';

function getOrb($player) {
    $colors = ["🔴", "🟢", "🔵", "🟡"];
    return $colors[$player];
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

function addOrb($row, $col, $player) {
    $grid = &$_SESSION['grid'];
    $owners = &$_SESSION['owners'];
    $counts = &$_SESSION['counts'];
    $scores = &$_SESSION['scores'];

    $counts[$row][$col]++;
    $owners[$row][$col] = $player;
    $grid[$row][$col] = str_repeat(getOrb($player), $counts[$row][$col]);

    $scores[$player]++;

    $limit = getLimit($row, $col);
    if ($counts[$row][$col] >= $limit) {
        explodeOrb($row, $col, $player);
    }
}

function explodeOrb($row, $col, $player) {
    $dirs = [[-1,0],[1,0],[0,-1],[0,1]];
    $_SESSION['counts'][$row][$col] = 0;
    $_SESSION['owners'][$row][$col] = -1;
    $_SESSION['grid'][$row][$col] = "";

    $_SESSION['exploding'][] = [$row, $col];
    $_SESSION['scores'][$player]++;

    foreach ($dirs as [$dr, $dc]) {
        $nr = $row + $dr;
        $nc = $col + $dc;
        if ($nr >= 0 && $nr < 6 && $nc >= 0 && $nc < 9) {
            addOrb($nr, $nc, $player);
        }
    }
}

if (isset($_POST['players'])) {
    $_SESSION['players'] = intval($_POST['players']);
    $_SESSION['turn'] = 0;
    $_SESSION['grid'] = array_fill(0, 6, array_fill(0, 9, ""));
    $_SESSION['counts'] = array_fill(0, 6, array_fill(0, 9, 0));
    $_SESSION['owners'] = array_fill(0, 6, array_fill(0, 9, -1));
    $_SESSION['scores'] = array_fill(0, $_SESSION['players'], 0);
    $_SESSION['eliminated'] = array_fill(0, $_SESSION['players'], false);
    header("Location: index.php");
    exit();
}

if (isset($_POST['save'])) {
    $saveData = [
        'players' => $_SESSION['players'],
        'turn' => $_SESSION['turn'],
        'grid' => $_SESSION['grid'],
        'counts' => $_SESSION['counts'],
        'owners' => $_SESSION['owners'],
        'scores' => $_SESSION['scores'],
        'eliminated' => $_SESSION['eliminated']
    ];
    file_put_contents($saveFile, json_encode($saveData));
    header("Location: index.php");
    exit();
}

if (isset($_POST['load']) && file_exists($saveFile)) {
    $saveData = json_decode(file_get_contents($saveFile), true);
    $_SESSION['players'] = $saveData['players'];
    $_SESSION['turn'] = $saveData['turn'];
    $_SESSION['grid'] = $saveData['grid'];
    $_SESSION['counts'] = $saveData['counts'];
    $_SESSION['owners'] = $saveData['owners'];
    $_SESSION['scores'] = $saveData['scores'] ?? array_fill(0, $_SESSION['players'], 0);
    $_SESSION['eliminated'] = $saveData['eliminated'] ?? array_fill(0, $_SESSION['players'], false);
    header("Location: index.php");
    exit();
}

if (isset($_POST['reset'])) {
    // Reset all necessary game session variables
    $_SESSION['turn'] = 0;
    $_SESSION['winner'] = null;
    $_SESSION['exploding'] = [];
    $_SESSION['owners'] = array_fill(0, 6, array_fill(0, 9, -1));
    $_SESSION['counts'] = array_fill(0, 6, array_fill(0, 9, 0));
    $_SESSION['grid'] = array_fill(0, 6, array_fill(0, 9, ''));
    $_SESSION['scores'] = array_fill(0, $_SESSION['players'], 0);
    $_SESSION['eliminated'] = array_fill(0, $_SESSION['players'], false);
    unset($_SESSION['show_winner']);

    // Redirect to game.php again to avoid form resubmission
    header("Location: index.php");
    exit();
}

if (isset($_POST['restart'])) {
    $players = $_SESSION['players'];
    $_SESSION['turn'] = 0;
    $_SESSION['grid'] = array_fill(0, 6, array_fill(0, 9, ""));
    $_SESSION['counts'] = array_fill(0, 6, array_fill(0, 9, 0));
    $_SESSION['owners'] = array_fill(0, 6, array_fill(0, 9, -1));
    $_SESSION['scores'] = array_fill(0, $players, 0);
    $_SESSION['eliminated'] = array_fill(0, $players, false);
    unset($_SESSION['winner'], $_SESSION['show_winner']);
    header("Location: game_online.php");
    exit();
}


if (isset($_POST['row']) && isset($_POST['col'])) {
    $row = $_POST['row'];
    $col = $_POST['col'];
    $player = $_SESSION['turn'] % $_SESSION['players'];

    if (!isset($_SESSION['winner']) && !$_SESSION['eliminated'][$player]) {
        if ($_SESSION['owners'][$row][$col] == -1 || $_SESSION['owners'][$row][$col] == $player) {
            addOrb($row, $col, $player);

            // Recalculate active players
            $activePlayers = [];
            $orbPresence = array_fill(0, $_SESSION['players'], 0);

            foreach ($_SESSION['owners'] as $r) {
                foreach ($r as $owner) {
                    if ($owner !== -1) {
                        $orbPresence[$owner]++;
                    }
                }
            }

            foreach ($orbPresence as $p => $count) {
                if ($count === 0 && $_SESSION['turn'] >= $_SESSION['players']) {
                    $_SESSION['eliminated'][$p] = true;
                } elseif ($count > 0) {
                    $activePlayers[$p] = true;
                }
            }

            do {
                $_SESSION['turn']++;
                $player = $_SESSION['turn'] % $_SESSION['players'];
            } while ($_SESSION['eliminated'][$player] && count($activePlayers) > 1);

            if (count($activePlayers) === 1 && $_SESSION['turn'] >= $_SESSION['players']) {
                $_SESSION['winner'] = array_keys($activePlayers)[0];
            }
        }
    }
}

if (isset($_SESSION['winner'])) {
    // Store winner and scores for alert
    $_SESSION['show_winner'] = [
        'winner' => $_SESSION['winner'],
        'scores' => $_SESSION['scores']
    ];
    header("Location: index.php");
    exit();
}

header("Location: index.php");
exit();
