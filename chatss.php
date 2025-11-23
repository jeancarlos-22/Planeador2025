<?php
require_once 'config.php';
require_login();
require_role(['estudiante','profesor','admin']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>💬 Chats</title>
<style>
body { font-family:'Segoe UI',Arial,sans-serif; background:#f0f2f5; margin:0; padding:20px; }
h1 { color:#1976d2; }
.btn-volver {
  border:none; padding:8px 14px; border-radius:8px; 
  background:linear-gradient(145deg,#1976d2,#1565c0); 
  color:white; font-weight:bold; cursor:pointer; 
  box-shadow:0 2px 6px rgba(0,0,0,0.2);
}
.btn-volver:hover { background:linear-gradient(145deg,#1e88e5,#0d47a1); }
</style>
</head>
<body>
<h1>💬 Centro de Chats</h1>
<p>Aquí iría tu sistema de mensajería interna.</p>

<button class="btn-volver" onclick="window.location='planeador.php'">⬅ Volver al planeador</button>
</body>
</html>

