<?php
require_once 'config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  exit(json_encode(['success' => false, 'error' => 'Método no permitido']));
}

$body = json_decode(file_get_contents('php://input'), true);
if (!$body) $body = $_POST;

// Verificar CSRF token
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $body['csrf_token'] ?? '')) {
  echo json_encode(['success'=>false,'error'=>'Token inválido']); 
  exit;
}

$title = trim($body['title'] ?? '');
$description = trim($body['description'] ?? '');
$priority = in_array($body['priority'] ?? 'medium', ['low','medium','high']) ? $body['priority'] : 'medium';
$due_date = !empty($body['due_date']) ? $body['due_date'] : null;
$status = $body['status'] ?? 'todo';
$creator = $_SESSION['user_id'];
$user_role = $_SESSION['role'] ?? 'normal';

if ($title === '') { 
  echo json_encode(['success'=>false,'error'=>'Título requerido']); 
  exit; 
}

/* ===================== ASIGNACIÓN MÚLTIPLE ===================== */
// Permite escribir o recibir varios nombres (por coma o espacio)
$assigned_field = $body['assigned_to'] ?? '';
$assigned_usernames = [];

if (is_array($assigned_field)) {
  $assigned_usernames = array_filter(array_map('trim', $assigned_field));
} else {
  $assigned_usernames = array_filter(array_map('trim', preg_split('/[, ]+/', $assigned_field)));
}

$assigned_ids = [];
$assigned_display_names = [];

if (!empty($assigned_usernames)) {
  $placeholders = str_repeat('?,', count($assigned_usernames) - 1) . '?';
  $stmt = $pdo->prepare("SELECT id, username, name, cargo FROM users WHERE username IN ($placeholders)");
  $stmt->execute($assigned_usernames);
  $users_found = $stmt->fetchAll(PDO::FETCH_ASSOC);

  foreach ($users_found as $user) {
    $assigned_ids[] = $user['id'];
    $nombre = $user['name'] ?: $user['username'];
    $cargo = $user['cargo'] ?: 'Sin cargo';
    $assigned_display_names[] = "{$nombre} - {$cargo}";
  }

  // Si el usuario es "normal", solo puede asignarse a sí mismo
  if ($user_role === 'normal') {
    $assigned_ids = [$creator];
    $stmt = $pdo->prepare("SELECT name, cargo FROM users WHERE id = ?");
    $stmt->execute([$creator]);
    $self = $stmt->fetch(PDO::FETCH_ASSOC);
    $assigned_display_names = [$self['name'] . " - " . ($self['cargo'] ?? 'Sin cargo')];
  }
} else {
  // Si no hay asignación, por defecto al creador
  $assigned_ids = [$creator];
  $stmt = $pdo->prepare("SELECT name, cargo FROM users WHERE id = ?");
  $stmt->execute([$creator]);
  $self = $stmt->fetch(PDO::FETCH_ASSOC);
  $assigned_display_names = [$self['name'] . " - " . ($self['cargo'] ?? 'Sin cargo')];
}

// Guardar IDs como texto "1,2,3" y nombres visibles "Jean - Cargo, Ana - Cargo"
$assigned_to = implode(',', $assigned_ids);
$assigned_to_display = implode(', ', $assigned_display_names);

/* ===================== ORDEN Y CREACIÓN ===================== */
$stmt = $pdo->prepare("SELECT COALESCE(MAX(order_index),0) + 1 FROM tasks WHERE status = :status");
$stmt->execute([':status'=>$status]);
$orderIndex = (int)$stmt->fetchColumn();

$ins = $pdo->prepare("
  INSERT INTO tasks (title, description, priority, due_date, assigned_to, assigned_display, status, order_index, created_by) 
  VALUES (:title, :desc, :prio, :due, :assigned, :assigned_display, :status, :ord, :creator)
");

$ins->execute([
  ':title'   => $title,
  ':desc'    => $description,
  ':prio'    => $priority,
  ':due'     => $due_date,
  ':assigned'=> $assigned_to,
  ':assigned_display'=> $assigned_to_display,
  ':status'  => $status,
  ':ord'     => $orderIndex,
  ':creator' => $creator
]);

$taskId = $pdo->lastInsertId();
log_action($pdo, $creator, 'create_task', "Tarea {$taskId}: {$title} (Asignada a {$assigned_to_display})");

/* ===================== NOTIFICACIONES ===================== */
$msgTitle = "Nueva tarea: {$title}";
$msgText = "Usuario {$_SESSION['username']} creó la tarea **{$title}** asignada a **{$assigned_to_display}** en **{$status}**. Prioridad: {$priority}";
notify_teams($msgTitle, $msgText);

// Notificar por correo a todos los asignados
if (!empty($assigned_ids)) {
  $in = str_repeat('?,', count($assigned_ids) - 1) . '?';
  $s = $pdo->prepare("SELECT email FROM users WHERE id IN ($in)");
  $s->execute($assigned_ids);
  foreach ($s->fetchAll(PDO::FETCH_ASSOC) as $u) {
    if (!empty($u['email'])) {
      notify_email($u['email'], $msgTitle, "<p>{$msgText}</p><p>ID: {$taskId}</p>");
    }
  }
}

// Notificar al admin si aplica
if (defined('NOTIFY_ADMIN_EMAIL') && NOTIFY_ADMIN_EMAIL) {
  notify_email(NOTIFY_ADMIN_EMAIL, $msgTitle, $msgText);
}

echo json_encode(['success'=>true,'id'=>$taskId]);



