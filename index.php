<?php
// index.php (login)
require_once 'config.php';

if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['role'] ?? 'estudiante';
    if ($role === 'admin') header('Location: admin.php');
    if ($role === 'profesor') header('Location: profesor.php');
    header('Location: planeador.php');
    exit;
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token inválido.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        if ($username === '' || $password === '') {
            $errors[] = 'Usuario y contraseña requeridos.';
        } else {
            $stmt = $pdo->prepare("
                SELECT id, username, password, role, name, cargo
                FROM users
                WHERE username = :u
                LIMIT 1
            ");
            $stmt->execute([':u' => $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['name'] = $user['name'] ?? '';
                $_SESSION['cargo'] = $user['cargo'] ?? '';
                $_SESSION['role'] = strtolower($user['role']); // evita errores de acceso

                log_action($pdo, $user['id'], 'login', 'Inicio de sesión exitoso');

                if ($_SESSION['role'] === 'admin') header('Location: admin.php');
                elseif ($_SESSION['role'] === 'profesor') header('Location: profesor.php');
                else header('Location: planeador.php');
                exit;
            } else {
                log_action($pdo, null, 'login_failed', "Usuario: {$username}");
                $errors[] = 'Credenciales inválidas.';
            }
        }
    }
}
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Planeador 2025 🚀</title>
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

  .login-box {
    background: #fff;
    padding: 30px 40px;
    border-radius: 10px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.2);
    width: 100%;
    max-width: 380px;
  }

  h2 { text-align: center; margin-bottom: 25px; color: #333; }
  label { display: block; font-weight: 600; color: #333; margin-bottom: 5px; }

  input[type="text"], input[type="password"] {
    width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px;
    margin-bottom: 15px; font-size: 14px; transition: border-color 0.3s;
  }
  input[type="text"]:focus, input[type="password"]:focus { border-color: #5563DE; outline: none; }

  button {
    width: 100%; padding: 10px; background: #5563DE; color: #fff; font-weight: bold;
    border: none; border-radius: 6px; cursor: pointer; transition: background 0.3s;
  }
  button:hover { background: #3e4bc1; }

  .error {
    color: #b30000; background: #ffe5e5; border: 1px solid #ffb3b3;
    padding: 8px 10px; border-radius: 6px; margin-bottom: 10px; font-size: 14px;
  }

  .register-link { text-align: center; margin-top: 15px; }
  .register-link a { color: #5563DE; text-decoration: none; font-weight: bold; }
  .register-link a:hover { text-decoration: underline; }

  .extra-links {
    text-align: center;
    margin-top: 10px;
    font-size: 13px;
  }

  .extra-links a {
    color: #5563DE;
    text-decoration: none;
    margin: 0 5px;
  }

  .extra-links a:hover {
    text-decoration: underline;
  }

  footer {
    position: absolute; bottom: 10px; width: 100%; text-align: center;
    color: #fff; font-size: 14px; letter-spacing: 0.5px; font-weight: 500; opacity: 0.9;
  }
</style>
</head>
<body>
  <div class="datetime" id="datetime"></div>
  <div class="title">Planeador 2025</div>

  <div class="login-box">
    <h2>Iniciar sesión</h2>
    <?php foreach ($errors as $e): ?>
      <div class="error"><?php echo htmlspecialchars($e); ?></div>
    <?php endforeach; ?>
    <form method="post" action="">
      <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
      <label>Usuario:</label>
      <input type="text" name="username" required>
      <label>Contraseña:</label>
      <input type="password" name="password" required>
      <button type="submit">Entrar</button>
    </form>
    <div class="register-link">
      <p><a href="register.php">Crear cuenta</a></p>
    </div>

    <!-- 🔹 NUEVO: Enlaces de Términos y Políticas -->
    <div class="extra-links">
      <a href="terminosycondiciones.php" target="_blank">Términos y condiciones</a> |
      <a href="politicasdeprivacidad.php" target="_blank">Políticas de privacidad</a>
    </div>
  </div>

  <footer>© 2025 Planeador 2025 — Desarrollado por Jean Carlos P. — Todos los derechos reservados.</footer>

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
