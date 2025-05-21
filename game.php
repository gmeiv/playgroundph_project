<?php
$filename = 'state.json';
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

if ($action === 'get') {
    echo file_get_contents($filename);
    exit;
}

if ($action === 'set') {
    $input = json_decode(file_get_contents('php://input'), true);
    file_put_contents($filename, json_encode($input));
    echo json_encode(['status' => 'ok']);
    exit;
}

if ($action === 'get_word') {
    header('Content-Type: application/json');

    $host = 'localhost';
    $db = 'draw_game'; // your DB name
    $user = 'root';    // your username
    $pass = '';        // your password

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->query("SELECT word FROM game_words ORDER BY RAND() LIMIT 3");
        $words = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (!$words) {
            echo json_encode(["error" => "No words found"]);
        } else {
            echo json_encode($words);
        }

    } catch (PDOException $e) {
        echo json_encode([
            "error" => "DB connection failed",
            "message" => $e->getMessage()
        ]);
    }
    exit;
}
?>
