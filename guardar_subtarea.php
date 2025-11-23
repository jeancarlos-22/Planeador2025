<?php
require_once 'config.php';
require_login();

$data = json_decode(file_get_contents('php://input'), true);
$task_id = $data['task_id'] ?? 0;
$title = $data['title'] ?? '';
$description = $data['description'] ?? '';

if($task_id && $title){
    $stmt = $pdo->prepare("INSERT INTO subtasks (task_id, title, description, created_by) VALUES (?, ?, ?, ?)");
    $stmt->execute([$task_id, $title, $description, $_SESSION['user_id']]);
    echo json_encode(['success'=>true]);
} else {
    echo json_encode(['success'=>false,'msg'=>'Datos incompletos']);
}
