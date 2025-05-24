<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Setup Game - Chain Reaction Offline</title>
    <style>
        body {
            background: radial-gradient(circle at center, #081028 0%, #061024 100%);
            color: #eaf6ff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            text-align: center;
            padding: 40px 20px;
            min-height: 100vh;
        }

        h2 {
            font-size: 2.2em;
            margin-bottom: 15px;
            color: #7ecbff;
            text-shadow: 0 0 12px #0056b3, 0 0 4px #00e1ff;
        }

        .explanation {
            max-width: 700px;
            margin: 0 auto 40px auto;
            font-size: 1.3em;
            line-height: 1.6;
            background: rgba(20, 40, 90, 0.65);
            padding: 25px 30px;
            border-radius: 15px;
            color: #eaf6ff;
            box-shadow: 0 8px 32px 0 rgba(0, 40, 80, 0.25);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            border: 1.5px solid rgba(120, 180, 255, 0.15);
        }

        form {
            max-width: 400px;
            margin: 0 auto;
        }

        label {
            font-size: 1.4em;
            display: block;
            margin-bottom: 15px;
            color: #4fa3ff;
            font-weight: 600;
        }

        select {
            width: 100%;
            padding: 14px;
            font-size: 1.2em;
            border-radius: 12px;
            border: 1.5px solid #2a5dbe;
            background: rgba(40, 80, 180, 0.22);
            color:rgb(106, 138, 220);
            font-weight: bold;
            cursor: pointer;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            text-align-last: center;
            transition: background-color 0.3s, border-color 0.3s;
        }

        select:focus, select:hover {
            background: rgba(40, 80, 180, 0.32);
            border-color: #00e1ff;
            outline: none;
        }

        select option {
            color: #0a183c;
            background: #eaf6ff;
            font-weight: 600;
        }

        button {
            margin-top: 30px;
            width: 100%;
            padding: 18px;
            font-size: 1.3em;
            background: linear-gradient(90deg, #003366 60%, #0056b3 100%);
            border: none;
            border-radius: 14px;
            font-weight: 700;
            color: #eaf6ff;
            cursor: pointer;
            transition: background 0.3s, box-shadow 0.3s, transform 0.3s;
            box-shadow: 0 2px 8px rgba(0, 120, 255, 0.15);
            position: relative;
            overflow: hidden;
        }

        button::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(45deg, #2c187d, #6f6abe, #007bff);
            border-radius: 14px;
            filter: blur(10px);
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
            z-index: -1;
        }

        button:hover::before {
            opacity: 1;
        }

        button:hover {
            background: linear-gradient(90deg, #002244 60%, #007bff 100%);
            box-shadow: 0 0 18px #00e1ff;
            transform: scale(1.05);
        }

        /* Remove default dropdown arrow for cleaner look */
        select::-ms-expand {
            display: none;
        }
    </style>
</head>
<body>
    <h2>Chain Reaction Offline</h2>

    <div class="explanation">
        <p>
            In <strong>Offline Play with Friends</strong>, you can play with <em>two to four players</em> using <strong>only one device</strong>.  
            Players take turns placing <strong>one orb at a time</strong> on the game board.
        </p>
        <p>
            Each cell can hold a limited number of orbs depending on its position. When a cell exceeds its limit, it explodes—spreading orbs to adjacent cells and potentially converting them to your control.
        </p>
        <p>
            The game continues with chain reactions across the board. The last player with orbs on the grid wins! This is a perfect setup for local multiplayer fun, strategy, and exciting twists.
        </p>
    </div>

    <form method="post" action="game.php">
        <label for="players">Select Number of Players</label>
        <select name="players" id="players" required>
            <option value="" disabled selected>Number of players</option>
            <option value="2">2 Players</option>
            <option value="3">3 Players</option>
            <option value="4">4 Players</option>
        </select>

        <button type="submit">Start Game</button>
    </form>
</body>
</html>
