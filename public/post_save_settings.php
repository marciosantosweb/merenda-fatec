<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['admin', 'cozinha'])) {
    die("Acesso Negado.");
}

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__FILE__) . '/../');
}
require_once ROOT_PATH . 'config/config.php';
require_once ROOT_PATH . 'app/Core/Database.php';

use App\Core\Database;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $expiration = $_POST['expiration'] ?? 30;
    $start_time = $_POST['start_time'] ?? '18:00';
    $end_time = $_POST['end_time'] ?? '19:30';

    $db = Database::getConnection();
    
    $stmt = $db->prepare("UPDATE settings SET config_value = ? WHERE config_key = ?");
    $stmt->execute([$expiration, 'login_expiration_days']);
    $stmt->execute([$start_time . ':00', 'reservation_start']);
    $stmt->execute([$end_time . ':00', 'reservation_end']);
    
    header("Location: " . BASE_URL . "administrador?status=config_saved");
    exit;
}
