// DOM Elements
const board = document.getElementById('board');
const status = document.getElementById('status');
const winnerPopup = document.createElement('div');
const winnerMessage = document.createElement('h2');
const playAgainBtn = document.createElement('button');
const lobbyBtn = document.createElement('button');

// Game State
let scoreSubmitted = false;
let gameActive = true;

// Initialize Winner Popup (add this once)
function initWinnerPopup() {
    winnerPopup.id = 'winnerPopup';
    winnerPopup.className = 'winner-popup';
    
    const winnerContent = document.createElement('div');
    winnerContent.className = 'winner-content';
    
    const trophy = document.createElement('div');
    trophy.className = 'trophy';
    trophy.textContent = '';
    
    winnerMessage.textContent = 'Congratulations!';
    
    playAgainBtn.textContent = 'Play Again';
    playAgainBtn.onclick = restartGame;
    
    lobbyBtn.textContent = 'Back to Lobby';
    lobbyBtn.onclick = () => window.location.href = 'online_game.php';
    
    winnerContent.appendChild(trophy);
    winnerContent.appendChild(winnerMessage);
    winnerContent.appendChild(playAgainBtn);
    winnerContent.appendChild(lobbyBtn);
    winnerPopup.appendChild(winnerContent);
    
    document.body.appendChild(winnerPopup);
    
    // Add minimal styles
    const style = document.createElement('style');
    style.textContent = `
        .winner-popup {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .winner-popup.active {
            display: flex;
        }

        .winner-content {
            background: linear-gradient(135deg, #1a2a6c, #b21f1f, #fdbb2d);
            padding: 40px;
            border-radius: 16px;
            text-align: center;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 10px 50px rgba(0, 0, 0, 0.5);
            position: relative;
            overflow: hidden;
        }

        .winner-content::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(
                to bottom right,
                rgba(255, 255, 255, 0.1) 0%,
                rgba(255, 255, 255, 0) 50%,
                rgba(255, 255, 255, 0.1) 100%
            );
            transform: rotate(30deg);
            animation: shine 3s infinite linear;
        }

        @keyframes shine {
            0% { transform: translateX(-100%) rotate(30deg); }
            100% { transform: translateX(100%) rotate(30deg); }
        }

        .winner-content h2 {
            font-size: 2.5em;
            margin-bottom: 20px;
            color: gold;
            text-shadow: 0 0 10px rgba(255, 215, 0, 0.7);
        }

        .winner-content p {
            font-size: 1.1em;
            margin-bottom: 10px;
            color: white;
        }

        .winner-content button {
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid white;
            color: white;
            padding: 12px 30px;
            font-size: 1.2em;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s ease;
            outline: none;
            margin: 10px;
        }

        .winner-content button:hover {
            background: rgba(255, 255, 255, 0.4);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        .trophy {
            font-size: 5em;
            margin-bottom: 20px;
            display: inline-block;
            animation: bounce 0.5s ease infinite alternate;
        }

        @keyframes bounce {
            from { transform: translateY(0); }
            to { transform: translateY(-20px); }
        }
    `;
    document.head.appendChild(style);
}

// Confetti Effect
function createConfetti() {
    const colors = ['#ff0000', '#00ff00', '#0000ff', '#ffff00', '#ff00ff', '#00ffff'];
    
    for (let i = 0; i < 50; i++) { // Reduced number for better performance
        const confetti = document.createElement('div');
        confetti.className = 'confetti';
        confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
        confetti.style.left = Math.random() * 100 + 'vw';
        confetti.style.top = '-10px';
        confetti.style.width = Math.random() * 8 + 4 + 'px';
        confetti.style.height = Math.random() * 8 + 4 + 'px';
        document.body.appendChild(confetti);
        
        const duration = Math.random() * 3 + 2;
        const animation = confetti.animate([
            { top: '-10px', opacity: 0 },
            { top: '10%', opacity: 1 },
            { top: '100vh', opacity: 0 }
        ], { duration: duration * 1000 });
        
        animation.onfinish = () => confetti.remove();
    }
}

// Modified renderBoard to handle winner display
function renderBoard(state) {
    board.innerHTML = '';
    state.board.forEach((cell, i) => {
        const div = document.createElement('div');
        div.className = 'cell';
        div.textContent = cell;
        if (!cell && state.turn === player && !state.winner && gameActive) {
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
        const result = state.winner === "draw" ? "draw" : 
                      state.winner === myMark ? "win" : "loss";
        submitScore(player, result);
        
        // Show winner popup
        gameActive = false;
        showWinnerPopup(state.winner === "draw" ? "It's a draw!" : 
                       state.winner === myMark ? "You won!" : "You lost!");
    }
}

function showWinnerPopup(message) {
    winnerMessage.textContent = message;
    winnerPopup.style.display = 'flex';
    createConfetti();
}

// Modified restartGame to hide popup
function restartGame() {
    fetch('restart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ room, player })
    }).then(() => {
        winnerPopup.style.display = 'none';
        gameActive = true;
        // Clear existing confetti
        document.querySelectorAll('.confetti').forEach(c => c.remove());
    });
}

// Initialize the popup when the script loads
initWinnerPopup();

// Pusher channel with game-over handling
const pusher = new Pusher('f19facd60b851f60a0e3', { cluster: 'ap1' });
const channel = pusher.subscribe('room-' + room);
channel.bind('move-made', renderBoard);
channel.bind('game-restart', () => {
    winnerPopup.style.display = 'none';
    gameActive = true;
});

// Initial fetch
fetch('rooms/' + room + '.json')
    .then(res => res.json())
    .then(renderBoard)
    .catch(err => console.error('Error loading game:', err));

// Keep all your existing utility functions:
function getPlayerMark(player, state) {
    return state.players[0] === player ? "X" : "O";
}

function getGameId(state) {
    return room + '-' + state.board.join('');
}

function hasScoreBeenSubmitted(gameId) {
    return localStorage.getItem('submitted-' + gameId) === '1';
}

function markScoreSubmitted(gameId) {
    localStorage.setItem('submitted-' + gameId, '1');
}

function makeMove(index) {
    if (!gameActive) return;
    fetch('move.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ room, player, index })
    });
}

function submitScore(player, result) {
    fetch('update_score.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `player=${encodeURIComponent(player)}&result=${encodeURIComponent(result)}`
    });
}