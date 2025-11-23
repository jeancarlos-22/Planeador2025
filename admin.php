<?php
require_once 'config.php';
require_login();
require_role(['admin']);
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Panel de Administración</title>
  <style>
    body { font-family: Arial, sans-serif; padding: 20px; background-color: #f8f9fa; }
    h2, h3 { color: #333; }
    table { border-collapse: collapse; width: 100%; margin-top: 10px; background: #fff; }
    th, td { padding: 8px 12px; border: 1px solid #ddd; text-align: left; }
    th { background-color: #f1f1f1; }
    .top-links a { margin-right: 10px; text-decoration: none; color: #007bff; }
    .top-links a:hover { text-decoration: underline; }
  </style>
</head>
<body>
  <h2>Panel de Administración</h2>
  <p>
    Bienvenido, <strong><?php echo escape($_SESSION['name'] ?? $_SESSION['username']); ?></strong>
    <br>
    Cargo: <em><?php echo escape($_SESSION['cargo'] ?? '—'); ?></em>
  </p>
  <div class="top-links">
    <a href="planeador.php">Ir al Planeador</a> |
    <a href="register.php">Registrar nuevo usuario</a> |
    <a href="logout.php" style="color:red;">Cerrar sesión</a>
  </div>

  <h3>Últimos logs del sistema</h3>
  <?php
    $stmt = $pdo->query("
      SELECT al.*, u.username, u.role, u.cargo
      FROM activity_logs al
      LEFT JOIN users u ON u.id = al.user_id
      ORDER BY al.id DESC LIMIT 50
    ");
    $logs = $stmt->fetchAll();
  ?>

  <table>
    <thead>
      <tr>
        <th>Fecha</th>
        <th>Usuario</th>
        <th>Rol</th>
        <th>Cargo</th>
        <th>Acción</th>
        <th>Detalles</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($logs as $l): ?>
        <tr>
          <td><?php echo escape($l['created_at']); ?></td>
          <td><?php echo escape($l['username'] ?? '—'); ?></td>
          <td><?php echo escape($l['role'] ?? '—'); ?></td>
          <td><?php echo escape($l['cargo'] ?? '—'); ?></td>
          <td><?php echo escape($l['action']); ?></td>
          <td><?php echo escape($l['details']); ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</body>
</html>


