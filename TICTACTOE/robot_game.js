// assets/robot_script.js
const boardElement = document.getElementById('board');
const status = document.getElementById('status');
let board = Array(9).fill("");
let gameOver = false;

function renderBoard() {
    boardElement.innerHTML = "";
    board.forEach((cell, index) => {
        const div = document.createElement('div');
        div.className = 'cell';
        div.textContent = cell;
        if (!cell && !gameOver) {
            div.onclick = () => playerMove(index);
        }
        boardElement.appendChild(div);
    });
}

function playerMove(index) {
    if (board[index] !== "" || gameOver) return;
    board[index] = "X";
    renderBoard();
    checkWinner();
    if (!gameOver) {
        setTimeout(robotMove, 400);
    }
}

function robotMove() {
    const emptyIndices = board.map((val, idx) => val === "" ? idx : null).filter(v => v !== null);
    if (emptyIndices.length === 0) return;

    const index = emptyIndices[Math.floor(Math.random() * emptyIndices.length)];
    board[index] = "O";
    renderBoard();
    checkWinner();
}

function checkWinner() {
    const wins = [
        [0,1,2], [3,4,5], [6,7,8],
        [0,3,6], [1,4,7], [2,5,8],
        [0,4,8], [2,4,6]
    ];

    for (const [a, b, c] of wins) {
        if (board[a] && board[a] === board[b] && board[a] === board[c]) {
            gameOver = true;
            status.textContent = board[a] === "X" ? "You Win!" : "Robot Wins!";
            return;
        }
    }

    if (!board.includes("")) {
        gameOver = true;
        status.textContent = "It's a Draw!";
    } else {
        status.textContent = board.filter(Boolean).length % 2 === 0 ? "Your Turn" : "Robot's Turn";
    }
}

function resetGame() {
    board = Array(9).fill("");
    gameOver = false;
    status.textContent = "Your Turn";
    renderBoard();
}

function submitScore(player, result) {
    console.log("Submitting score:", player, result); // ✅ Debug log
    fetch('update_score.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `player=${encodeURIComponent(player)}&result=${encodeURIComponent(result)}`
    })
    .then(response => response.text())
    .then(data => console.log("Server response:", data)) // ✅ Show result
    .catch(error => console.error("Error updating score:", error));
}


document.addEventListener("DOMContentLoaded", renderBoard);
