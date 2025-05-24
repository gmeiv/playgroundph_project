<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="style.css">

    
</head>

<body>
    
</body>
</html>

<?php

session_start();
require 'vendor/autoload.php';

$room = $_GET['room'] ?? $_SESSION['room'] ?? null;
$player = $_SESSION['player'] ?? null;
if (!$room || !$player) {
    header("Location: homepage.php");
    exit();
}

$stateFile = "state_$room.json";

// Initialize game state if not exists
if (!file_exists($stateFile)) {
    $players = [ $player ];
    $state = [
        'players' => $players,
        'turn' => 0,
        'grid' => array_fill(0, 6, array_fill(0, 9, "")),
        'counts' => array_fill(0, 6, array_fill(0, 9, 0)),
        'owners' => array_fill(0, 6, array_fill(0, 9, -1)),
        'scores' => array_fill(0, 4, 0),
        'eliminated' => array_fill(0, 4, false),
        'winner' => null
    ];
    file_put_contents($stateFile, json_encode($state));
} else {
    $state = json_decode(file_get_contents($stateFile), true);
    // Add player if not present
    if (!in_array($player, $state['players']) && count($state['players']) < 4) {
        $state['players'][] = $player;
        file_put_contents($stateFile, json_encode($state));
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Chain Reaction - Room <?= htmlspecialchars($room) ?></title>
    <script src="https://js.pusher.com/8.4.0/pusher.min.js"></script>
    <style>
        body {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    align-items: center;      /* Center all content horizontally */
    justify-content: flex-start; /* Or use center for vertical centering */
    background: radial-gradient(circle at center, #081028 0%, #061024 100%);
    color: #eaf6ff;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    text-align: center;
    margin: 0;
    padding: 0;
}

h1 {
    font-size: 2.5em;
    margin-top: 20px;
    color: #7ecbff;
    text-shadow: 0 0 12px #0056b3, 0 0 4px #00e1ff;
    letter-spacing: 2px;
}

h2 {
    font-size: 1.5em;
    margin: 10px;
    color: #b3e0ff;
    text-shadow: 0 2px 8px #003366;
}

.emoji {
    font-size: 2em;
    line-height: 1;
    display: inline-block;
}

/* Do not touch .cell, .row, .board, or any cell-related styles */
        .board {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    margin: 20px auto 0 auto;
}
        .row {
    display: flex;
    justify-content: center;
}
        .cell { 
            width: 70px; 
            height: 70px; 
            margin: 4px; 
            border-radius: 12px; 
            border: 2px solid #333; 
            background: #222; 
            cursor: pointer; 
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            transition: border 0.2s;
        }
        /* Highlight border for current turn */
        .cell.red-turn {
            border: 3px solid #ff4444 !important;
        }
        /* Cell background and border for each player turn */
        body.player-0 .cell { background-color: rgba(255,0,0,0.15); border: 3px solid #ff4444; }
        body.player-1 .cell { background-color: rgba(0,255,0,0.15); border: 3px solid #44ff44; }
        body.player-2 .cell { background-color: rgba(0,0,255,0.15); border: 3px solid #4488ff; }
        body.player-3 .cell { background-color: rgba(255,255,0,0.15); border: 3px solid #ffe066; }

        /* Keep owned cells background #222, but border stays colored for turn */
        .cell[data-owner="-1"] { background: inherit; }
        .cell[data-owner="0"],
        .cell[data-owner="1"],
        .cell[data-owner="2"],
        .cell[data-owner="3"] {
            background: #222;
        }
        .cell.winner { background: gold !important; color: #000; }
        .orb-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            grid-template-rows: repeat(2, 1fr);
            width: 90%;
            height: 90%;
            justify-items: center;
            align-items: center;
        }
    </style>
</head>
<body>
    <h1>Room <?= htmlspecialchars($room) ?></h1>
    <form class="top-buttons">
        <button type="button" onclick="window.location.href='../dashboard.php'">🏠 Dashboard</button>
        <button type="button" onclick="window.location.href='homepage.php'">🏷️ Lobby</button>
        <button type="button" onclick="restartGame()">🔁 Restart</button>
    </form>
    <div style="display: flex; flex-direction: column; align-items: center; gap: 18px;">
        <h2 id="turn"></h2>
        <div class="scoreboard" id="scoreboard" style="min-width:180px;">
            <h3>Scoreboard</h3>
            <ul id="score-list"></ul>
        </div>
        <div class="board" id="board"></div>
        <h2 id="winner"></h2>
    </div>
    <script>
    const player = <?= json_encode($player) ?>;
    const room = <?= json_encode($room) ?>;
    let state = null;

    function fetchState() {
        fetch('move.php?room=' + room)
            .then(res => res.json())
            .then(updateBoard);
    }

    function updateBoard(data) {
        state = data;
        const boardDiv = document.getElementById('board');
        boardDiv.innerHTML = '';
        // --- Scoreboard update ---
        const scoreList = document.getElementById('score-list');
        scoreList.innerHTML = '';
        if (data.players && data.scores) {
            data.players.forEach((p, i) => {
                const li = document.createElement('li');
                li.textContent = `Player ${i+1}: ${p} — Score: ${data.scores[i] ?? 0}`;
                if (data.eliminated && data.eliminated[i]) li.className = 'eliminated';
                scoreList.appendChild(li);
            });
        }
        // --- Board update ---
        for (let r = 0; r < 6; r++) {
            const rowDiv = document.createElement('div');
            rowDiv.className = 'row';
            for (let c = 0; c < 9; c++) {
                const cell = document.createElement('button');
                cell.className = 'cell';
                cell.dataset.row = r;
                cell.dataset.col = c;
                cell.dataset.owner = data.owners[r][c];
                cell.innerHTML = data.grid[r][c] || '';
                if (data.winner !== null) cell.classList.add('winner');
                cell.disabled = data.winner !== null || (data.owners[r][c] !== -1 && data.owners[r][c] !== data.turn % data.players.length) || data.players[data.turn % data.players.length] !== player;
                cell.onclick = () => makeMove(r, c);
                rowDiv.appendChild(cell);
            }
            boardDiv.appendChild(rowDiv);
        }
        document.getElementById('turn').textContent = data.winner !== null
            ? `Winner: ${data.players[data.winner]}`
            : `Turn: ${data.players[data.turn % data.players.length]}`;
        document.getElementById('winner').textContent = data.winner !== null ? "Game Over!" : "";

        const currentTurnIdx = data.turn % data.players.length;
        document.body.classList.remove('player-0', 'player-1', 'player-2', 'player-3');
        document.body.classList.add('player-' + currentTurnIdx);
    }

    function makeMove(row, col) {
        fetch('move.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({room, player, row, col})
        });
    }

    // Pusher setup
    Pusher.logToConsole = false;
    const pusher = new Pusher('f19facd60b851f60a0e3', {cluster: 'ap1'});
    const channel = pusher.subscribe('room-' + room);
    channel.bind('move-made', function(data) {
        if (data.winner) {
            alert('Winner: ' + data.winner + '!');
            window.location.href = 'homepage.php';
        } else {
            fetchState();
        }
    });
    channel.bind('game-restart', function() {
        setTimeout(fetchState, 500);
    });

    // Initial board
    fetchState();
    </script>
</body>
</html>