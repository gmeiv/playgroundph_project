const magicButton = document.getElementById("magicButton");
let starInterval = null;

magicButton.addEventListener("mouseleave", () => {
  // Start spawning stars continuously every 300ms
  if (!starInterval) {
    starInterval = setInterval(() => {
      createStar(magicButton);
    }, 300);
  }
});

magicButton.addEventListener("mouseenter", () => {
  // Optionally: Stop stars when hovered again
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