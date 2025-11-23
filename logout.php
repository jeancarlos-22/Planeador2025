<?php
require_once 'config.php';

// Verifica si hay sesión activa
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $username = $_SESSION['username'] ?? 'Usuario desconocido';

    // Registrar el evento en los logs
    log_action($pdo, $user_id, 'logout', "Cierre de sesión del usuario: $username");
}

// Destruir la sesión de forma segura
$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, 
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

// Redirigir al login
header("Location: index.php");
exit;
?>
