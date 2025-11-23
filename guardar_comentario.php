<?php
require_once 'config.php';
require_login();

$data = json_decode(file_get_contents('php://input'), true);
$task_id = $data['task_id'] ?? 0;
$comment = $data['comment'] ?? '';

if($task_id && $comment){
    $stmt = $pdo->prepare("INSERT INTO comments (task_id, comment, user_id) VALUES (?, ?, ?)");
    $stmt->execute([$task_id, $comment, $_SESSION['user_id']]);
    echo json_encode(['success'=>true]);
} else {
    echo json_encode(['success'=>false,'msg'=>'Datos incompletos']);
}
