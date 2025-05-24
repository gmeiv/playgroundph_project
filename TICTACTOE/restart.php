<?php
$data = json_decode(file_get_contents("php://input"), true);
$room = $data['room'];
$player = $data['player'];
$file = "rooms/$room.json";

if (file_exists($file)) {
    $state = json_decode(file_get_contents($file), true);
    if (in_array($player, $state['players'])) {
        $state['board'] = array_fill(0, 9, "");
        $state['turn'] = $state['players'][0]; // Reset to first player
        $state['winner'] = null;
        file_put_contents($file, json_encode($state));

        // Broadcast reset
        require __DIR__ . '/vendor/autoload.php';
        $pusher = new Pusher\Pusher('f19facd60b851f60a0e3', '595ce39f6c7ee69924c5', '1996214', [
            'cluster' => 'ap1',
            'useTLS' => true
        ]);
        $pusher->trigger("room-$room", 'move-made', $state);
    }
}
