<?php
$lobbyFile = 'lobby.json';
$lobby = file_exists($lobbyFile) ? json_decode(file_get_contents($lobbyFile), true) : ['rooms' => []];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $room = $_POST['room'];
    $user = $_POST['user'];

    if ($action === 'create') {
        $lobby['rooms'][$room] = [
            'players' => [$user],
            'spectators' => [],
            'status' => 'waiting',
            'created' => gmdate("c")
        ];
    } elseif ($action === 'join') {
        if (count($lobby['rooms'][$room]['players']) < 2 && !in_array($user, $lobby['rooms'][$room]['players'])) {
            $lobby['rooms'][$room]['players'][] = $user;
            $lobby['rooms'][$room]['status'] = 'started';
        } elseif (!in_array($user, $lobby['rooms'][$room]['spectators'])) {
            $lobby['rooms'][$room]['spectators'][] = $user;
        }
    }

    file_put_contents($lobbyFile, json_encode($lobby));
    echo json_encode(['success' => true]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode($lobby);
}
