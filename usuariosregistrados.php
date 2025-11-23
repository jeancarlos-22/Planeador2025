<?php
require_once 'config.php';
require_login();
require_role(['admin', 'profesor']);

// --- Eliminar usuario ---
if (isset($_GET['accion'], $_GET['id']) && $_GET['accion'] === 'eliminar') {
    $id = (int)$_GET['id'];
    $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
    header("Location: usuariosregistrados.php");
    exit;
}

// --- Obtener todos los usuarios ---
$stmt = $pdo->query("SELECT id, username, name, email, cargo, role, status, created_at FROM users ORDER BY id DESC");
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Usuarios Registrados — Planeador 2025</title>
<style>
  body {
    font-family: 'Segoe UI', Arial, sans-serif;
    background: linear-gradient(135deg, #6C8EF5, #5563DE);
    margin: 0;
    padding: 40px;
    color: #333;
  }

  h1 {
    text-align: center;
    color: #fff;
    font-size: 36px;
    margin-bottom: 25px;
    letter-spacing: 1px;
    animation: fadeInDown 0.8s ease;
  }

  @keyframes fadeInDown {
    from { opacity: 0; transform: translateY(-15px); }
    to { opacity: 1; transform: translateY(0); }
  }

  .tabla-container {
    background: #fff;
    border-radius: 18px;
    padding: 30px 35px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    max-width: 1100px;
    margin: 0 auto;
    overflow-x: auto;
    animation: fadeIn 0.8s ease;
  }

  @keyframes fadeIn {
    from { opacity: 0; transform: scale(0.98); }
    to { opacity: 1; transform: scale(1); }
  }

  table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
    font-size: 15px;
  }

  th {
    background: #4452d4;
    color: #fff;
    text-transform: uppercase;
    font-weight: 600;
    padding: 14px 10px;
    border-radius: 8px 8px 0 0;
  }

  td {
    padding: 12px 10px;
    border-bottom: 1px solid #e3e6ff;
    text-align: left;
  }

  tr:hover {
    background-color: #f4f6ff;
    transition: background 0.2s ease;
  }

  /* Luz de estado */
  .estado {
    display: flex;
    align-items: center;
    gap: 6px;
    font-weight: 600;
  }

  .foco {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    box-shadow: 0 0 5px rgba(0,0,0,0.15);
  }

  .verde {
    background-color: #22c55e;
    box-shadow: 0 0 10px rgba(34,197,94,0.7);
  }

  .rojo {
    background-color: #ef4444;
    box-shadow: 0 0 10px rgba(239,68,68,0.7);
  }

  /* Botón eliminar */
  .boton-eliminar {
    background: #e63946;
    color: #fff;
    padding: 8px 14px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    display: inline-block;
    transition: all 0.25s ease;
  }

  .boton-eliminar:hover {
    background: #c9202d;
    transform: scale(1.05);
    box-shadow: 0 4px 10px rgba(230,57,70,0.4);
  }

  .volver {
    display: inline-block;
    margin-top: 25px;
    background: #fff;
    color: #4452d4;
    font-weight: 600;
    border: 2px solid #4452d4;
    border-radius: 10px;
    padding: 10px 18px;
    text-decoration: none;
    transition: 0.3s;
  }

  .volver:hover {
    background: #4452d4;
    color: white;
  }
</style>
</head>
<body>

<h1>👥 Gestión de Usuarios</h1>

<div class="tabla-container">
  <table>
    <tr>
      <th>ID</th>
      <th>Usuario</th>
      <th>Nombre</th>
      <th>Email</th>
      <th>Cargo</th>
      <th>Rol</th>
      <th>Estado</th>
      <th>Registrado</th>
      <th>Acción</th>
    </tr>

    <?php foreach ($usuarios as $u): ?>
      <tr>
        <td><?= htmlspecialchars($u['id']) ?></td>
        <td><?= htmlspecialchars($u['username']) ?></td>
        <td><?= htmlspecialchars($u['name']) ?></td>
        <td><?= htmlspecialchars($u['email']) ?></td>
        <td><?= htmlspecialchars($u['cargo']) ?></td>
        <td><?= ucfirst(htmlspecialchars($u['role'])) ?></td>
        <td>
          <div class="estado">
            <?php 
              $estado = strtolower($u['status']);
              if ($estado === 'activo' || $estado === 'active') {
                echo '<span class="foco verde"></span>Activo';
              } else {
                echo '<span class="foco rojo"></span>Inactivo';
              }
            ?>
          </div>
        </td>
        <td><?= htmlspecialchars($u['created_at'] ?? '-') ?></td>
        <td>
          <a href="?accion=eliminar&id=<?= $u['id'] ?>" 
             class="boton-eliminar" 
             onclick="return confirm('⚠️ ¿Seguro que deseas eliminar al usuario <?= htmlspecialchars($u['username']) ?>?');">
             🗑️ Eliminar
          </a>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>
</div>

<div style="text-align:center;">
  <a href="jefe.php" class="volver">⬅️ Volver al Panel</a>
</div>

</body>
</html>

