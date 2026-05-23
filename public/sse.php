<?php

require_once __DIR__ . '/../config/env.php';
loadEnv(__DIR__ . '/../.env');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';

$sessionName = $_ENV['SESSION_NAME'] ?? 'jaanahai_session';
session_name($sessionName);
session_start();

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    exit;
}

$rideId = (int) ($_GET['ride_id'] ?? 0);
if (!$rideId) {
    http_response_code(400);
    exit;
}

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('X-Accel-Buffering: no');

$pdo  = getDB();
$stmt = $pdo->prepare('SELECT status, people FROM offers WHERE id = ?');
$stmt->execute([$rideId]);
$row = $stmt->fetch();

$data = json_encode([
    'status' => $row['status'] ?? 'open',
    'people' => (int) ($row['people'] ?? 0),
]);

echo "data: $data\n\n";
flush();
exit;
