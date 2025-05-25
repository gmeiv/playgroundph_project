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
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;      /* Center all content horizontally */
            justify-content: flex-start; /* Or use center for vertical centering */
            background: url('../IMAGES_GIF/payrplay.gif') no-repeat center center fixed;
      background-size: cover;
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

        /* Winner Popup Styles */
.winner-popup {
    position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
    background: rgba(0,0,0,0.8); display: none; justify-content: center; align-items: center; z-index: 1000;
}
.winner-popup.active { display: flex; }
.winner-content {
    background: linear-gradient(135deg, #1a2a6c, #b21f1f, #fdbb2d);
    padding: 40px; border-radius: 16px; text-align: center; max-width: 500px; width: 90%;
    box-shadow: 0 10px 50px rgba(0,0,0,0.5); position: relative; overflow: hidden;
}
.winner-content h2 {
    font-size: 2.5em; margin-bottom: 20px; color: gold;
    text-shadow: 0 0 10px rgba(255,215,0,0.7);
}
.winner-content button {
    background: rgba(255,255,255,0.2); border: 2px solid white; color: white;
    padding: 12px 30px; font-size: 1.2em; border-radius: 50px; cursor: pointer;
    transition: all 0.3s ease; outline: none; margin: 10px;
}
.winner-content button:hover {
    background: rgba(255,255,255,0.4); transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
}
.trophy { font-size: 5em; margin-bottom: 20px; display: inline-block; animation: bounce 0.5s ease infinite alternate; }
@keyframes bounce { from { transform: translateY(0); } to { transform: translateY(-20px); } }
    </style>
</head>
<body>
    <h1>Room <?= htmlspecialchars($room) ?></h1>
    <form class="top-buttons">
        <button type="button" onclick="window.location.href='../dashboard.php'">Dashboard</button>
        <button type="button" onclick="window.location.href='homepage.php'">Lobby</button>
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

    <!-- Winner Popup -->
<div id="winnerPopup" class="winner-popup">
  <div class="winner-content">
    <div class="trophy"></div>
    <h2 id="winnerMessage"></h2>
    <button id="lobbyBtn">Back to Lobby</button>
  </div>
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

    function showWinnerPopup(message) {
        document.getElementById('winnerMessage').textContent = message;
        document.getElementById('winnerPopup').classList.add('active');
    }
    function hideWinnerPopup() {
        document.getElementById('winnerPopup').classList.remove('active');
    }
    document.getElementById('lobbyBtn').onclick = function() {
        fetch('end_room.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({room: room})
        }).then(() => {
            window.location.href = 'homepage.php';
        });
    };

    // Top "Lobby" button handler
document.querySelector('button[onclick*="homepage.php"]').onclick = function(e) {
    e.preventDefault();
    fetch('end_room.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams({room: room})
    }).then(() => {
        window.location.href = 'homepage.php';
    });
};

document.querySelector('button[onclick*="../dashboard.php"]').onclick = function(e) {
    e.preventDefault();
    fetch('end_room.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams({room: room})
    }).then(() => {
        window.location.href = '../dashboard.php';
    });
};
    
    // Pusher setup
    Pusher.logToConsole = false;
    const pusher = new Pusher('f19facd60b851f60a0e3', {cluster: 'ap1'});
    const channel = pusher.subscribe('room-' + room);
    channel.bind('move-made', function(data) {
        if (data.winner !== null) {
            // Determine if you win or lose
            const winnerName = data.players[data.winner];
            if (winnerName === player) {
                showWinnerPopup("You Win!");
            } else {
                showWinnerPopup("You Lose!");
            }
        } else {
            fetchState();
        }
    });
    channel.bind('game-restart', function() {
        hideWinnerPopup();
        setTimeout(fetchState, 500);
    });

    // Initial board
    fetchState();
    </script>
    
</body>
</html>