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
    $firstDay = date('Y-m-01');
    $lastDay = date('Y-m-t');
    
    // Buscar todos os pratos do mês via intervalo de datas (mais robusto)
    $stmt = $db->prepare("SELECT date, description FROM menu WHERE date >= ? AND date <= ? ORDER BY date ASC");
    $stmt->execute([$firstDay, $lastDay]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    jsonResponse([
        'status' => 'success',
        'month' => (int)date('m'),
        'year' => (int)date('Y'),
        'menu' => $items ?: [], // Garante lista vazia e não null
        'blocked_days' => new stdClass()
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

// 2. Verificar se é Dia Bloqueado ou Feriado Acadêmico
$stmt = $db->prepare("SELECT description FROM blocked_days WHERE date = ?");
$stmt->execute([$today]);
$blocked = $stmt->fetch();

if (!$blocked) {
    // Se não está em blocked_days, tenta o calendário acadêmico (alimentado pela IA)
    $stmt = $db->prepare("SELECT description FROM academic_calendar WHERE date = ?");
    $stmt->execute([$today]);
    $blocked = $stmt->fetch();
}

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
