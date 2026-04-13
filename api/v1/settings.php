<?php
/**
 * API v1 - Settings
 */

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__FILE__) . '/../../');
}
require_once ROOT_PATH . 'config/config.php';
require_once ROOT_PATH . 'app/Core/Database.php';

use App\Core\Database;

$db = Database::getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $db->query("SELECT config_key, config_value FROM settings");
    $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    // Formatar dados importantes
    $response = [
        'status' => 'success',
        'data' => [
            'login_expiration_days' => (int)$settings['login_expiration_days'],
            'reservation_window' => [
                'start' => $settings['reservation_start'],
                'end' => $settings['reservation_end']
            ],
            'notification' => [
                'text' => $settings['notification_text'],
                'limit_per_day' => (int)$settings['notifications_per_day']
            ],
            'server_time' => date('H:i:s'),
            'server_date' => date('Y-m-d')
        ]
    ];

    jsonResponse($response);
}
