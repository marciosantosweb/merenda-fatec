<?php
/**
 * API v1 - Menu
 */
use App\Core\Database;

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__FILE__) . '/../../');
}
require_once ROOT_PATH . 'config/config.php';
require_once ROOT_PATH . 'app/Core/Database.php';

if (!function_exists('jsonResponse')) {
    function jsonResponse($data, $status = 200) {
        header('Content-Type: application/json');
        http_response_code($status);
        echo json_encode($data);
        exit;
    }
}

$db = Database::getConnection();

// --- CARDÁPIO MENSAL ---
if (isset($_GET['type']) && $_GET['type'] === 'monthly') {
    $mesAtual = date('m');
    $anoAtual = date('Y');
    
    // Buscar todos os pratos do mês
    $stmt = $db->prepare("SELECT date, description FROM menu WHERE MONTH(date) = ? AND YEAR(date) = ? ORDER BY date ASC");
    $stmt->execute([$mesAtual, $anoAtual]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Buscar dias bloqueados do mês para sinalizar no aplicativo
    $stmtBlocked = $db->prepare("SELECT date, description FROM blocked_days WHERE MONTH(date) = ? AND YEAR(date) = ?");
    $stmtBlocked->execute([$mesAtual, $anoAtual]);
    $blockedDays = $stmtBlocked->fetchAll(PDO::FETCH_KEY_PAIR);

    jsonResponse([
        'status' => 'success',
        'month' => (int)$mesAtual,
        'year' => (int)$anoAtual,
        'menu' => $items,
        'blocked_days' => $blockedDays
    ]);
}

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
