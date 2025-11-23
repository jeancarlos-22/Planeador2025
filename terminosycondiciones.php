<?php
// terminosycondiciones.php
session_start();
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Términos y Condiciones — Aula Virtual 2025</title>
<style>
  body {
    font-family: 'Segoe UI', Arial, sans-serif;
    background: linear-gradient(135deg, #6C8EF5, #5563DE);
    margin: 0;
    color: #333;
    line-height: 1.8;
    padding: 40px 20px;
  }

  .container {
    background: #fff;
    max-width: 850px;
    margin: 40px auto;
    padding: 50px 60px;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    animation: fadeIn 0.6s ease-in-out;
  }

  @keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
  }

  h1 {
    color: #5563DE;
    text-align: center;
    margin-bottom: 25px;
    font-size: 32px;
  }

  h2 {
    color: #444;
    margin-top: 30px;
    font-size: 22px;
    border-left: 5px solid #5563DE;
    padding-left: 10px;
  }

  p {
    margin: 12px 0;
    text-align: justify;
    font-size: 16px;
  }

  strong {
    color: #2c3e50;
  }

  a {
    color: #5563DE;
    text-decoration: none;
    font-weight: bold;
  }

  a:hover {
    text-decoration: underline;
  }

  .intro {
    background: #f7f8ff;
    border-left: 5px solid #5563DE;
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 25px;
  }

  .highlight {
    background: #e9edff;
    padding: 12px 15px;
    border-radius: 8px;
    border-left: 4px solid #6C8EF5;
    margin: 15px 0;
  }

  .back {
    display: block;
    text-align: center;
    margin-top: 35px;
    font-size: 16px;
  }

  footer {
    text-align: center;
    color: #fff;
    margin-top: 40px;
    font-size: 14px;
    opacity: 0.9;
  }
</style>
</head>
<body>
  <div class="container">
    <h1>Términos y Condiciones</h1>

    <div class="intro">
      <p>Bienvenido a <strong>Planeador Virtual 2025</strong>. Al acceder o utilizar esta plataforma, aceptas los siguientes términos y condiciones.  
      Por favor, léelos cuidadosamente antes de continuar.</p>
    </div>

    <div class="highlight">
      <p><strong>Planeador Virtual 2025</strong> fue creada para facilitar la gestión y organización de tareas, actividades y comunicación entre estudiantes y profesores.  
      Su objetivo es mejorar la coordinación, seguimiento y eficiencia académica dentro del planeador virtual.</p>
      <p>Este sistema ha sido desarrollado utilizando diversas tecnologías y lenguajes de programación modernos, tales como <strong>PHP, HTML, CSS, JavaScript y MySQL</strong>.</p>
    </div>

    <h2>1. Uso del servicio</h2>
    <p><strong>Planeador Virtual 2025</strong> está diseñada para estudiantes y profesores que participan en actividades académicas.  
    El uso indebido del sistema, incluyendo el acceso no autorizado, la alteración de información o cualquier intento de vulnerar la seguridad, está estrictamente prohibido.</p>

    <h2>2. Cuentas de usuario</h2>
    <p>Cada usuario es responsable de mantener la confidencialidad de sus credenciales.  
    No compartas tu contraseña con terceros y notifícanos de inmediato cualquier acceso o actividad sospechosa.</p>

    <h2>3. Propiedad intelectual</h2>
    <p>Todo el contenido, diseño, estructura y código fuente de <strong>Planeador Virtual 2025</strong> son propiedad exclusiva de sus desarrolladores.  
    Está protegido por las leyes de derechos de autor y propiedad intelectual.  
    Queda prohibida la copia, modificación o distribución no autorizada del sistema.</p>

    <h2>4. Modificaciones</h2>
    <p>Nos reservamos el derecho de actualizar estos términos y condiciones en cualquier momento.  
    Cualquier modificación será publicada en esta página junto con la fecha de su última actualización.</p>

    <h2>5. Contacto</h2>
    <p>Si tienes dudas, sugerencias o comentarios sobre estos términos, puedes comunicarte con el administrador del aula virtual a través de los canales oficiales de comunicación con estudiantes y profesores.</p>

    <a href="index.php" class="back">⬅ Volver al inicio</a>
  </div>

  <footer>© 2025 Aula Virtual 2025 — Desarrollado para estudiantes y profesores — Todos los derechos reservados.</footer>
</body>
</html>


