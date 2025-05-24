
<!DOCTYPE html>
<html>
<head>
    <title>Chain Reaction - Home</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background: radial-gradient(circle at center, #0a1733 0%, #061024 100%);
            color: #eaf6ff;
            text-align: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 40px 0;
            min-height: 100vh;
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
            color: white;
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

        .emoji {
            font-size: 1.5em;
        }
    </style>
</head>
<body>
    <h1> Chain Reaction </h1>

    <div class="description">
        <h3>What is Chain Reaction?</h3>
        <p>
            <span class="emoji"></span> Chain Reaction is a strategic, turn-based multiplayer game. 
            Players take turns placing orbs in cells. Each cell has a limit, and when exceeded, it causes an explosion, spreading orbs to neighboring cells — potentially triggering more explosions!
        </p>

        <h3>How to Play</h3>
        <p>
            <span class="emoji"></span> You and your friends take turns placing colored orbs in a 6×9 grid.  
            A cell can hold only a certain number of orbs before it explodes.  
            When it does, it sends orbs to neighboring cells — capturing them if they were previously owned by another player.
        </p>
        <p>
            <span class="emoji"></span> The goal? Be the last player standing by eliminating your opponents through chain reactions!
        </p>
    </div>

    <div class="btn-container">
        <a href="onlinehomepage.php" class="home-button">🌐 Online Play with Friends</a>
        <a href="setup.php" class="home-button">👥 Offline Play with Friends</a>
    </div>
</body>
</html>
