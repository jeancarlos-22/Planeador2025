<?php
require_once 'config.php';
require_login();
require_role(['estudiante','admin']); // Solo Jefe o Admin

// --- Configurar fecha (sin usar strftime) ---
date_default_timezone_set('America/Bogota');

$formatter = new IntlDateFormatter(
    'es_CO', // Español Colombia
    IntlDateFormatter::FULL, // Ejemplo: jueves, 23 de octubre de 2025
    IntlDateFormatter::NONE,
    'America/Bogota',
    IntlDateFormatter::GREGORIAN
);
$fecha_larga = ucfirst($formatter->format(new DateTime()));
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>📊 Rendimiento de Usuarios</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<style>
body {
  font-family: 'Poppins', 'Segoe UI', Arial, sans-serif;
  background: linear-gradient(135deg, #e9efff, #ffffff);
  margin: 0;
  padding: 30px;
}
h1 {
  text-align: center;
  color: #2a2a72;
  font-weight: 700;
}
#reporte {
  background: white;
  border-radius: 16px;
  padding: 25px;
  box-shadow: 0 6px 15px rgba(0,0,0,0.08);
  animation: fadeIn 0.7s ease-in-out;
}
.header-info {
  text-align: center;
  margin-bottom: 10px;
  color: #555;
}
#hora-actual {
  font-weight: bold;
  color: #2a2a72;
  font-size: 1.1em;
  animation: parpadeo 1s infinite;
}
@keyframes parpadeo {
  50% { opacity: 0.6; }
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}
button {
  display: block;
  margin: 20px auto;
  background: linear-gradient(135deg, #5563DE, #3643B3);
  color: white;
  padding: 12px 25px;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  font-size: 1em;
  transition: transform 0.3s, background 0.3s;
}
button:hover {
  transform: scale(1.05);
  background: linear-gradient(135deg, #3643B3, #5563DE);
}
table {
  border-collapse: collapse;
  width: 100%;
  margin-top: 20px;
  border-radius: 10px;
  overflow: hidden;
}
th, td {
  padding: 12px 15px;
  text-align: center;
}
th {
  background: #2a2a72;
  color: white;
}
tr:nth-child(even) {
  background-color: #f3f4f8;
}
tr:hover {
  background-color: #eef1ff;
}
td strong {
  color: #3643B3;
}
canvas {
  max-width: 800px;
  margin: 25px auto;
  display: block;
}
footer {
  text-align: center;
  margin-top: 30px;
  color: #888;
  font-size: 0.9em;
}
</style>
</head>
<body>

<h1>📊 Rendimiento de los Usuarios</h1>

<div class="header-info">
  <strong>📅 Reporte emitido el:</strong><br>
  <?= $fecha_larga ?> — <span id="hora-actual"></span> (hora Colombia)
</div>

<button id="pdfBtn">📄 Descargar PDF</button>

<div id="reporte">
  <h2 style="text-align:center; color:#2a2a72;">Resumen general de desempeño</h2>
  <p style="text-align:center; font-size:0.95em; color:#555;">
    Reporte emitido el <?= $fecha_larga ?> — <span id="hora-actual-dos"></span> (hora Colombia)
  </p>

  <canvas id="grafico"></canvas>

  <?php
  // --- Consultar rendimiento general de usuarios ---
  $query = "
      SELECT 
          u.id,
          u.username,
          u.name,
          u.cargo,
          COUNT(t.id) AS total_tareas,
          SUM(CASE WHEN t.status = 'todo' THEN 1 ELSE 0 END) AS pendientes,
          SUM(CASE WHEN t.status = 'doing' THEN 1 ELSE 0 END) AS en_progreso,
          SUM(CASE WHEN t.status = 'review' THEN 1 ELSE 0 END) AS en_revision,
          SUM(CASE WHEN t.status = 'done' THEN 1 ELSE 0 END) AS completadas
      FROM users u
      LEFT JOIN tasks t ON u.id = t.assigned_to
      GROUP BY u.id, u.username, u.name, u.cargo
  ";
  $stmt = $pdo->query($query);
  $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

  foreach ($usuarios as &$u) {
      $total = max(1, $u['total_tareas']);
      $u['porcentaje'] = round(($u['completadas'] / $total) * 100, 1);
  }
  unset($u);
  usort($usuarios, fn($a, $b) => $b['porcentaje'] <=> $a['porcentaje']);
  ?>

  <table>
    <tr>
      <th>🏅 Ranking</th>
      <th>Usuario</th>
      <th>Nombre</th>
      <th>Cargo</th>
      <th>Total</th>
      <th>Pendientes</th>
      <th>En progreso</th>
      <th>En revisión</th>
      <th>Completadas ✅</th>
      <th>Rendimiento (%)</th>
    </tr>
    <?php 
    $rank = 1;
    foreach ($usuarios as $u): 
        $medal = $rank == 1 ? '🥇' : ($rank == 2 ? '🥈' : ($rank == 3 ? '🥉' : ''));
    ?>
    <tr>
      <td><?= $rank ?> <?= $medal ?></td>
      <td><?= htmlspecialchars($u['username']) ?></td>
      <td><?= htmlspecialchars($u['name']) ?></td>
      <td><?= htmlspecialchars($u['cargo']) ?></td>
      <td><?= $u['total_tareas'] ?></td>
      <td><?= $u['pendientes'] ?></td>
      <td><?= $u['en_progreso'] ?></td>
      <td><?= $u['en_revision'] ?></td>
      <td><?= $u['completadas'] ?></td>
      <td><strong><?= $u['porcentaje'] ?>%</strong></td>
    </tr>
    <?php $rank++; endforeach; ?>
  </table>

  <footer>🔒 Reporte generado automáticamente — Solo para Jefes y Administradores</footer>
</div>

<script>
// 🕒 Hora en vivo
function actualizarHora() {
  const ahora = new Date();
  let horas = ahora.getHours();
  const minutos = String(ahora.getMinutes()).padStart(2, '0');
  const segundos = String(ahora.getSeconds()).padStart(2, '0');
  const ampm = horas >= 12 ? 'pm' : 'am';
  horas = horas % 12 || 12;
  const horaCompleta = `${horas}:${minutos}:${segundos} ${ampm}`;
  document.getElementById('hora-actual').textContent = horaCompleta;
  document.getElementById('hora-actual-dos').textContent = horaCompleta;
}
setInterval(actualizarHora, 1000);
actualizarHora();

// 🎨 Gráfico con degradado
const ctx = document.getElementById('grafico');
const nombres = <?= json_encode(array_column($usuarios, 'username')) ?>;
const rendimiento = <?= json_encode(array_column($usuarios, 'porcentaje')) ?>;

const gradient = ctx.getContext('2d').createLinearGradient(0, 0, 0, 400);
gradient.addColorStop(0, '#5563DE');
gradient.addColorStop(0.5, '#6C8EF5');
gradient.addColorStop(1, '#89A7FF');

new Chart(ctx, {
  type: 'bar',
  data: {
    labels: nombres,
    datasets: [{
      label: 'Rendimiento (%)',
      data: rendimiento,
      backgroundColor: gradient,
      borderRadius: 8,
      borderSkipped: false
    }]
  },
  options: {
    responsive: true,
    animation: {
      duration: 1500,
      easing: 'easeOutBounce'
    },
    scales: {
      y: { beginAtZero: true, max: 100, ticks: { color: '#3643B3' } },
      x: { ticks: { color: '#3643B3' }, grid: { display: false } }
    },
    plugins: {
      legend: { display: false },
      tooltip: {
        backgroundColor: '#3643B3',
        titleColor: '#fff',
        bodyColor: '#fff',
        cornerRadius: 10,
        displayColors: false
      }
    }
  }
});

// 💾 Descargar PDF con hora y fecha
document.getElementById('pdfBtn').addEventListener('click', async () => {
  const { jsPDF } = window.jspdf;
  const pdf = new jsPDF('p', 'pt', 'a4');
  const reporte = document.getElementById('reporte');

  await html2canvas(reporte, { scale: 2 }).then(canvas => {
    const img = canvas.toDataURL('image/png');
    const imgProps = pdf.getImageProperties(img);
    const pdfWidth = pdf.internal.pageSize.getWidth();
    const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;
    pdf.addImage(img, 'PNG', 0, 0, pdfWidth, pdfHeight);

    const ahora = new Date();
    const dia = String(ahora.getDate()).padStart(2, '0');
    const mes = String(ahora.getMonth() + 1).padStart(2, '0');
    const año = ahora.getFullYear();
    let horas = ahora.getHours();
    const minutos = String(ahora.getMinutes()).padStart(2, '0');
    const ampm = horas >= 12 ? 'pm' : 'am';
    horas = horas % 12 || 12;
    const hora_formato = `${horas}-${minutos}${ampm}`;
    const nombreArchivo = `reporte_rendimiento_${dia}-${mes}-${año}_${hora_formato}.pdf`;

    pdf.save(nombreArchivo);
  });
});
</script>
</body>
</html>







