<?php
/**
 * API Router - Centraliza as requisições Mobile
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Captura a URI
$uri = $_GET['url']; 
$parts = explode('/', str_replace('api/', '', $uri));
$endpoint = $parts[0] ?? '';

// Helper para resposta JSON
function jsonResponse($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

// Roteamento
switch ($endpoint) {
    case 'auth':
        // Autenticação Microsoft para Mobile - valida access_token e retorna dados do usuário
        require_once __DIR__ . '/v1/auth_mobile.php';
        break;

    case 'menu':
        // Retorna o cardápio do dia
        require_once __DIR__ . '/v1/menu.php';
        break;

    case 'reservation':
        // Faz, altera ou consulta reserva
        require_once __DIR__ . '/v1/reservation.php';
        break;

    case 'settings':
        // Retorna configurações (horários, expiração)
        require_once __DIR__ . '/v1/settings.php';
        break;

    default:
        jsonResponse(['error' => 'Endpoint não encontrado'], 404);
        break;
}
