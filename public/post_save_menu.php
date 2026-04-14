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
    $date = date('Y-m-d'); // Default to today, but could be passed via form
    $description = $_POST['menu_description'] ?? '';

    $db = Database::getConnection();
    
    $stmt = $db->prepare("
        INSERT INTO menu (date, description) VALUES (?, ?) 
        ON DUPLICATE KEY UPDATE description = ?
    ");
    $stmt->execute([$date, $description, $description]);
    
    header("Location: " . BASE_URL . "administrador?status=menu_saved");
    exit;
}
