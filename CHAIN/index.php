<?php
session_start();
if (!isset($_SESSION['players'])) {
    header("Location: setup.php");
    exit();
}

$colors = ["red", "green", "blue", "gold"];
$emojis = ["🔴", "🟢", "🔵", "🟡"];
$player = $_SESSION['turn'] % $_SESSION['players'];

// Winner alert logic
$winnerAlert = '';
if (isset($_SESSION['show_winner'])) {
    $winnerIdx = $_SESSION['show_winner']['winner'];
    $scores = $_SESSION['show_winner']['scores'];
    $scoreMsg = '';
    foreach ($scores as $i => $score) {
        $scoreMsg .= "Player " . ($i+1) . " ({$emojis[$i]}): $score\\n";
    }
    $winnerAlert = "Player " . ($winnerIdx+1) . " ({$emojis[$winnerIdx]}) wins!\\n\\nScores:\\n$scoreMsg";
    unset($_SESSION['show_winner']);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Chain Reaction Game</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body class="player-<?= $player ?>">
    <h1>Chain Reaction</h1>

    <?php if (isset($_SESSION['winner'])): ?>
        <h2>🎉 Player <?= $_SESSION['winner'] + 1 ?> Wins! <?= $emojis[$_SESSION['winner']] ?></h2>
    <?php else: ?>
        <h2>Player <?= $player + 1 ?>'s Turn <?= $emojis[$player] ?></h2>
    <?php endif; ?>

    <form method="post" action="game.php" class="top-buttons">
        <button type="submit" name="save">💾 Save Game</button>
        <button type="submit" name="load">📂 Load Game</button>
        <button type="submit" name="reset" onclick="return confirm('Are you sure you want to restart the game?')">🔁 Restart</button>
    </form>

    <!-- Scoreboard -->
    <div class="scoreboard">
        <h3>Scoreboard</h3>
        <ul>
        <?php
        foreach ($_SESSION['scores'] as $i => $score) {
            $eliminated = $_SESSION['eliminated'][$i];
            $emoji = $emojis[$i];
            $status = $eliminated ? "❌ Eliminated" : "$emoji";
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

    <script>
    // Winner alert
    <?php if ($winnerAlert): ?>
        alert("<?= $winnerAlert ?>");
        window.location.href = "setup.php";
    <?php endif; ?>
    </script>
</body>
</html>
