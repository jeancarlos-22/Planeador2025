<?php
require_once 'config.php';
require_login();

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    http_response_code(403);
    echo json_encode(['success'=>false]);
    exit;
}

$stmt = $pdo->prepare("UPDATE notifications SET read_at = NOW() WHERE receiver_id = ?");
$stmt->execute([$user_id]);
echo json_encode(['success'=>true]);










