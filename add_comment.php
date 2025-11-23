<?php
require_once 'config.php';
require_login();
require_role(['estudiante','profesor','admin']);

$data = json_decode(file_get_contents('php://input'), true);
$task_id = $data['task_id'] ?? null;
$comment = trim($data['comment'] ?? '');

if(!$task_id || !$comment){
    echo json_encode(['success'=>false,'msg'=>'Faltan datos']);
    exit;
}

// Insertar comentario
$stmt = $pdo->prepare("INSERT INTO task_comments (task_id, user_id, comment, created_at) VALUES (?, ?, ?, NOW())");
$res = $stmt->execute([$task_id, $_SESSION['user_id'], $comment]);

if($res){
    echo json_encode(['success'=>true]);
}else{
    echo json_encode(['success'=>false,'msg'=>'Error al guardar']);
}
?>
