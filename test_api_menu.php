<?php
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__FILE__) . '/');
}
require_once ROOT_PATH . 'config/config.php';
require_once ROOT_PATH . 'app/Core/Database.php';

use App\Core\Database;

try {
    $db = Database::getConnection();
    $mesAtual = date('m');
    $anoAtual = date('Y');
    
    $stmt = $db->prepare("SELECT date, description FROM menu WHERE MONTH(date) = ? AND YEAR(date) = ? ORDER BY date ASC");
    $stmt->execute([$mesAtual, $anoAtual]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $data = [
        'status' => 'success',
        'month' => (int)$mesAtual,
        'year' => (int)$anoAtual,
        'menu' => $items
    ];
    
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
