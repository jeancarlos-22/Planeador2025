<?php
require_once 'config.php';
require_login();
require_role(['Profesor','admin']);
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Panel Jefe 🚀</title>
<style>
  body {
    font-family: 'Segoe UI', Arial, sans-serif;
    background: linear-gradient(135deg, #74ABE2, #5563DE);
    color: #333;
    margin: 0;
    padding: 0;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    overflow: hidden;
  }

  .datetime {
    position: absolute;
    top: 15px;
    right: 20px;
    color: #fff;
    font-weight: 500;
    background: rgba(0,0,0,0.2);
    padding: 8px 12px;
    border-radius: 8px;
    font-size: 14px;
  }

  .container {
    background: #fff;
    padding: 35px 45px;
    border-radius: 12px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.2);
    text-align: center;
    width: 100%;
    max-width: 450px;
    animation: fadeIn 1s ease-in-out;
  }

  @keyframes fadeIn {
    from {opacity: 0; transform: translateY(-15px);}
    to {opacity: 1; transform: translateY(0);}
  }

  h2 {
    color: #334;
    margin-bottom: 15px;
    font-size: 26px;
    border-bottom: 3px solid #5563DE;
    display: inline-block;
    padding-bottom: 5px;
  }

  p {
    margin: 12px 0;
    font-size: 16px;
  }

  a {
    color: #5563DE;
    font-weight: 600;
    text-decoration: none;
    transition: color 0.3s;
  }

  a:hover {
    color: #3f4dc0;
    text-decoration: underline;
  }

  footer {
    position: absolute;
    bottom: 15px;
    color: #fff;
    font-size: 14px;
    letter-spacing: 0.5px;
    opacity: 0.9;
  }

  /* Encabezado animado */
  .title {
    position: absolute;
    top: 30px;
    font-size: 34px;
    font-weight: 800;
    letter-spacing: 1.5px;
    background: linear-gradient(90deg, #fff, #ffdd57, #74ABE2, #fff);
    background-size: 300%;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    animation: gradientMove 4s linear infinite;
    text-transform: uppercase;
  }

  @keyframes gradientMove {
    0% { background-position: 0%; }
    100% { background-position: 100%; }
  }
</style>
</head>
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
<link rel="manifest" href="/site.webmanifest">

<body>

  <!-- 🔹 Fecha y hora -->
  <div class="datetime" id="datetime"></div>

  <!-- 🔹 Título animado -->
  <div class="title">Planeador 2025  </div>

  <div class="container">
    <h2>Panel Jefe </h2>
    <p>👋 Bienvenido, <strong><?php echo escape($_SESSION['name'] ?? $_SESSION['username']); ?></strong></p>
    <p>
       
        
         
     <div style="font-family: Arial, sans-serif; display:flex; flex-direction:column; gap:10px;">

         
         
  <a href="comunicaciones.php" target="_blank" style="text-decoration:none; font-size:16px;">🗣️📨 <strong>Comunicaciones</strong></a>       
  <a href="rendimiento.php" target="_blank" style="text-decoration:none; font-size:16px;">💪🧠 <strong>Rendimiento</strong></a>
  <a href="movimientostareasyusuarios.php" target="_blank" style="text-decoration:none; font-size:16px;">📝👥 <strong>Movimientos de usuarios en las tareas</strong></a>
  <a href="estadotareas.php" target="_blank" style="text-decoration:none; font-size:16px;">📊📌 <strong>Estados de las Tareas</strong></a>  
  <a href="usuariosregistrados.php" target="_blank" style="text-decoration:none; font-size:16px;">👤📋 <strong>Usuarios Registrados</strong></a>
  <a href="planeador.php" target="_blank" style="text-decoration:none; font-size:16px;">🚀📋 <strong>Ir a Planeador</strong></a>
  <a href="logout.php" style="text-decoration:none; font-size:16px;">🚪🔒 <strong>Cerrar sesión</strong></a>
</div>

        
        
    </p>
  </div>

  <footer>Planeador 2025 — Derechos Reservados</footer>

  <script>
    // 🔸 Mostrar fecha y hora dinámica
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
