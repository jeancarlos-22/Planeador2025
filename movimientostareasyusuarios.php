<?php
require_once 'config.php';
require_login();
require_role(['profesor','admin']); // Solo Jefe y Admin

// --- Obtener registros de actividad de los últimos 7 días ---
$stmt = $pdo->query("
    SELECT a.id, u.username, u.name, u.cargo, a.action, a.details, a.created_at
    FROM activity_logs a
    LEFT JOIN users u ON a.user_id = u.id
    WHERE a.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    ORDER BY a.created_at DESC
");
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- Función para traducir acciones y agregar nombre de tarea ---
function traducirAccion($pdo, $action, $details) {
    return match($action) {
        'move_task' => preg_replace_callback('/Movió tarea ID (\d+) a (\w+) \(posición (\d+)\)/i', function($m) use ($pdo) {
            $taskId = $m[1];
            $estados = [
                'todo' => 'Por hacer',
                'in_progress' => 'En progreso',
                'review' => 'En revisión',
                'done' => 'Hecho'
            ];
            $estado = $estados[$m[2]] ?? $m[2];

            // Obtener nombre de la tarea
            $stmt = $pdo->prepare("SELECT title FROM tasks WHERE id = ?");
            $stmt->execute([$taskId]);
            $task = $stmt->fetch(PDO::FETCH_ASSOC);
            $nombreTarea = $task['title'] ?? 'Sin nombre';

            return "Movió la tarea '{$nombreTarea}' (ID {$taskId}) a {$estado} (posición {$m[3]})";
        }, $details),
        default => $details
    };
}

// --- Agrupar por usuario que realizó la acción ---
$usuarios_logs = [];
foreach($logs as $log) {
    $uid = $log['username'] ?? $log['name'];
    $log['details'] = traducirAccion($pdo, $log['action'], $log['details']); // traducir detalles
    // Reemplazar action para que aparezca en español
    $log['action'] = match($log['action']) {
        'move_task' => 'Movió la tarea',
        default => $log['action']
    };
    $usuarios_logs[$uid][] = $log;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>📊 Movimientos de Usuarios</title>
<style>
body { font-family:'Segoe UI',Arial,sans-serif; background:#f0f2f5; margin:0; padding:20px; }
h1 { text-align:center; color:#5563DE; margin-bottom:30px; }
.board { display:flex; flex-wrap:wrap; gap:20px; justify-content:center; }
.user-card { background:#fff; border-radius:12px; box-shadow:0 6px 15px rgba(0,0,0,0.1); width:300px; padding:15px; transition:transform 0.3s; }
.user-card:hover { transform:translateY(-5px); box-shadow:0 10px 20px rgba(0,0,0,0.15); }
.user-card h2 { margin-top:0; color:#334; font-size:18px; border-bottom:2px solid #5563DE; padding-bottom:5px; text-align:center; }
.log { background:#e8f0fe; margin:8px 0; padding:10px; border-radius:8px; font-size:14px; transition:0.2s; }
.log:hover { background:#d0e2fd; }
.log .user { font-weight:bold; color:#1565c0; display:block; margin-bottom:2px; }
.log .action { font-weight:bold; color:#2e7d32; }
.log .details { display:block; font-style:italic; color:#555; margin-top:2px; }
.log .date { font-size:12px; color:#777; text-align:right; margin-top:3px; }
</style>
</head>
<body>

<h1>📊 Movimientos de Usuarios (Últimos 7 días)</h1>

<div class="board">
<?php foreach($usuarios_logs as $usuario => $movimientos): ?>
    <div class="user-card">
        <h2><?= escape($usuario); ?></h2>
        <?php foreach($movimientos as $log): ?>
            <div class="log">
                <span class="user">👤 <?= escape($log['name'] ?? $log['username']); ?> (<?= escape($log['cargo']); ?>)</span>
                <span class="action"><?= escape($log['action']); ?></span>
                <span class="details"><?= escape($log['details']); ?></span>
                <span class="date"><?= escape($log['created_at']); ?></span>
            </div>
        <?php endforeach; ?>
    </div>
<?php endforeach; ?>

<?php if(empty($usuarios_logs)): ?>
    <p style="text-align:center; color:#777;">No hay movimientos registrados en los últimos 7 días.</p>
<?php endif; ?>
</div>

</body>
</html>





