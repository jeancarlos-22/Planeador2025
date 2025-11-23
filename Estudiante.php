<?php
require_once 'config.php';
require_login();

$user = current_user();
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Panel de Usuario - Planeador 2025</title>
<style>
  body {
    font-family: 'Segoe UI', Arial, sans-serif;
    background: #f5f7fb;
    margin: 0;
    padding: 0;
  }
  header {
    background: #5563DE;
    color: white;
    padding: 15px 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  .user-info {
    font-size: 15px;
  }
  .user-info span {
    font-weight: bold;
  }
  .datetime {
    font-size: 13px;
    opacity: 0.9;
  }
  main {
    padding: 25px;
  }
  h2 {
    color: #333;
  }
  a.button {
    display: inline-block;
    background: #5563DE;
    color: white;
    padding: 10px 20px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
  }
  a.button:hover { background: #3e4bc1; }
</style>
</head>
<body>
  <header>
    <div class="user-info">
      👤 <?php echo escape($user['username']); ?> — <?php echo escape($user['cargo']); ?> — <?php echo ucfirst(escape($user['role'])); ?>
    </div>
    <div class="datetime" id="datetime"></div>
  </header>

  <main>
    <h2>Bienvenido, <?php echo escape($user['name'] ?: $user['username']); ?> 👋</h2>
    <p>Este es tu panel principal del <strong>Planeador Scrumban 2025</strong>.</p>

    <a href="planeador.php" class="button">📋 Ir al Planeador</a>
    <a href="logout.php" class="button" style="background:#d9534f;">🔒 Cerrar sesión</a>
     
      
  </main>

  <script>
    function updateDateTime() {
      const now = new Date();
      const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
      const date = now.toLocaleDateString('es-ES', options);
      const time = now.toLocaleTimeString('es-ES');
      document.getElementById('datetime').textContent = `${date} — ${time}`;
    }
    updateDateTime();
    setInterval(updateDateTime, 1000);
  </script>
</body>
</html>