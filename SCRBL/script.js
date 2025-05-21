const canvas = document.getElementById('drawArea');
const ctx = canvas.getContext('2d');
let drawing = false;
let currentColor = document.getElementById('colorPicker').value;
let currentSize = parseInt(document.getElementById('penSize').value);
let lastX = 0;
let lastY = 0;

let currentWord = '';
let score = 0;

// Drawing events
canvas.addEventListener('mousedown', (e) => {
  drawing = true;
  [lastX, lastY] = [e.offsetX, e.offsetY];
});
canvas.addEventListener('mouseup', () => drawing = false);
canvas.addEventListener('mouseout', () => drawing = false);
canvas.addEventListener('mousemove', (e) => {
  if (!drawing) return;
  ctx.strokeStyle = currentColor;
  ctx.lineWidth = currentSize;
  ctx.lineCap = 'round';
  ctx.beginPath();
  ctx.moveTo(lastX, lastY);
  ctx.lineTo(e.offsetX, e.offsetY);
  ctx.stroke();
  [lastX, lastY] = [e.offsetX, e.offsetY];
});

// Tools
document.getElementById('colorPicker').addEventListener('input', (e) => {
  currentColor = e.target.value;
});
document.getElementById('penSize').addEventListener('change', (e) => {
  currentSize = parseInt(e.target.value);
});
function useEraser() {
  currentColor = '#ffffff';
}
function clearCanvas() {
  ctx.clearRect(0, 0, canvas.width, canvas.height);
}

// ✅ Updated fetchWord function
function fetchWord() {
  fetch('game.php?action=get_word')
    .then(res => res.json())
    .then(words => {
      if (words.error) {
        console.error("Word fetch error:", words.error);
        document.getElementById('currentWordText').innerText = "Error fetching word";
        return;
      }

      if (!Array.isArray(words) || words.length === 0) {
        document.getElementById('currentWordText').innerText = "No words found";
        return;
      }

      currentWord = words[Math.floor(Math.random() * words.length)];
      document.getElementById('currentWordText').innerText = currentWord;
      document.getElementById('guess').value = '';
      document.getElementById('guess').style.backgroundColor = '';
    })
    .catch(err => {
      console.error("Fetch failed:", err);
      document.getElementById('currentWordText').innerText = "Fetch error";
    });
}

// Initial load
fetchWord();

// Guess submission
function submitGuess() {
  const guessInput = document.getElementById('guess');
  const inputValue = guessInput.value.trim().toLowerCase();

  if (inputValue === currentWord.toLowerCase()) {
    guessInput.style.backgroundColor = '#c8f7c5'; // Green
    score += 10; // ✅ Add 10 points
    document.getElementById('scoreDisplay').innerText = `Score: ${score}`;
    fetchWord();
  } else {
    guessInput.style.backgroundColor = '#f8d7da'; // Red
  }

  // Save guess
  fetch('game.php?action=get')
    .then(res => res.json())
    .then(data => {
      data.guesses = data.guesses || [];
      data.guesses.push(inputValue);
      return fetch('game.php?action=set', {
        method: 'POST',
        body: JSON.stringify(data)
      });
    });
}

// Optional debug
setInterval(() => {
  fetch('game.php?action=get')
    .then(res => res.json())
    .then(data => {
      console.log("Guesses:", data.guesses);
    });
}, 3000);
