<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "HELLO from test_500.<br>";

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__FILE__) . '/../../');
}

echo "ROOT_PATH is: " . ROOT_PATH . "<br>";

require_once ROOT_PATH . 'config/config.php';
echo "Included config.php<br>";

require_once ROOT_PATH . 'app/Core/Database.php';
echo "Included Database.php<br>";

use App\Core\Database;

try {
    $db = Database::getConnection();
    echo "DB connection SUCCESS<br>";
} catch (Exception $e) {
    echo "DB ERROR: " . $e->getMessage() . "<br>";
}
