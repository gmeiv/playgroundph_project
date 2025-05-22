function updateGameState($state, $user, $x, $y) {
  if ($state['turn'] !== $user) return $state;

  $cell = &$state['board'][$x][$y];

  // Allow only if empty or owned
  if ($cell === 0 || $cell['player'] === $user) {
    if ($cell === 0) $cell = ['count' => 0, 'player' => $user];
    $cell['count']++;
    $cell['player'] = $user;

    // Handle explosion
    $state = handleExplosions($state, $x, $y);

    // Rotate turn
    $players = $state['players'];
    $index = array_search($user, $players);
    $state['turn'] = $players[($index + 1) % count($players)];
  }

  return $state;
}

function handleExplosions($state, $x, $y) {
  $queue = [[$x, $y]];
  while (!empty($queue)) {
    [$i, $j] = array_shift($queue);
    $cell = &$state['board'][$i][$j];
    $limit = 4;
    if (($i === 0 || $i === 5) + ($j === 0 || $j === 8) === 2) $limit = 2;
    elseif ($i === 0 || $i === 5 || $j === 0 || $j === 8) $limit = 3;

    if ($cell['count'] >= $limit) {
      $owner = $cell['player'];
      $cell['count'] = 0;
      $cell['player'] = null;
      foreach ([[-1,0],[1,0],[0,-1],[0,1]] as [$dx, $dy]) {
        $ni = $i + $dx; $nj = $j + $dy;
        if ($ni >= 0 && $ni < 6 && $nj >= 0 && $nj < 9) {
          $ncell = &$state['board'][$ni][$nj];
          if ($ncell === 0) $ncell = ['count' => 0, 'player' => $owner];
          $ncell['count']++;
          $ncell['player'] = $owner;
          $queue[] = [$ni, $nj];
        }
      }
    }
  }

  return $state;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['room'])) {
  echo file_get_contents("state_" . $_GET['room'] . ".json");
}
