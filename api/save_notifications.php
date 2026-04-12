<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Core/Database.php';

use App\Core\Database;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $msg1 = $_POST['msg1'] ?? '';
    $msg2 = $_POST['msg2'] ?? '';
    $msg3 = $_POST['msg3'] ?? '';
    $time = $_POST['scheduled_time'] ?? '19:15:00';

    $db = Database::getConnection();

    try {
        $stmt = $db->prepare("
            UPDATE notification_settings 
            SET message_1 = ?, message_2 = ?, message_3 = ?, scheduled_time = ? 
            WHERE id = 1
        ");
        $stmt->execute([$msg1, $msg2, $msg3, $time]);

        header('Location: ' . BASE_URL . 'administrador/notificacoes?success=1');
    } catch (PDOException $e) {
        die("Erro ao salvar: " . $e->getMessage());
    }
}
