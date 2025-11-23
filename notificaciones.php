<?php
require_once 'config.php';
require_login();
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT n.*, u.name as sender_name, 
           DATE_FORMAT(n.created_at, '%d/%m/%Y %H:%i:%s') as created_at_formatted
    FROM notifications n
    LEFT JOIN users u ON n.sender_id = u.id
    WHERE n.receiver_id = ? AND n.read_status = 0
    ORDER BY n.created_at ASC
");
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($notifications);

















