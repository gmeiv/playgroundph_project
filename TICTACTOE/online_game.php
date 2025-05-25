<?php
require 'pusher_config.php';

session_start();

// Redirect if not logged in
if (!isset($_SESSION["user"])) {
    header("Location: login_sign.html");
    exit;
}

$username = $_SESSION["user"];

// Function to get all active rooms
function getActiveRooms() {
    $roomsDir = "rooms";
    $activeRooms = [];
    
    if (file_exists($roomsDir)) {
        $roomFiles = scandir($roomsDir);
        foreach ($roomFiles as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'json') {
                $roomData = json_decode(file_get_contents("$roomsDir/$file"), true);
                $activeRooms[] = [
                    'name' => pathinfo($file, PATHINFO_FILENAME),
                    'players' => $roomData['players'] ?? []
                ];
            }
        }
    }
    
    return $activeRooms;
}

$activeRooms = getActiveRooms();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tic Tac Toe - Lobby</title>
    <link href="https://fonts.googleapis.com/css2?family=Rubik&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="index.css">
    <script src="https://js.pusher.com/7.2/pusher.min.js"></script>
    <style>
         .magic_button {
            position: relative;
            padding: 12px 20px;
            font-size: 18px;
            border: 1px solid #555;
            border-radius: 6px;
            cursor: pointer;
            overflow: hidden;
            color: lightgray;
            background-color: rgba(80, 120, 255, 0.2);
            transition: box-shadow 0.4s ease, transform 0.3s ease;
            z-index: 1;
            width: 180px;
            text-decoration: none;
            text-align: center;
        }

        .magic_button::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(45deg, #2c187d, #6f6abe, #007bff);
            border-radius: 6px;
            filter: blur(10px);
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
            z-index: -1;
        }

        .magic_button:hover::before {
            opacity: 1;
        }

        .magic_button:hover {
            box-shadow: 0 0 20px #00f0ff;
        }

        .back-button {
            position: absolute;
            top: 20px;
            left: 20px;
            font-size: 18px;
            padding: 10px 16px;
            border-radius: 6px;
            border: 1px solid #555;
            background-color: rgba(80, 120, 255, 0.2);
            color: lightgray;
            text-decoration: none;
            transition: box-shadow 0.4s ease;
            z-index: 100;
        }

        .back-button::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(45deg, #2c187d, #6f6abe, #007bff);
            border-radius: 6px;
            filter: blur(10px);
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
            z-index: -1;
        }

        .back-button:hover::before {
            opacity: 1;
        }

        .back-button:hover {
            box-shadow: 0 0 15px #00f0ff;
        }

        .star {
            position: absolute;
            color: cyan;
            font-size: 16px;
            animation: sparkle 1s ease-out forwards;
            pointer-events: none;
        }

        @keyframes sparkle {
            0% {
                transform: translateY(0) scale(1);
                opacity: 1;
            }
            100% {
                transform: translateY(-40px) scale(0.5);
                opacity: 0;
            }
        }
        .active-rooms {
            margin-top: 20px;
            margin-bottom: 5px;
            max-width: 800px;
            background-color: rgba(15, 15, 40, 0.65);
            border-radius: 14px;
            padding: 25px;
            box-shadow: 0 8px 32px 0 rgba(0, 40, 80, 0.25);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            border: 1.5px solid rgba(120, 180, 255, 0.15);
            /* Add these new properties */
            max-height: 500px; /* Adjust this value as needed */
            overflow-y: auto; /* This enables vertical scrolling */
        }
        
        .active-rooms h2 {
            color: #b3e0ff;
            margin-bottom: 20px;
            text-align: center;
            text-shadow: 0 2px 8px #003366;
        }

        .active-rooms::-webkit-scrollbar {
            width: 10px;
        }

        .active-rooms::-webkit-scrollbar-track {
            background: rgba(0, 60, 120, 0.1);
            border-radius: 10px;
        }

        .active-rooms::-webkit-scrollbar-thumb {
            background: linear-gradient(#00e1ff, #0077ff);
            border-radius: 10px;
            border: 2px solid rgba(0, 225, 255, 0.2);
        }

        .active-rooms::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(#00b3ff, #0055ff);
        }

        /* For Firefox */
        .active-rooms {
            scrollbar-width: thin;
            scrollbar-color: #00e1ff rgba(0, 60, 120, 0.1);
        }
        
        .rooms-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 15px;
        }
        
        .room-card {
            background: rgba(0, 60, 120, 0.22);
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #00e1ff;
            transition: all 0.3s ease;
        }
        
        .room-card:hover {
            background: rgba(0, 80, 160, 0.3);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 225, 255, 0.2);
        }
        
        .room-name {
            color: #00e1ff;
            margin-bottom: 10px;
            font-size: 1.1em;
        }
        
        .players-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        
        .player-tag {
            background: rgba(0, 150, 255, 0.15);
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.9em;
        }
        
        .no-rooms {
            text-align: center;
            color: #7ecbff;
            padding: 20px;
            grid-column: 1 / -1;
        }
    </style>
</head>
<body>
    <header style="text-align: center; padding: 30px 0; margin-bottom: 30px;">
        <h1 style="font-size: 2.8em; color: #00eaff; text-shadow: 0 0 10px #0077ff, 0 0 20px #0033ff;">
            Tic Tac Toe Online Lobby
        </h1>
    </header>
    <a href="home.html" class="back-button">← Back</a>
    <div class="game-modes">
        <!-- Create Room -->
        <div class="container">
            <h2>Create a Room</h2>
            <p>Start a new 1v1 game</p>
            <form action="game.php" method="get">
                <input type="hidden" name="action" value="create">
                <input type="text" name="room" placeholder="Enter new room name" required>
                <input type="text" name="player" placeholder="Your name" value="<?= htmlspecialchars($username) ?>" required>
                <button class="magicbutton" type="submit">Create Room</button>
            </form>
        </div>

        <!-- Join Room -->
        <div class="container">
            <h2>Join a Room</h2>
            <p>Enter an existing room name to join</p>
            <form action="game.php" method="get">
                <input type="hidden" name="action" value="join">
                <input type="text" name="room" placeholder="Enter existing room name" required>
                <input type="text" name="player" placeholder="Your name" value="<?= htmlspecialchars($username) ?>" required>
                <button class="magicbutton" type="submit">Join Room</button>
            </form>
        </div>
    </div>
    <!-- Active Rooms Section -->
    <div class="active-rooms">
        <h2>Active Rooms</h2>
        <div class="rooms-grid">
            <?php if (empty($activeRooms)): ?>
                <div class="no-rooms">No active rooms available. Create one above!</div>
            <?php else: ?>
                <?php foreach ($activeRooms as $room): ?>
                    <div class="room-card">
                        <div class="room-name"><?= htmlspecialchars($room['name']) ?></div>
                        <div class="players-list">
                            <?php foreach ($room['players'] as $player): ?>
                                <span class="player-tag"><?= htmlspecialchars($player) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    <!-- Leaderboard -->
    <div class="leaderboard-link">
        <a href="leaderboard.php" id="leaderboard-link">View Leaderboards</a>
    </div>
                                

    <script>
        const pusher = new Pusher('f19facd60b851f60a0e3', { cluster: 'ap1' });
        const channel = pusher.subscribe('lobby');

        channel.bind('room-updated', function (data) {
            updateRoomCard(data.room, data.players);
        });

        function updateRoomCard(roomName, players) {
            const grid = document.querySelector('.rooms-grid');
            let card = [...grid.children].find(div =>
                div.querySelector('.room-name')?.textContent === roomName
            );

            // If room has no players, remove it
            if (players.length === 0) {
                if (card) card.remove();
                checkEmptyRooms();
                return;
            }

            // Else, create/update the room card
            if (!card) {
                card = document.createElement('div');
                card.className = 'room-card';
                card.innerHTML = `<div class="room-name"></div><div class="players-list"></div>`;
                grid.appendChild(card);
            }

            card.querySelector('.room-name').textContent = roomName;

            const playerList = card.querySelector('.players-list');
            playerList.innerHTML = '';
            players.forEach(player => {
                const span = document.createElement('span');
                span.className = 'player-tag';
                span.textContent = player;
                playerList.appendChild(span);
            });

            checkEmptyRooms();
        }

            function checkEmptyRooms() {
            const grid = document.querySelector('.rooms-grid');
            const hasRooms = [...grid.children].some(div => div.classList.contains('room-card'));
            let emptyNotice = document.querySelector('.no-rooms');

            if (!hasRooms) {
                if (!emptyNotice) {
                    emptyNotice = document.createElement('div');
                    emptyNotice.className = 'no-rooms';
                    emptyNotice.textContent = 'No active rooms available. Create one above!';
                    grid.appendChild(emptyNotice);
                }
            } else {
                if (emptyNotice) 
                emptyNotice.remove();
            }
        }
    </script>
</body>
</html>
