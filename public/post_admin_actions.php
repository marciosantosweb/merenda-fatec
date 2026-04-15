<?php
/**
 * PROCESSADOR DE AÇÕES ADMINISTRATIVAS (BLOQUEIOS E CALENDÁRIO)
 */
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__FILE__) . '/../');
}
require_once ROOT_PATH . 'config/config.php';
require_once ROOT_PATH . 'app/Core/Database.php';

use App\Core\Database;
$db = Database::getConnection();

$action = $_GET['action'] ?? '';

try {
    if ($action === 'add_block') {
        $date = $_POST['data_bloqueio'] ?? '';
        $desc = $_POST['description'] ?? 'Bloqueio Manual';
        
        if (!empty($date)) {
            $stmt = $db->prepare("INSERT INTO blocked_days (date, description) VALUES (?, ?) ON DUPLICATE KEY UPDATE description = ?");
            $stmt->execute([$date, $desc, $desc]);
        }
    } 
    
    else if ($action === 'remove_block') {
        $date = $_GET['date'] ?? '';
        $type = $_GET['type'] ?? 'manual';
        
        if (!empty($date)) {
            $table = ($type === 'ia') ? 'academic_calendar' : 'blocked_days';
            $stmt = $db->prepare("DELETE FROM $table WHERE date = ?");
            $stmt->execute([$date]);
        }
    }

    header("Location: " . BASE_URL . "administrador?success=1#calendario");
    exit;

} catch (Exception $e) {
    die("Erro ao processar ação: " . $e->getMessage());
}
