const board = document.getElementById('board');
const status = document.getElementById('status');

let scoreSubmitted = false;

function getPlayerMark(player, state) {
    return state.players[0] === player ? "X" : "O";
}

function getGameId(state) {
    return room + '-' + state.board.join(''); // Unique-ish game signature
}

function hasScoreBeenSubmitted(gameId) {
    return localStorage.getItem('submitted-' + gameId) === '1';
}

function markScoreSubmitted(gameId) {
    localStorage.setItem('submitted-' + gameId, '1');
}

function renderBoard(state) {
    board.innerHTML = '';
    state.board.forEach((cell, i) => {
        const div = document.createElement('div');
        div.className = 'cell';
        div.textContent = cell;
        if (!cell && state.turn === player && !state.winner) {
            div.onclick = () => makeMove(i);
        }
        board.appendChild(div);
    });

    status.textContent = state.winner
        ? (state.winner === "draw" ? "It's a Draw!" : `Winner: ${state.winner}`)
        : (state.turn === player ? "Your Turn" : "Opponent's Turn");

    const gameId = getGameId(state);

    if (state.winner && !hasScoreBeenSubmitted(gameId)) {
        markScoreSubmitted(gameId);
        const myMark = getPlayerMark(player, state);

        if (state.winner === "draw") {
            submitScore(player, "draw");
        } else if (state.winner === myMark) {
            submitScore(player, "win");
        } else {
            submitScore(player, "loss");
        }
    }
}

function makeMove(index) {
    fetch('move.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ room, player, index })
    });
}
function restartGame() {
    fetch('restart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ room, player })
    });
}

function submitScore(player, result) {
    fetch('update_score.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `player=${encodeURIComponent(player)}&result=${encodeURIComponent(result)}`
    });
}

const pusher = new Pusher('f19facd60b851f60a0e3', { cluster: 'ap1' });
const channel = pusher.subscribe('room-' + room);
channel.bind('move-made', renderBoard);

// Initial fetch
fetch('rooms/' + room + '.json')
    .then(res => res.json())
    .then(renderBoard);
