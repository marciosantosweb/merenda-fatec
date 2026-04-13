<?php
/**
 * API v1 - Menu
 */

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__FILE__) . '/../../');
}
require_once ROOT_PATH . 'config/config.php';
require_once ROOT_PATH . 'app/Core/Database.php';

use App\Core\Database;

$db = Database::getConnection();
$today = date('Y-m-d');
$dayOfWeek = date('w'); // 0 (Dom) a 6 (Sab)

// 1. Verificar se é Final de Semana
if ($dayOfWeek == 0 || $dayOfWeek == 6) {
    jsonResponse([
        'status' => 'blocked',
        'message' => 'Reservas não permitidas aos finais de semana.',
        'next_available_date' => date('Y-m-d', strtotime('next monday'))
    ]);
}

// 2. Verificar se é Dia Bloqueado no Banco (Feriado/Emenda)
$stmt = $db->prepare("SELECT description FROM blocked_days WHERE date = ?");
$stmt->execute([$today]);
$blocked = $stmt->fetch();

if ($blocked) {
    jsonResponse([
        'status' => 'blocked',
        'message' => 'Hoje não haverá merenda: ' . $blocked['description']
    ]);
}

// 3. Buscar Cardápio do Dia
$stmt = $db->prepare("SELECT description FROM menu WHERE date = ?");
$stmt->execute([$today]);
$menu = $stmt->fetch();

jsonResponse([
    'status' => 'success',
    'date' => $today,
    'menu' => $menu ? $menu['description'] : 'Cardápio ainda não informado pela cozinha.',
    'is_available' => $menu ? true : false
]);
