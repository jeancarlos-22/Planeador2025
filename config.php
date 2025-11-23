<?php
/**
 * --------------------------------------------------------------
 * CONFIGURACIÓN GENERAL DEL SISTEMA
 * Proyecto: Planeador Scrumban PHP
 * Servidor: InfinityFree
 * --------------------------------------------------------------
 */

date_default_timezone_set('America/Bogota');

// --- Mostrar errores (solo en desarrollo, no en producción) ---
error_reporting(E_ALL);
ini_set('display_errors', 1);

/* ==================== CONEXIÓN BASE DE DATOS ==================== */
define('DB_HOST', 'sql100.infinityfree.com');
define('DB_NAME', 'if0_40114929_datos_planeador');
define('DB_USER', 'if0_40114929');
define('DB_PASS', 'diwMJzozxYZ3');
define('DB_CHARSET', 'utf8mb4');

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    exit('❌ Error de conexión a la base de datos.');
}

/* ==================== SESIÓN SEGURA ==================== */
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        // 'cookie_secure' => true, // Activa si usas HTTPS
        'use_strict_mode' => true,
        'cookie_samesite' => 'Lax',
    ]);
}

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/* ==================== FUNCIONES DE UTILIDAD ==================== */
function escape($str) {
    return htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * ✅ Registra acciones en la tabla `activity_logs`.
 */
function log_action(PDO $pdo, $user_id, $action, $details = null) {
    try {
        if ($user_id !== null) {
            $check = $pdo->prepare("SELECT id FROM users WHERE id = :id");
            $check->execute([':id' => $user_id]);
            if ($check->rowCount() === 0) {
                $user_id = null;
            }
        }

        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? null;

        $sql = "INSERT INTO activity_logs (user_id, action, details, ip, user_agent, created_at)
                VALUES (:uid, :action, :details, :ip, :ua, NOW())";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':uid' => $user_id,
            ':action' => $action,
            ':details' => $details,
            ':ip' => $ip,
            ':ua' => $ua
        ]);
    } catch (Exception $e) {
        error_log("⚠️ Error registrando log: " . $e->getMessage());
    }
}

/* ==================== NOTIFICACIONES ==================== */
define('TEAMS_WEBHOOK_URL', '');
define('NOTIFY_FROM_EMAIL', 'noreply@tu-dominio.com');
define('NOTIFY_ADMIN_EMAIL', 'admin@tu-dominio.com');

define('SMTP_ENABLED', false);
define('SMTP_HOST', 'smtp.tuservidor.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'smtp_user');
define('SMTP_PASS', 'smtp_pass');
define('SMTP_SECURE', 'tls');

/**
 * Enviar mensaje a Microsoft Teams (si el webhook está configurado)
 */
function notify_teams($title, $text = '') {
    if (empty(TEAMS_WEBHOOK_URL)) return false;

    $payload = [
        "@type" => "MessageCard",
        "@context" => "http://schema.org/extensions",
        "summary" => $title,
        "themeColor" => "0076D7",
        "title" => $title,
        "text" => $text
    ];

    $ch = curl_init(TEAMS_WEBHOOK_URL);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $res = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    return $err ? false : true;
}

/**
 * Enviar notificación por correo (modo simple)
 */
function notify_email($to, $subject, $htmlBody) {
    if (SMTP_ENABLED) {
        return false; // Aquí se puede integrar PHPMailer si se habilita SMTP
    }

    $from = NOTIFY_FROM_EMAIL;
    $headers = "From: {$from}\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";

    return mail($to, $subject, $htmlBody, $headers);
}

/* ==================== CONTROL DE ACCESO ==================== */
function require_login() {
    if (empty($_SESSION['user_id'])) {
        header('Location: index.php');
        exit;
    }
}

/**
 * ✅ Corrige comparación de roles (acepta mayúsculas y minúsculas)
 */
function require_role($roles = []) {
    $current = strtolower($_SESSION['role'] ?? '');

    // Convertir roles permitidos a minúsculas
    $roles = array_map('strtolower', $roles);

    if (!in_array($current, $roles)) {
        header('HTTP/1.1 403 Forbidden');
        echo "⛔ Acceso denegado.";
        exit;
    }
}

/* ==================== DATOS DEL USUARIO LOGUEADO ==================== */
/**
 * Devuelve los datos del usuario actual almacenados en sesión.
 * Ejemplo de uso:
 * $u = current_user();
 * echo "👤 {$u['name']} — {$u['cargo']} — {$u['role']}";
 */
function current_user() {
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'username' => $_SESSION['username'] ?? '',
        'name' => $_SESSION['name'] ?? '',
        'cargo' => $_SESSION['cargo'] ?? '',
        'role' => $_SESSION['role'] ?? '',
    ];
}
?>



