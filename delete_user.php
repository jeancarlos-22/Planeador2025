<?php
require_once 'config.php';
require_login();
require_role(['jefe','admin']); // Solo Jefe y Admin
header('Content-Type: application/json');

// Leer la entrada JSON
$data = json_decode(file_get_contents('php://input'), true);
$id = isset($data['id']) ? (int)$data['id'] : 0;

if (!$id) {
    echo json_encode(['success' => false, 'error' => 'ID de usuario no proporcionado.']);
    exit;
}

// No permitir que un usuario se elimine a sí mismo
if ($id === $_SESSION['user_id']) {
    echo json_encode(['success' => false, 'error' => 'No puedes eliminar tu propio usuario.']);
    exit;
}

try {
    // Verificar que el usuario exista
    $stmt = $pdo->prepare("SELECT id, username FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(['success' => false, 'error' => 'Usuario no encontrado.']);
        exit;
    }

    // Eliminar usuario
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);

    // Registrar acción en logs
    log_action($pdo, $_SESSION['user_id'], 'delete_user', "Eliminó al usuario ID {$id} ({$user['username']})");

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Error al eliminar usuario: ' . $e->getMessage()]);
}
