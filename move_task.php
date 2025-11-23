<?php
require_once 'config.php';
require_login();
require_role(['estudiante','profesor','admin']);
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$id = (int)($data['id'] ?? 0);
$newStatus = $data['status'] ?? '';
$user_id = $_SESSION['user_id'] ?? null;

// Estados válidos según planeador
$allowed = ['todo','doing','review','done'];

if(!$id || !in_array($newStatus,$allowed)){
    echo json_encode(['success'=>false,'error'=>'Datos inválidos']);
    exit;
}

// Obtener tarea
$stmt = $pdo->prepare("SELECT status, order_index FROM tasks WHERE id=?");
$stmt->execute([$id]);
$task = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$task){
    echo json_encode(['success'=>false,'error'=>'Tarea no encontrada']);
    exit;
}

$oldStatus = $task['status'];
$oldOrder = (int)$task['order_index'];

// Calcular el nuevo orden al final de la columna destino
$stmt = $pdo->prepare("SELECT COALESCE(MAX(order_index),0)+1 FROM tasks WHERE status=?");
$stmt->execute([$newStatus]);
$newOrder = (int)$stmt->fetchColumn();

try {
    $pdo->beginTransaction();

    if($oldStatus === $newStatus){
        // Solo reordenar dentro de la misma columna
        $stmt = $pdo->prepare("UPDATE tasks SET order_index=? WHERE id=?");
        $stmt->execute([$newOrder,$id]);
    } else {
        // Ajustar orden en columna antigua
        $stmt = $pdo->prepare("UPDATE tasks SET order_index=order_index-1 WHERE status=? AND order_index>?");
        $stmt->execute([$oldStatus,$oldOrder]);

        // Actualizar tarea con nuevo estado y orden
        $stmt = $pdo->prepare("UPDATE tasks SET status=?, order_index=? WHERE id=?");
        $stmt->execute([$newStatus,$newOrder,$id]);
    }

    $pdo->commit();
    log_action($pdo,$user_id,'move_task',"Movió tarea ID $id a $newStatus (posición $newOrder)");
    echo json_encode(['success'=>true]);

} catch(Exception $e){
    $pdo->rollBack();
    echo json_encode(['success'=>false,'error'=>'Error al mover tarea: '.$e->getMessage()]);
}





