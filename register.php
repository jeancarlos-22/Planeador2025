<?php
require_once 'config.php';
$success = false;
$message = '';
$userData = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role = trim($_POST['role'] ?? '');
    $cargo = trim($_POST['cargo'] ?? '');

    if ($username && $name && $email && $password && $role && $cargo) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, name, email, password, role, cargo) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$username, $name, $email, $hashed_password, $role, $cargo]);
            $success = true;
            $userData = [
                'Usuario' => $username,
                'Nombre' => $name,
                'Correo' => $email,
                'Rol' => ucfirst($role),
                'Cargo' => $cargo
            ];
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $message = "⚠️ El usuario o correo ya están registrados.";
            } else {
                $message = "❌ Error: " . $e->getMessage();
            }
        }
    } else {
        $message = "Por favor completa todos los campos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Registro de Usuario - Planeador 2025</title>
<style>
  body {
    font-family: 'Segoe UI', Arial, sans-serif;
    background: linear-gradient(135deg, #74ABE2, #5563DE);
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    margin: 0;
    flex-direction: column;
    overflow: hidden;
    color: #333;
  }

  .title {
    position: absolute;
    top: 40px;
    text-align: center;
    font-size: 36px;
    font-weight: 800;
    letter-spacing: 2px;
    background: linear-gradient(90deg, #fff, #ffdd57, #74ABE2, #fff);
    background-size: 300%;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    animation: gradientMove 4s linear infinite, fadeIn 2s ease-in-out;
    text-transform: uppercase;
  }

  @keyframes gradientMove { 0% { background-position: 0% } 100% { background-position: 100% } }
  @keyframes fadeIn { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }

  .register-box {
    background: #fff;
    padding: 30px 40px;
    border-radius: 10px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.2);
    width: 100%;
    max-width: 400px;
    animation: fadeIn 1s ease;
  }

  h2 {
    text-align: center;
    margin-bottom: 25px;
    color: #333;
  }

  input, select {
    width: 100%;
    padding: 10px;
    margin: 8px 0;
    border: 1px solid #ccc;
    border-radius: 6px;
    outline: none;
    font-size: 14px;
    transition: border-color 0.3s;
  }

  input:focus, select:focus {
    border-color: #5563DE;
  }

  button {
    width: 100%;
    padding: 10px;
    background: #5563DE;
    color: #fff;
    font-weight: bold;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: background 0.3s;
  }

  button:hover {
    background: #3e4bc1;
  }

  .error {
    color: #b30000;
    background: #ffe5e5;
    border: 1px solid #ffb3b3;
    padding: 8px 10px;
    border-radius: 6px;
    margin-bottom: 10px;
    font-size: 14px;
  }

  .success-box {
    background: rgba(255,255,255,0.95);
    padding: 30px;
    border-radius: 10px;
    text-align: center;
    box-shadow: 0 0 15px rgba(0,0,0,0.2);
    animation: slideUp 1s ease;
  }

  @keyframes slideUp {
    from { opacity: 0; transform: translateY(40px); }
    to { opacity: 1; transform: translateY(0); }
  }

  .copy-btn {
    background: #4caf50;
    margin-top: 10px;
  }
  .copy-btn:hover { background: #43a047; }

  .login-link {
    text-align: center;
    margin-top: 15px;
  }
  .login-link a {
    color: #5563DE;
    text-decoration: none;
    font-weight: bold;
  }
  .login-link a:hover {
    text-decoration: underline;
  }

  footer {
    position: absolute;
    bottom: 10px;
    width: 100%;
    text-align: center;
    color: #fff;
    font-size: 14px;
  }
</style>
</head>
<body>
<div class="title">Planeador 2025</div>

<div class="register-box">
  <?php if ($success): ?>
    <div class="success-box">
      <h2>🎉 ¡Bienvenido, <?php echo htmlspecialchars($name); ?>!</h2>
      <p>Tu registro fue exitoso. Aquí están tus datos:</p>
      <div id="userData">
        <?php foreach ($userData as $key => $value): ?>
          <p><strong><?php echo $key; ?>:</strong> <?php echo htmlspecialchars($value); ?></p>
        <?php endforeach; ?>
      </div>
      <button class="copy-btn" onclick="copiarDatos()">📋 Copiar datos</button>
      <br><br>
      <a href="index.php" style="color:#5563DE;text-decoration:none;">➡️ Ir al inicio de sesión</a>
    </div>
  <?php else: ?>
    <h2>Registrar usuario</h2>
    <form method="POST">
      <input type="text" name="username" placeholder="Usuario" required>
      <input type="text" name="name" placeholder="Nombre completo" required>
      <input type="email" name="email" placeholder="Correo electrónico" required>
      <input type="password" name="password" placeholder="Contraseña" required>
      <select name="role" required>
        <option value="">Seleccione rol</option>
        <option value="estudiante">👨‍🎓 Estudiante</option>
        <option value="profesor">👨‍🏫 Profesor</option>
        <option value="admin">🛠️ Administrador</option>
      </select>
      <input type="text" name="cargo" placeholder="Cargo" required>
      <button type="submit">Registrar</button>
    </form>
    <?php if ($message): ?>
      <div class="error"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    <div class="login-link">
      <p><a href="index.php">Ya tengo cuenta</a></p>
    </div>
  <?php endif; ?>
</div>

<footer>© 2025 Planeador 2025 — Desarrollado por Jean Carlos P.</footer>

<script>
function copiarDatos() {
    const text = document.getElementById("userData").innerText;
    navigator.clipboard.writeText(text).then(() => {
        alert("✅ Datos copiados al portapapeles");
    });
}
</script>
</body>
</html>












