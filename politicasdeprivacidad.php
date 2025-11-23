<?php
// politicasdeprivacidad.php
session_start();
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Políticas de Privacidad — Aula Virtual 2025</title>
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
    <h1>Políticas de Privacidad</h1>

    <div class="intro">
      <p>En <strong>Planeador Virtual 2025</strong> valoramos tu privacidad y nos comprometemos a proteger la información personal de estudiantes y profesores.  
      Este documento describe cómo recopilamos, usamos y protegemos tus datos al utilizar nuestra plataforma educativa.</p>
    </div>

    <div class="highlight">
      <p><strong>Planeador Virtual 2025</strong> fue desarrollada para facilitar la gestión académica, comunicación y seguimiento de actividades entre estudiantes y profesores.  
      La información gestionada se almacena de forma segura y se utiliza únicamente para fines educativos internos.</p>
      <p>El sistema emplea tecnologías como <strong>PHP, HTML, CSS, JavaScript y MySQL</strong> para garantizar estabilidad, rendimiento y protección de datos.</p>
    </div>

    <h2>1. Información que recopilamos</h2>
    <p>Recopilamos únicamente los datos necesarios para el funcionamiento del sistema, tales como nombre, usuario, rol (estudiante o profesor), asignaturas y actividad académica.  
    No solicitamos información sensible ni financiera.</p>

    <h2>2. Uso de la información</h2>
    <p>Los datos personales se utilizan exclusivamente para permitir el acceso seguro, asignación de tareas, seguimiento académico y comunicación dentro del aula virtual.  
    No compartimos tu información con terceros bajo ninguna circunstancia.</p>

    <h2>3. Seguridad de los datos</h2>
    <p>Implementamos medidas de seguridad técnicas y administrativas para proteger tu información contra accesos no autorizados, pérdida o alteración.  
    Todas las contraseñas son almacenadas mediante <strong>cifrado seguro (password_hash)</strong> y no pueden ser vistas por otros usuarios o administradores.</p>

    <h2>4. Derechos del usuario</h2>
    <p>Estudiantes y profesores tienen derecho a solicitar la revisión o eliminación de su cuenta y datos personales contactando al administrador del aula virtual.  
    Toda solicitud será evaluada conforme a las políticas internas de seguridad y educación.</p>

    <h2>5. Modificaciones</h2>
    <p>Estas políticas pueden ser actualizadas periódicamente para reflejar mejoras en la seguridad o en el funcionamiento del sistema.  
    Las versiones actualizadas se publicarán en esta misma página con su fecha correspondiente.</p>

    <h2>6. Contacto</h2>
    <p>Si tienes preguntas o inquietudes sobre nuestras políticas de privacidad, puedes comunicarte directamente con el administrador del aula virtual o con el equipo de soporte académico.</p>

    <a href="index.php" class="back">⬅ Volver al inicio</a>
  </div>

  <footer>© 2025 Planeador Virtual 2025 — Desarrollado para estudiantes y profesores — Todos los derechos reservados.</footer>
</body>
</html>

