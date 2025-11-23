<?php
require_once 'config.php';
require_login();
require_role(['estudiante','profesor']); // Solo Jefe y Admin

// --- Colores por prioridad ---
function colorPorPrioridad($prioridad) {
    return match (strtolower($prioridad)) {
        'alta' => '#ff8a80',     // rojo
        'media' => '#fff59d',    // amarillo
        'baja' => '#a5d6a7',     // verde
        default => '#e0e0e0',    // gris
    };
}

// --- Obtener tareas activas ---
$stmt = $pdo->query("
    SELECT t.*, u.username, u.name, u.cargo
    FROM tasks t
    LEFT JOIN users u ON t.assigned_to = u.id
    WHERE t.status != 'done'
    ORDER BY t.status ASC, t.due_date ASC
");
$tareas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- Agrupar tareas por estado ---
$estados = ['todo'=>[], 'doing'=>[], 'review'=>[]];
foreach($tareas as $t){
    $estado = $t['status'] ?? 'todo';
    if(isset($estados[$estado])){
        $estados[$estado][] = $t;
    }
}

// --- Función para calcular días restantes ---
function diasRestantes($fecha) {
    $hoy = new DateTime();
    $due = new DateTime($fecha);
    return (int)$due->diff($hoy)->format("%r%a"); // negativo si vencido
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>📋 Tablero de Tareas Activas</title>
<style>
body { font-family:'Segoe UI',Arial,sans-serif; background:#f0f2f5; margin:0; padding:20px; }
header { text-align:center; margin-bottom:20px; }
h1 { color:#5563DE; margin:0; }
main { display:flex; gap:15px; justify-content:center; flex-wrap:wrap; }
.column { background:white; border-radius:12px; width:300px; padding:15px; box-shadow:0 6px 12px rgba(0,0,0,0.1); min-height:400px; }
.column h2 { text-align:center; border-bottom:2px solid #5563DE; color:#334; padding-bottom:5px; margin-bottom:10px; }
.task { border-radius:10px; padding:12px; margin:10px 0; box-shadow:0 4px 6px rgba(0,0,0,0.1); transition:all 0.3s ease; position:relative; border-left:6px solid #5563DE; }
.task:hover { transform:scale(1.03); box-shadow:0 8px 12px rgba(0,0,0,0.15); }
.task strong { display:block; font-size:15px; margin-bottom:4px; }
.task p { margin:5px 0; font-size:13px; color:#333; }
.task small { display:block; font-size:12.5px; color:#444; line-height:1.4; }
.priority { padding:3px 8px; border-radius:8px; color:white; font-weight:600; font-size:12px; }
.urgente { border-left:6px solid #ff5252 !important; }
</style>
</head>
<body>

<header>
    <h1>📋 Tablero de Tareas Activas</h1>
    <p>Mostrando tareas por estado y prioridad. Las tareas con borde rojo están próximas a vencer (≤2 días).</p>
</header>

<main>
<?php foreach($estados as $estado => $tareas_estado): ?>
<div class="column">
    <h2>
        <?= match($estado) {
            'todo' => '🕓 Por hacer',
            'doing' => '⚙️ En progreso',
            'review' => '🔍 En revisión',
        }; ?>
    </h2>
    <?php foreach($tareas_estado as $t): 
        $colorPrioridad = colorPorPrioridad($t['priority'] ?? 'media');
        $dias = diasRestantes($t['due_date'] ?? date('Y-m-d'));
        $urgenteClass = ($dias <= 2) ? 'urgente' : '';
    ?>
    <div class="task <?= $urgenteClass; ?>" style="background:<?= $colorPrioridad; ?>;">
        <strong><?= escape($t['title']); ?></strong>
        <p><?= escape($t['description']); ?></p>
        <small>
            <b>📅 Fecha límite:</b> <?= escape($t['due_date'] ?? 'Sin definir'); ?><br>
            <b>Días restantes:</b> <?= $dias; ?><br>
            <b>👥 Asignado a:</b> <?= escape($t['name'] ?? $t['username']); ?> — <?= escape($t['cargo']); ?><br>
            <b>⚡ Prioridad:</b> <?= ucfirst(escape($t['priority'] ?? 'Media')); ?>
        </small>
    </div>
    <?php endforeach; ?>
    <?php if(count($tareas_estado)===0): ?>
        <p style="text-align:center; color:#999; margin-top:15px;">No hay tareas activas.</p>
    <?php endif; ?>
</div>
<?php endforeach; ?>
</main>

</body>
</html>





