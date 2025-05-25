<?php
session_start();
if (!isset($_SESSION['players'])) {
    header("Location: setup.php");
    exit();
}

$colors = ["white", "white", "white", "white"];
$emojis = ["", "", "", ""];
$player = $_SESSION['turn'] % $_SESSION['players'];

// Winner data for popup
$winnerData = null;
if (isset($_SESSION['show_winner'])) {
    $winnerData = [
        'winner' => $_SESSION['show_winner']['winner'],
        'scores' => $_SESSION['show_winner']['scores'],
        'players' => $_SESSION['players']
    ];
    unset($_SESSION['show_winner']);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Chain Reaction Game</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <style>
        body {
            background: url('../IMAGES_GIF/payrplay.gif') no-repeat center center fixed;
            background-size: cover;
        }
        /* Winner Popup Styles */
        .winner-popup {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .winner-popup.active {
            display: flex;
        }

        .winner-content {
            background: linear-gradient(135deg, #1a2a6c, #b21f1f, #fdbb2d);
            padding: 40px;
            border-radius: 16px;
            text-align: center;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 10px 50px rgba(0, 0, 0, 0.5);
            position: relative;
            overflow: hidden;
        }

        .winner-content::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(
                to bottom right,
                rgba(255, 255, 255, 0.1) 0%,
                rgba(255, 255, 255, 0) 50%,
                rgba(255, 255, 255, 0.1) 100%
            );
            transform: rotate(30deg);
            animation: shine 3s infinite linear;
        }

        @keyframes shine {
            0% { transform: translateX(-100%) rotate(30deg); }
            100% { transform: translateX(100%) rotate(30deg); }
        }

        .winner-content h2 {
            font-size: 2.5em;
            margin-bottom: 20px;
            color: gold;
            text-shadow: 0 0 10px rgba(255, 215, 0, 0.7);
        }

        .winner-content p {
            font-size: 1.1em;
            margin-bottom: 10px;
            color: white;
        }

        .winner-content button {
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid white;
            color: white;
            padding: 12px 30px;
            font-size: 1.2em;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s ease;
            outline: none;
            margin: 10px;
        }

        .winner-content button:hover {
            background: rgba(255, 255, 255, 0.4);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        .trophy {
            font-size: 5em;
            margin-bottom: 20px;
            display: inline-block;
            animation: bounce 0.5s ease infinite alternate;
        }

        @keyframes bounce {
            from { transform: translateY(0); }
            to { transform: translateY(-20px); }
        }
    </style>
</head>
<body class="player-<?= $player ?>">
    <h1>Chain Reaction</h1>

    <?php if (isset($_SESSION['winner'])): ?>
        <h2> Player <?= $_SESSION['winner'] + 1 ?> Wins! <?= $emojis[$_SESSION['winner']] ?></h2>
    <?php else: ?>
        <h2>Player <?= $player + 1 ?>'s Turn <?= $emojis[$player] ?></h2>
    <?php endif; ?>

    <div class="top-buttons">
        <a href="dashboard.php"><button type="button">Dashboard</button></a>
        <a href="home.php"><button type="button">Lobby</button></a>
        <form method="post" action="game.php" style="display: inline;">
            <button type="submit" name="reset" onclick="return confirm('Are you sure you want to restart the game?')">Restart</button>
        </form>
    </div>

    <!-- Scoreboard -->
    <div class="scoreboard">
        <h3>Scoreboard</h3>
        <ul>
        <?php
        foreach ($_SESSION['scores'] as $i => $score) {
            $eliminated = $_SESSION['eliminated'][$i];
            $emoji = $emojis[$i];
            $status = $eliminated ? "Eliminated" : "$emoji";
            $style = $eliminated ? "class='eliminated'" : "";
            echo "<li $style style='color: {$colors[$i]}'>Player " . ($i+1) . ": $score pts $status</li>";
        }
        ?>
        </ul>
    </div>

    <!-- Game board -->
    <?php if (!isset($_SESSION['winner'])): ?>
        <div class="board">
            <?php
            $exploding = $_SESSION['exploding'] ?? [];
            for ($row = 0; $row < 6; $row++): ?>
                <div class="row">
                    <?php for ($col = 0; $col < 9; $col++):
                        $owner = $_SESSION['owners'][$row][$col];
                        $ownedClass = ($owner !== -1) ? "owned player-$owner" : "empty";
                        $isExploding = in_array([$row, $col], $exploding);
                        $explodeClass = $isExploding ? 'explode' : '';
                        $count = $_SESSION['counts'][$row][$col];
                    ?>
                        <form method="post" action="game.php">
                            <input type="hidden" name="row" value="<?= $row ?>">
                            <input type="hidden" name="col" value="<?= $col ?>">
                            <button type="submit" class="cell <?= $ownedClass ?> <?= $explodeClass ?>" data-count="<?= $count ?>">
                                <span><?= $_SESSION['grid'][$row][$col] ?? '' ?></span>
                            </button>
                        </form>
                    <?php endfor; ?>
                </div>
            <?php endfor; ?>
        </div>
        <?php $_SESSION['exploding'] = []; ?>
    <?php else: ?>
        <p style="color: gold; font-size: 18px; text-align: center;">Game Over — Please restart to play again.</p>
    <?php endif; ?>

    <!-- Winner Popup -->
    <div class="winner-popup" id="winnerPopup">
        <div class="winner-content">
            <div class="trophy"></div>
            <h2>Congratulations!</h2>
            <p id="winnerMessage"></p>
            <div id="scoresList"></div>
            <div>
                <form method="post" action="game.php" style="display:inline;">
                    <button type="submit" name="reset">Play Again</button>
                </form>
                <button onclick="window.location.href='setup.php'">Back to Lobby</button>
            </div>
        </div>
    </div>

    <script>
    // Show winner popup if there's winner data
    <?php if ($winnerData): ?>
        const popup = document.getElementById('winnerPopup');
        const winnerMessage = document.getElementById('winnerMessage');
        const scoresList = document.getElementById('scoresList');
        
        // Set winner message
        winnerMessage.textContent = `Player <?= $winnerData['winner'] + 1 ?> wins the game!`;
        
        // Create scores list
        let scoresHTML = '';
        <?php foreach ($winnerData['scores'] as $i => $score): ?>
            scoresHTML += `<p>Player <?= $i + 1 ?>: <?= $score ?> points</p>`;
        <?php endforeach; ?>
        scoresList.innerHTML = scoresHTML;
        
        // Show popup
        popup.classList.add('active');
    <?php endif; ?>
    </script>
 

</body>
</html>