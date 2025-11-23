<?php
require_once 'config.php';
require_login();

/* ============================================================
   1️⃣  PROCESAR EL ENVÍO DE FORMULARIO (PETICIÓN AJAX)
   ============================================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    // Validar token CSRF
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        echo json_encode(['success' => false, 'error' => 'Token CSRF inválido.']);
        exit;
    }

    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $priority = $_POST['priority'] ?? 'Media';
    $due_date = $_POST['due_date'] ?? null;
    $assigned_to_usernames = $_POST['assigned_to'] ?? [];

    if ($title === '' || $description === '') {
        echo json_encode(['success' => false, 'error' => 'Faltan campos obligatorios.']);
        exit;
    }

    // Obtener username actual
    $current_username_stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
    $current_username_stmt->execute([$_SESSION['user_id']]);
    $current_username = $current_username_stmt->fetchColumn();

    // Convertir usernames a IDs
    $assigned_to_ids = [];
    if (!empty($assigned_to_usernames)) {
        $in = str_repeat('?,', count($assigned_to_usernames) - 1) . '?';
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username IN ($in)");
        $stmt->execute($assigned_to_usernames);
        $assigned_to_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // Insertar la tarea
    $stmt = $pdo->prepare("
        INSERT INTO tasks (title, description, priority, due_date, assigned_to, status, created_by, created_at)
        VALUES (:title, :desc, :priority, :due_date, :assigned_to, 'todo', :created_by, NOW())
    ");
    $stmt->execute([
        ':title' => $title,
        ':desc' => $description,
        ':priority' => $priority,
        ':due_date' => $due_date ?: null,
        ':assigned_to' => implode(',', $assigned_to_ids),
        ':created_by' => $_SESSION['user_id']
    ]);

    log_action($pdo, $_SESSION['user_id'], 'create_task', "Tarea creada: $title");

    // Retornar éxito + fecha y título para abrir calendario
    echo json_encode([
        'success' => true,
        'title' => $title,
        'description' => $description,
        'due_date' => $due_date
    ]);
    exit;
}

/* ============================================================
   2️⃣  MOSTRAR EL FORMULARIO
   ============================================================ */
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Info del usuario logueado
$user_name  = htmlspecialchars($_SESSION['name'] ?? $_SESSION['username']);
$user_cargo = htmlspecialchars($_SESSION['cargo'] ?? 'Sin cargo');
$user_role  = ucfirst(htmlspecialchars($_SESSION['role'] ?? 'Normal'));

function colorRol($rol) {
    $rol = strtolower($rol ?? '');
    return match ($rol) {
        'admin' => '#e74c3c',
        'jefe'  => '#e67e22',
        default => '#27ae60',
    };
}
$role_color = colorRol($_SESSION['role'] ?? 'normal');

$stmt = $pdo->query("SELECT id, username, name, cargo, role FROM users ORDER BY name ASC");
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

date_default_timezone_set('America/Bogota');

$diasSemana = ['domingo','lunes','martes','miércoles','jueves','viernes','sábado'];
$meses = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];

$diaSemana = $diasSemana[(int)date('w')];
$diaNum = date('d');
$mes = $meses[(int)date('n') - 1];
$anio = date('Y');

$fecha_sin_hora = "$diaSemana, $diaNum de $mes de $anio";
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Crear Tarea</title>
<style>
body { font-family: 'Segoe UI', Arial, sans-serif; background: linear-gradient(135deg, #74ABE2, #5563DE); margin: 0; padding: 30px; min-height: 100vh; display: flex; justify-content: center; align-items: flex-start; }
.container { background: #fff; border-radius: 10px; box-shadow: 0 8px 20px rgba(0,0,0,0.2); max-width: 600px; width: 100%; padding: 30px 40px; }
h2 { text-align: center; margin-bottom: 30px; font-weight: 800; font-size: 32px; letter-spacing: 1.5px; color: #333; text-transform: uppercase; background: linear-gradient(90deg, #fff, #ffdd57, #74ABE2, #fff); background-size: 300%; -webkit-background-clip: text; -webkit-text-fill-color: transparent; animation: gradientMove 4s linear infinite, fadeIn 2s ease-in-out; }
@keyframes gradientMove {0% { background-position: 0% }100% { background-position: 100% }}
@keyframes fadeIn {from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); }}
.user-info { text-align: center; font-size: 16px; margin-bottom: 30px; color: #333; }
.user-info span.role { font-weight: 700; }
label { display: block; font-weight: 600; color: #333; margin-top: 20px; margin-bottom: 8px; }
input[type="text"], input[type="date"], select, textarea { width: 100%; padding: 12px 14px; font-size: 15px; border-radius: 8px; border: 1.5px solid #ccc; transition: border-color 0.3s ease; font-family: 'Segoe UI', Arial, sans-serif; box-sizing: border-box; }
textarea { resize: vertical; min-height: 80px; }
input[type="text"]:focus, input[type="date"]:focus, select:focus, textarea:focus { outline: none; border-color: #5563DE; box-shadow: 0 0 6px rgba(85, 99, 222, 0.5); }
button { margin-top: 30px; background: #5563DE; border: none; color: #fff; font-weight: 700; padding: 14px 0; border-radius: 8px; font-size: 18px; cursor: pointer; transition: background 0.3s ease; width: 100%; text-transform: uppercase; letter-spacing: 1px; }
button:hover { background: #3e4bc1; }
a { color: #5563DE; text-decoration: none; font-weight: 700; }
a:hover { text-decoration: underline; }
option.admin { color: #e74c3c; font-weight: 700; }
option.jefe { color: #e67e22; font-weight: 700; }
option.normal { color: #27ae60; font-weight: 700; }
.current-date-time { position: fixed; top: 15px; right: 25px; font-family: 'Segoe UI', Arial, sans-serif; font-size: 14px; font-weight: 600; color: #fff; background: rgba(0,0,0,0.25); padding: 8px 14px; border-radius: 12px; box-shadow: 0 1px 5px rgba(0,0,0,0.3); user-select: none; z-index: 1000; }
select[multiple] { height: 150px; }
</style>

<!-- Flatpickr CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
</head>
<body>

<div class="current-date-time" id="fechaHora"><?php echo $fecha_sin_hora; ?> — <span id="hora">00:00:00</span></div>

<div class="container">
  <h2>➕ Crear Nueva Tarea</h2>

  <div class="user-info">
      👤 <strong><?php echo $user_name; ?></strong> — 
      <?php echo $user_cargo; ?> — 
      <span class="role" style="color:<?php echo $role_color; ?>">
          <?php echo $user_role; ?>
      </span>
  </div>

  <form id="taskForm">
    <label>Título</label>
    <input type="text" name="title" required placeholder="Colocale Un Titulo A la Tarea">

    <label>Descripción</label>
    <textarea name="description" rows="3" required placeholder="Colocale Una Descripción "></textarea>

    <label>Prioridad</label>
    <select name="priority">
      <option value="Baja">Baja 🟢</option>
      <option value="Media" selected>Media 🟠</option>
      <option value="Alta">Alta 🔴</option>
    </select>

    <label>Fecha límite</label>
    <input type="text" id="due_date" name="due_date" placeholder="Selecciona fecha límite">

    <label>Asigna El Usuario</label>
    <select name="assigned_to[]" multiple size="6">
      <?php foreach ($usuarios as $u): 
          $rol = ucfirst($u['role']);
          $cargo = $u['cargo'] ?: 'Sin cargo';
          $nombre = htmlspecialchars($u['name'] ?: $u['username']);
          $display = "$nombre — $cargo — $rol";
          $claseRol = strtolower($u['role']);
      ?>
        <option value="<?php echo htmlspecialchars($u['username']); ?>" class="<?php echo $claseRol; ?>">
          <?php echo htmlspecialchars($display); ?>
        </option>
      <?php endforeach; ?>
    </select>

    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <button type="submit">Guardar Tarea</button>
  </form>

  <p style="text-align:center;margin-top:15px;">
    <a href="planeador.php">⬅️ Volver al tablero</a>
  </p>
</div>

<!-- JS -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
function actualizarHora() {
    const ahora = new Date();
    const opcionesHora = { hour12: false, hour: '2-digit', minute: '2-digit', second: '2-digit', timeZone: 'America/Bogota' };
    const horaFormateada = new Intl.DateTimeFormat('es-CO', opcionesHora).format(ahora);
    document.getElementById('hora').textContent = horaFormateada;
}
setInterval(actualizarHora, 1000);
actualizarHora();

document.getElementById('taskForm').addEventListener('submit', async e => {
  e.preventDefault();
  const form = e.target;
  const data = new FormData(form);

  const res = await fetch('crear_tarea.php', {
    method: 'POST',
    body: data
  });

  const result = await res.json();
  if (result.success) {
    alert('✅ Tarea creada correctamente');

    const title = encodeURIComponent(result.title);
    const desc  = encodeURIComponent(result.description);
    const due   = result.due_date;

    if (due) {
        const start = due + 'T06:00:00';
        const end   = due + 'T19:00:00';
        const url = `https://outlook.office.com/calendar/deeplink/compose?subject=${title}&body=${desc}&startdt=${start}&enddt=${end}`;
        window.open(url, '_blank');
    }

    window.location.href = 'planeador.php';
  } else {
    alert('❌ Error: ' + result.error);
  }
});

// Festivos Colombia 2025
const festivos = [
  '2025-01-01','2025-01-06','2025-03-24','2025-04-17','2025-04-18',
  '2025-05-01','2025-06-02','2025-06-23','2025-06-30','2025-07-20',
  '2025-08-07','2025-08-18','2025-10-13','2025-11-03','2025-11-17',
  '2025-12-08','2025-12-25'
];

// Inicializar Flatpickr en due_date
flatpickr("#due_date", {
    dateFormat: "Y-m-d",
    disable: festivos, // no se pueden seleccionar días festivos
    onDayCreate: function(dObj, dStr, fp, dayElem) {
        const date = dayElem.dateObj;
        const dateStr = date.toISOString().split('T')[0];
        if (festivos.includes(dateStr)) {
            dayElem.style.backgroundColor = "#FFCDD2"; // resalta festivos en rojo
            dayElem.style.color = "#000";               // color de texto
            dayElem.title = "Festivo en Colombia";      // tooltip
        }
    }
});
</script>

</body>
</html>





