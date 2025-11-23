<?php
require_once 'config.php';
require_login();
require_role(['estudiante','profesor','admin']);

header('Content-Type: application/json; charset=utf-8');

// Capturar entrada JSON
$rawData = file_get_contents('php://input');
$data = json_decode($rawData, true);

if (!$data || !isset($data['id'], $data['status'])) {
    echo json_encode(['success'=>false,'error'=>'Datos incompletos o JSON inválido']);
    exit;
}

$id = (int)$data['id'];
$status = $data['status'];

// Validar estados permitidos
$estadosPermitidos = ['todo','doing','review','done'];
if (!in_array($status, $estadosPermitidos)) {
    echo json_encode(['success'=>false,'error'=>'Estado inválido']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE tasks SET status=:status WHERE id=:id");
    $stmt->execute([':status'=>$status, ':id'=>$id]);
    echo json_encode(['success'=>true,'id'=>$id,'new_status'=>$status]);
} catch (Exception $e) {
    echo








