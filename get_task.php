<?php
require_once 'config.php';
header('Content-Type: application/json');

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
  echo json_encode(['success'=>false,'error'=>'ID inválido']);
  exit;
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'] ?? 'normal';

// Buscar tarea
$stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = :id LIMIT 1");
$stmt->execute([':id'=>$id]);
$task = $stmt->fetch();

if (!$task) {
  echo json_encode(['success'=>false,'error'=>'No encontrada']);
  exit;
}

// Verificar permisos
if ($user_role === 'normal' && $task['created_by'] != $user_id && strpos($task['assigned_to'], (string)$user_id) === false) {
  echo json_encode(['success'=>false,'error'=>'Sin permiso para ver']);
  exit;
}

/* ===================== CONVERTIR IDs A NOMBRES ===================== */
$usernames = '';
if (!empty($task['assigned_to'])) {
  $ids = array_map('intval', explode(',', $task['assigned_to']));
  $in = str_repeat('?,', count($ids) - 1) . '?';
  $q = $pdo->prepare("SELECT username FROM users WHERE id IN ($in)");
  $q->execute($ids);
  $names = $q->fetchAll(PDO::FETCH_COLUMN);
  $usernames = implode(', ', $names);
}

$task['assigned_to'] = $usernames;

echo json_encode(['success'=>true,'task'=>$task]);


