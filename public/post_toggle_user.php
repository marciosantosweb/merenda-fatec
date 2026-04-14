<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit;
}

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__FILE__) . '/../');
}
require_once ROOT_PATH . 'config/config.php';
require_once ROOT_PATH . 'app/Core/Database.php';

use App\Core\Database;

header('Content-Type: application/json');

$userId = $_POST['user_id'] ?? null;
$action = $_POST['action'] ?? null; // 'activate' or 'deactivate'

if (!$userId || !in_array($action, ['activate', 'deactivate'])) {
    echo json_encode(['success' => false, 'message' => 'Parâmetros inválidos']);
    exit;
}

$newStatus = $action === 'activate' ? 'active' : 'inactive';

try {
    $db = Database::getConnection();
    $stmt = $db->prepare("UPDATE users SET status = ? WHERE id = ? AND role = 'aluno'");
    $stmt->execute([$newStatus, $userId]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'new_status' => $newStatus]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Usuário não encontrado ou sem permissão para alteração']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro no banco: ' . $e->getMessage()]);
}
