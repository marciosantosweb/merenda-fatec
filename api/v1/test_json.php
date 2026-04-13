<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__FILE__) . '/../../');
}
require_once ROOT_PATH . 'config/config.php';
require_once ROOT_PATH . 'app/Core/Database.php';

use App\Core\Database;

try {
    $db = Database::getConnection();
    $today = date('Y-m-d');
    
    $stmt = $db->prepare("SELECT description FROM menu WHERE date = ?");
    $stmt->execute([$today]);
    $menu = $stmt->fetch();
    
    $data = [
        'status' => 'success',
        'date' => $today,
        'menu' => $menu ? $menu['description'] : 'Cardápio ainda não informado pela cozinha.',
        'is_available' => $menu ? true : false
    ];
    
    $json = json_encode($data);
    if ($json === false) {
        echo "JSON Encode Error: " . json_last_error_msg() . "<br>";
        var_dump($data);
    } else {
        echo "JSON Result: " . $json;
    }
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage();
}
