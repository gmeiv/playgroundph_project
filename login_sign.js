window.addEventListener('DOMContentLoaded', () => {
  const urlParams = new URLSearchParams(window.location.search);
  const mode = urlParams.get('mode');
  const error = urlParams.get('error');
  const toggle = document.getElementById('toggle');

  // Toggle to correct form
  if (mode === 'register') {
    toggle.checked = true;
  } else {
    toggle.checked = false;
  }

  // Show appropriate error
  if (mode === 'login') {
    const loginError = document.getElementById("login-error");
    const loginUser = document.getElementById('login-username');

    // Reset error messages and styles
    loginError.style.display = 'none';
    loginUser.classList.remove('error');

    if (error === 'nouser') {
      loginError.textContent = 'Username not found.';
      loginError.style.display = 'block';
      loginUser.classList.add('error');
    } else if (error === 'wrongpass') {
      loginError.textContent = 'Incorrect password.';
      loginError.style.display = 'block';
      loginUser.classList.add('error');
    }
  }

  if (mode === 'register') {
    const registerError = document.getElementById('register-error');
    const registerUser = document.getElementById('register-username');

    // Reset error messages and styles
    registerError.style.display = 'none';
    registerUser.classList.remove('error');

    if (error === 'exists') {
      registerError.textContent = 'Username already exists.';
      registerError.style.display = 'block';
      registerUser.classList.add('error');
    } else if (error === 'nomatch') {
      registerError.textContent = 'Passwords do not match.';
      registerError.style.display = 'block';
    } else if (error === 'fail') {
      registerError.textContent = 'Registration failed. Please try again.';
      registerError.style.display = 'block';
    }
  }
});

// Magic button effects remain unchanged
const magicButton = document.getElementById("magicButton");
let starInterval = null;

magicButton.addEventListener("mouseleave", () => {
  if (!starInterval) {
    starInterval = setInterval(() => {
      createStar(magicButton);
    }, 300);
  }
});

magicButton.addEventListener("mouseenter", () => {
  if (starInterval) {
    clearInterval(starInterval);
    starInterval = null;
  }
});

function createStar(button) {
  const star = document.createElement("div");
  star.classList.add("star");
  star.innerHTML = "★";

  const rect = button.getBoundingClientRect();
  const size = 20;
  star.style.left = Math.random() * (rect.width - size) + "px";
  star.style.top = Math.random() * (rect.height - size) + "px";

  button.appendChild(star);

  setTimeout(() => {
    star.remove();
  }, 1000);
}
