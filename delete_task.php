<?php
require_once 'config.php';
require_login();
require_role(['estudiante','profesor','admin']);

$data = json_decode(file_get_contents('php://input'), true);

if(isset($data['id'])){
    $id = (int)$data['id'];
    $stmt = $pdo->prepare("DELETE FROM tasks WHERE id=:id");
    if($stmt->execute([':id'=>$id])){
        echo json_encode(['success'=>true]);
        exit;
    } else {
        echo json_encode(['success'=>false,'error'=>'No se pudo eliminar la tarea.']);
        exit;
    }
}

echo json_encode(['success'=>false,'error'=>'ID de tarea inválido']);


