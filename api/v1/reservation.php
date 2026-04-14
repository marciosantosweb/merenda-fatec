<?php
/**
 * API v1 - Realizar Reserva
 */
use App\Core\Database;

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__FILE__) . '/../../');
}
require_once ROOT_PATH . 'config/config.php';
require_once ROOT_PATH . 'app/Core/Database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_POST['user_id'] ?? null;
    $repetitions = $_POST['repetitions'] ?? 0;
    $action = $_POST['action'] ?? 'save';
    $today = date('Y-m-d');

    if (!$userId) {
        echo json_encode(['success' => false, 'message' => 'Usuário inválido']);
        exit;
    }

    $db = Database::getConnection();

    try {
        if ($action === 'delete') {
            $stmt = $db->prepare("DELETE FROM reservations WHERE user_id = ? AND date = ?");
            $stmt->execute([$userId, $today]);
            echo json_encode([
                'success' => true,
                'message' => 'Reserva cancelada!',
                'date' => $today
            ]);
        } else {
            // Upsert da reserva
            $stmt = $db->prepare("
                INSERT INTO reservations (user_id, date, repetitions) 
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE repetitions = ?, modifications = modifications + 1
            ");
            $stmt->execute([$userId, $today, $repetitions, $repetitions]);
            echo json_encode([
                'success' => true, 
                'message' => 'Reserva confirmada!',
                'date' => $today
            ]);
        }

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erro no banco: ' . $e->getMessage()]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $userId = $_GET['user_id'] ?? null;
    $today = date('Y-m-d');

    if (!$userId) {
        echo json_encode(['success' => false, 'message' => 'Usuário inválido']);
        exit;
    }

    $db = Database::getConnection();

    try {
        $stmt = $db->prepare("SELECT repetitions FROM reservations WHERE user_id = ? AND date = ?");
        $stmt->execute([$userId, $today]);
        $res = $stmt->fetch();

        if ($res) {
            echo json_encode([
                'success' => true,
                'has_reservation' => true,
                'repetitions' => $res['repetitions']
            ]);
        } else {
            echo json_encode([
                'success' => true,
                'has_reservation' => false
            ]);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erro no banco: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
}
