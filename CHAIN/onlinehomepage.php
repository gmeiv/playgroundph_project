<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Chain Reaction Online - Play With Friends</title>
  <style>
    body {
      background: url('../IMAGES_GIF/payrplay.gif') no-repeat center center fixed;
      background-size: cover;
      overflow: hidden;
      color: #eaf6ff;
      text-align: center;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      padding: 40px;
      position: relative;
    }

    .back-button {
      position: absolute;
      top: 20px;
      left: 20px;
      background-color: rgba(0, 51, 102, 0.8);
      color: #eaf6ff;
      text-decoration: none;
      padding: 10px 18px;
      border-radius: 10px;
      font-size: 1.1em;
      font-weight: bold;
      box-shadow: 0 4px 12px rgba(0, 80, 180, 0.3);
      transition: background 0.3s, transform 0.2s;
    }

    .back-button:hover {
      background-color: #0056b3;
      transform: scale(1.05);
    }

    h1 {
      font-size: 3em;
      margin-bottom: 10px;
      color: #7ecbff;
      text-shadow: 0 0 12px #0056b3, 0 0 4px #00e1ff;
      letter-spacing: 2px;
    }

    h3 {
      color: #4fa3ff;
      text-shadow: 0 2px 8px #003366;
    }

    .description {
      max-width: 800px;
      margin: 0 auto 30px auto;
      font-size: 1.2em;
      line-height: 1.6;
      background: rgba(20, 40, 90, 0.65);
      border-radius: 14px;
      padding: 32px 22px;
      box-shadow: 0 8px 32px 0 rgba(0, 40, 80, 0.25);
      backdrop-filter: blur(4px);
      -webkit-backdrop-filter: blur(4px);
      border: 1.5px solid rgba(120, 180, 255, 0.15);
    }

    .btn-container {
      margin-top: 30px;
    }

    .home-button {
      background: linear-gradient(90deg, #003366 60%, #0056b3 100%);
      color: #eaf6ff;
      padding: 15px 30px;
      margin: 15px;
      border: none;
      border-radius: 10px;
      font-size: 1.2em;
      cursor: pointer;
      transition: background 0.3s, box-shadow 0.3s, transform 0.3s;
      text-decoration: none;
      display: inline-block;
      box-shadow: 0 2px 8px rgba(0, 120, 255, 0.15);
      font-weight: 600;
      position: relative;
      overflow: hidden;
    }

    .home-button::before {
      content: '';
      position: absolute;
      top: -2px;
      left: -2px;
      right: -2px;
      bottom: -2px;
      background: linear-gradient(45deg, #2c187d, #6f6abe, #007bff);
      border-radius: 10px;
      filter: blur(10px);
      opacity: 0;
      transition: opacity 0.3s ease-in-out;
      z-index: -1;
    }

    .home-button:hover::before {
      opacity: 1;
    }

    .home-button:hover {
      background: linear-gradient(90deg, #002244 60%, #007bff 100%);
      box-shadow: 0 0 18px #00e1ff;
      transform: scale(1.05);
    }
  </style>
</head>
<body>
  <a href="home.php" class="back-button">← Back</a>

  <h1>Chain Reaction Online</h1>

  <div class="description">
    <h3>How the Online Version Works</h3>
    <p>
      Play Chain Reaction in real-time with friends across the web. Create or join game rooms, share room links, and compete in this strategic turn-based multiplayer game!
    </p>
    <p>
      When you start a game, a unique room link is generated — send it to your friends so you can play together simultaneously.
    </p>
  </div>

  <div class="btn-container">
    <button class="home-button" id="startGameBtn">Start Game</button>
  </div>

  <script>
    document.getElementById('startGameBtn').addEventListener('click', () => {
      window.location.href = '../CHAIN/homepage.php';
    });
  </script>
</body>
</html>
