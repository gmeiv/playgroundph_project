
<?php
session_start();
$room = $_POST['room'] ?? null;
if (!$room) exit('No room specified');

$lobbyFile = __DIR__ . '/lobby.json';
if (file_exists($lobbyFile)) {
    $lobby = json_decode(file_get_contents($lobbyFile), true);
    if (isset($lobby['rooms'][$room])) {
        // Set status to finished (optional, for logging)
        $lobby['rooms'][$room]['status'] = 'finished';
        // Remove the room
        unset($lobby['rooms'][$room]);
        file_put_contents($lobbyFile, json_encode($lobby));
    }
}
echo 'ok';