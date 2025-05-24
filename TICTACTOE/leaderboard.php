<?php
// Connect to the database
$conn = new mysqli("localhost", "root", "", "ttt_scores");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch players sorted by total wins
$sql = "SELECT player, wins, losses, draws, streak FROM scores ORDER BY wins DESC, player ASC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Leaderboard - Tic Tac Toe</title>
    <link href="https://fonts.googleapis.com/css2?family=Rubik&display=swap" rel="stylesheet">
    <style>
        body {
        font-family: 'Rubik', sans-serif;
        margin: 0;
        padding: 0;
        text-align: center;
        background: url('../IMAGES_GIF/payrplay.gif') no-repeat center center fixed;
        background-size: cover;
        overflow: hidden;
        color: #fff;
        }

        h1 {
            margin: 0;
            padding: 20px 0;
            font-size: 2.5em;
            text-shadow: 0 0 8px #00f0ff, 0 0 16px #00f0ff;
        }

        table {
            margin: 30px auto;
            width: 85%;
            border-collapse: collapse;
            background-color: rgba(0, 0, 20, 0.7);
            box-shadow: 0 0 20px rgba(0,255,255,0.3);
            border-radius: 12px;
            overflow: hidden;
        }

        th, td {
            padding: 14px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            color: #fff;
        }

        th {
            background-color: rgba(0, 0, 40, 0.8);
            font-weight: bold;
            text-shadow: 0 0 5px #0ff;
        }

        tr:hover {
            background-color: rgba(0, 50, 80, 0.3);
        }

        .back-btn {
            display: inline-block;
            margin: 30px auto;
            padding: 12px 28px;
            background-color: rgba(0, 204, 255, 0.8);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            transition: 0.3s;
            box-shadow: 0 0 12px rgba(0,204,255,0.5);
        }

        .back-btn:hover {
            background-color: rgba(0, 150, 255, 0.9);
            box-shadow: 0 0 18px rgba(0,204,255,0.8);
        }

        .rank {
            color: #0ff;
            font-weight: bold;
            text-shadow: 0 0 6px #0ff;
        }
    </style>
</head>
<body>
    <h1>🏆 Tic Tac Toe Leaderboard</h1>
    <table>
        <tr>
            <th>Rank</th>
            <th>Player</th>
            <th>Wins</th>
            <th>Losses</th>
            <th>Draws</th>
            <th>Win Streak</th>
        </tr>
        <?php
        if ($result->num_rows > 0) {
            $rank = 1;
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                    <td class='rank'>#{$rank}</td>
                    <td>".htmlspecialchars($row['player'])."</td>
                    <td>{$row['wins']}</td>
                    <td>{$row['losses']}</td>
                    <td>{$row['draws']}</td>
                    <td>{$row['streak']}</td>
                </tr>";
                $rank++;
            }
        } else {
            echo "<tr><td colspan='5'>No scores recorded yet.</td></tr>";
        }
        ?>
    </table>
    <a class="back-btn" href="index.php">⬅ Back to Lobby</a>
</body>
</html>
