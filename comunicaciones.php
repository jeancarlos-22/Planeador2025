<?php
require_once 'config.php';
require_login();

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipient_id = $_POST['recipient_id'] ?? null;
    $message = trim($_POST['message'] ?? '');
    $type = $_POST['type'] ?? 'mensaje';
    $sender_id = $_SESSION['user_id'] ?? null;

    if (!$message || !$sender_id) {
        $errors[] = "Todos los campos son obligatorios.";
    } else {
        try {
            if($recipient_id === 'all') {
                $stmt = $pdo->prepare("SELECT id FROM users WHERE id != ?");
                $stmt->execute([$sender_id]);
                $usuarios = $stmt->fetchAll(PDO::FETCH_COLUMN);

                $stmt = $pdo->prepare("
                    INSERT INTO notifications (sender_id, receiver_id, message, type, created_at)
                    VALUES (?, ?, ?, ?, NOW())
                ");
                foreach($usuarios as $uid){
                    $stmt->execute([$sender_id, $uid, $message, $type]);
                }
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO notifications (sender_id, receiver_id, message, type, created_at)
                    VALUES (?, ?, ?, ?, NOW())
                ");
                $stmt->execute([$sender_id, $recipient_id, $message, $type]);
            }

            $success = true;
            echo "<script>window.opener?.notificarNuevoMensaje();</script>";

        } catch (PDOException $e) {
            $errors[] = "Error al enviar mensaje: " . $e->getMessage();
        }
    }
}

$stmt = $pdo->query("SELECT id, name, username FROM users ORDER BY name ASC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Comunicaciones — Planeador</title>
<style>
body { font-family:'Segoe UI',Arial,sans-serif; background:#f0f2f5; padding:20px; }
form { background:white; padding:20px; border-radius:10px; box-shadow:0 4px 8px rgba(0,0,0,0.1); max-width:500px; margin:auto; }
input, select, textarea { width:100%; padding:8px; margin:8px 0; border-radius:6px; border:1px solid #ccc; }
button { padding:10px 15px; background:#5563DE; color:white; border:none; border-radius:6px; cursor:pointer; }
button:hover { background:#334ac0; }
.success { color:green; }
.error { color:red; }
</style>
</head>
<body>

<h2>Enviar Mensaje</h2>

<?php if($success): ?>
<p class="success">Mensaje enviado correctamente ✅</p>
<?php endif; ?>

<?php if($errors): foreach($errors as $err): ?>
<p class="error"><?= htmlspecialchars($err); ?></p>
<?php endforeach; endif; ?>

<form method="POST">
    <label>Para:</label>
    <select name="recipient_id" required>
        <option value="">-- Selecciona usuario --</option>
        <option value="all">📢 Todos</option>
        <?php foreach($users as $u): ?>
            <option value="<?= $u['id']; ?>"><?= htmlspecialchars($u['name'] . ' (' . $u['username'] . ')'); ?></option>
        <?php endforeach; ?>
    </select>

    <label>Tipo de mensaje:</label>
    <select name="type" required>
        <option value="mensaje">💬 Mensaje</option>
        <option value="alerta">🚨 Alerta</option>
        <option value="zumbido">📳 Zumbido</option>
    </select>

    <label>Mensaje:</label>
    <textarea name="message" rows="4" required></textarea>

    <button type="submit">Enviar</button>
</form>

</body>
</html>







