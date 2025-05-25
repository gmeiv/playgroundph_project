<?php
// Connect to the database
$conn = new mysqli("localhost", "u778263593_root", "PlaygroundPH00", "u778263593_playgroundph");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch players sorted by total wins
$sql = "SELECT player, wins, losses, draws, streak FROM scores ORDER BY wins DESC, player ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tic Tac Toe - Leaderboard</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Rubik&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Rubik', sans-serif;
            background: url('../IMAGES_GIF/payrplay.gif') no-repeat center center fixed;
            background-size: cover;
            color: #eee;
            text-align: center;
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        h1 {
            color: #00f0ff;
            font-size: 2.5em;
            margin-bottom: 30px;
            text-shadow: 0 0 20px rgba(0, 240, 255, 0.5);
        }
        .stats-summary {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        .stat-card {
            background: rgba(255, 255, 255, 0.1);
            padding: 15px 25px;
            border-radius: 10px;
            border: 1px solid rgba(0, 240, 255, 0.3);
            backdrop-filter: blur(10px);
        }
        .stat-card h3 {
            margin: 0 0 5px 0;
            color: #00f0ff;
        }
        .stat-card p {
            margin: 0;
            font-size: 1.2em;
            font-weight: bold;
        }
        table {
            width: 100%;
            margin: 30px auto;
            border-collapse: collapse;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            overflow: hidden;
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }
        th, td {
            padding: 15px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        th {
            background: rgba(0, 240, 255, 0.2);
            color: #00f0ff;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        tr:nth-child(even) {
            background: rgba(255, 255, 255, 0.05);
        }
        tr:hover {
            background: rgba(0, 240, 255, 0.1);
            transition: all 0.3s ease;
        }
        .rank {
            font-weight: bold;
            font-size: 1.1em;
        }
        .rank-1 { color: #FFD700; }
        .rank-2 { color: #C0C0C0; }
        .rank-3 { color: #CD7F32; }
        .player-name {
            font-weight: bold;
            color: #fff;
        }
        .win-rate {
            color: #4CAF50;
            font-weight: bold;
        }
        .navigation {
            margin-top: 30px;
        }
        .nav-button {
            background: linear-gradient(45deg, #00f0ff, #0080ff);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            text-decoration: none;
            display: inline-block;
            font-weight: bold;
            transition: all 0.3s ease;
            margin: 0 10px;
        }
        .nav-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 240, 255, 0.4);
        }
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #888;
        }
        .refresh-button {
            background: transparent;
            border: 2px solid #00f0ff;
            color: #00f0ff;
            padding: 10px 20px;
            border-radius: 20px;
            cursor: pointer;
            margin-left: 15px;
            transition: all 0.3s ease;
        }
        .refresh-button:hover {
            background: #00f0ff;
            color: #000;
        }
        @media (max-width: 768px) {
            table { font-size: 0.9em; }
            th, td { padding: 10px; }
            .stats-summary { flex-direction: column; align-items: center; }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🏆 Tic Tac Toe Leaderboard
            <button class="refresh-button" onclick="location.reload()">↻ Refresh</button>
        </h1>
        <?php
        // Calculate summary stats
        $totalPlayers = 0;
        $totalGames = 0;
        $result->data_seek(0);
        while ($row = $result->fetch_assoc()) {
            $totalPlayers++;
            $totalGames += ($row['wins'] + $row['losses'] + (isset($row['draws']) ? $row['draws'] : 0));
        }
        $totalGames = $totalGames / 2; // Each game has two players
        ?>
        <div class="stats-summary">
            <div class="stat-card">
                <h3>Total Players</h3>
                <p><?= $totalPlayers ?></p>
            </div>
            <div class="stat-card">
                <h3>Games Played</h3>
                <p><?= $totalGames ?></p>
            </div>
        </div>
        <?php
        $result->data_seek(0);
        if ($result->num_rows > 0):
        ?>
        <table>
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Player Name</th>
                    <th>Wins</th>
                    <th>Losses</th>
                    <th>Draws</th>
                    <th>Win Rate</th>
                    <th>Win Streak</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $rank = 1;
                while ($row = $result->fetch_assoc()):
                    $totalGamesPlayed = $row['wins'] + $row['losses'] + (isset($row['draws']) ? $row['draws'] : 0);
                    $winRate = ($totalGamesPlayed > 0) ? round(($row['wins'] / $totalGamesPlayed) * 100, 1) : 0;
                    $rankClass = $rank <= 3 ? "rank-$rank" : "";
                ?>
                <tr>
                    <td class="rank <?= $rankClass ?>"> <?= $rank ?> </td>
                    <td class="player-name"><?= htmlspecialchars($row['player']) ?></td>
                    <td><?= $row['wins'] ?></td>
                    <td><?= $row['losses'] ?></td>
                    <td><?= isset($row['draws']) ? $row['draws'] : 'N/A' ?></td>
                    <td class="win-rate"><?= $winRate ?>%</td>
                    <td><strong><?= $row['streak'] ?></strong></td>
                </tr>
                <?php 
                $rank++;
                endwhile; 
                ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="empty-state">
            <h2>No games played yet!</h2>
            <p>Be the first to play and claim the top spot on the leaderboard.</p>
        </div>
        <?php endif; ?>
        <div class="navigation">
            <a href="online_game.php" class="nav-button">🏠 Lobby</a>
        </div>
    </div>
    <script>
        // Auto-refresh every 30 seconds
        setTimeout(() => {
            location.reload();
        }, 30000);
    </script>
</body>
</html>
<?php $conn->close(); ?>
