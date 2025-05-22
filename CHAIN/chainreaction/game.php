<?php
$room = $_GET['room'];
$user = $_GET['user'];
$lobby = json_decode(file_get_contents('lobby.json'), true);
$isSpectator = in_array($user, $lobby['rooms'][$room]['spectators']);
$stateFile = "state_$room.json";

// Initialize game state if doesn't exist
if (!file_exists($stateFile)) {
    $players = $lobby['rooms'][$room]['players'];
    $state = [
        'board' => array_fill(0, 6, array_fill(0, 8, null)),
        'players' => $players,
        'turn' => 0,
        'winner' => null,
        'game_over' => false
    ];
    file_put_contents($stateFile, json_encode($state));
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Chain Reaction – Room <?php echo htmlspecialchars($room); ?></title>
  <script src="https://js.pusher.com/7.2/pusher.min.js"></script>
  <style>
    table { border-collapse: collapse; }
    td { width: 50px; height: 50px; border: 1px solid #ccc; text-align: center; font-weight: bold; font-size: 20px; }
    td:hover { background: #eef; cursor: pointer; }
  </style>
</head>
<body>
  <h2>Room: <?php echo htmlspecialchars($room); ?> | You: <?php echo htmlspecialchars($user); ?><?php if ($isSpectator) echo " (Spectator)"; ?></h2>
  <div id="board"></div>
  <p id="status"></p>

  <script>
    const room = <?php echo json_encode($room); ?>;
    const user = <?php echo json_encode($user); ?>;
    const isSpectator = <?php echo json_encode($isSpectator); ?>;

    function renderBoard(board) {
      const container = document.getElementById('board');
      container.innerHTML = '<table>' +
        board.map((row, i) => '<tr>' +
          row.map((cell, j) => {
            let value = cell ? cell.count : '';
            return `<td data-i="${i}" data-j="${j}">${value}</td>`;
          }).join('') +
        '</tr>').join('') +
        '</table>';
      
      if (!isSpectator) {
        document.querySelectorAll('td').forEach(cell => {
          cell.onclick = () => {
            const i = cell.getAttribute('data-i');
            const j = cell.getAttribute('data-j');
            fetch('move.php', {
              method: 'POST',
              body: new URLSearchParams({ room, user, i, j })
            });
          };
        });
      }
    }

    const pusher = new Pusher('f19facd60b851f60a0e3', { cluster: 'ap1' });
    const channel = pusher.subscribe('room-' + room);
    channel.bind('move', data => {
      renderBoard(data.board);
      document.getElementById('status').innerText = data.message;
    });

    fetch('state_' + room + '.json')
      .then(res => res.json())
      .then(data => renderBoard(data.board));
  </script>
</body>
</html>
