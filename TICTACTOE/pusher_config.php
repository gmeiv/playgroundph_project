<?php
require 'vendor/autoload.php'; // Ensure Pusher PHP SDK is installed

$pusher = new Pusher\Pusher(
    "f19facd60b851f60a0e3",
    "595ce39f6c7ee69924c5",
    "1996214",
    [
        'cluster' => 'ap1',
        'useTLS' => true
    ]
);
