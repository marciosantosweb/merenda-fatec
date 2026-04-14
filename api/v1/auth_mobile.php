<?php
/**
 * API v1 - Autenticação Mobile (Microsoft OAuth2)
 * 
 * Recebe o access_token do app Flutter (obtido via OAuth2 no dispositivo)
 * Valida com a Microsoft Graph API
 * Verifica o status do usuário no banco de dados
 * Retorna dados do usuário em JSON
 */
use App\Core\Database;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if (!function_exists('jsonResponse')) {
    function jsonResponse($data, $status = 200) {
        http_response_code($status);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__FILE__) . '/../../');
}
require_once ROOT_PATH . 'config/config.php';
require_once ROOT_PATH . 'app/Core/Database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Método não permitido'], 405);
}

// Pegar o access_token enviado pelo app
$input = json_decode(file_get_contents('php://input'), true);
$access_token = $input['access_token'] ?? null;

if (!$access_token) {
    jsonResponse(['success' => false, 'message' => 'Token não fornecido'], 400);
}

// 1. Validar token com Microsoft Graph API
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://graph.microsoft.com/v1.0/me');
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $access_token]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$graph_response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code !== 200) {
    jsonResponse(['success' => false, 'message' => 'Token inválido ou expirado pela Microsoft'], 401);
}

$ms_user = json_decode($graph_response, true);
if (!$ms_user || !isset($ms_user['mail']) && !isset($ms_user['userPrincipalName'])) {
    jsonResponse(['success' => false, 'message' => 'Não foi possível obter dados da Microsoft'], 400);
}

$email = $ms_user['mail'] ?? $ms_user['userPrincipalName'];
$name  = $ms_user['displayName'] ?? 'Usuário';
$ms_id = $ms_user['id'] ?? '';

// 2. Verificar domínio permitido
$allowed_domains = ['@cps.sp.gov.br', '@fatec.sp.gov.br'];
$is_allowed = false;
foreach ($allowed_domains as $domain) {
    if (str_contains(strtolower($email), strtolower($domain))) {
        $is_allowed = true;
        break;
    }
}

if (!$is_allowed) {
    jsonResponse([
        'success' => false,
        'blocked' => true,
        'message' => 'Acesso restrito a e-mails institucionais (@fatec.sp.gov.br ou @cps.sp.gov.br).'
    ], 403);
}

// 3. Buscar ou criar usuário no banco
try {
    $db = Database::getConnection();

    $stmt = $db->prepare("SELECT id, name, email, role, status, microsoft_id FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        // Primeiro acesso: criar conta com role 'aluno' e status 'active'
        $ins = $db->prepare("INSERT INTO users (name, email, role, status, microsoft_id, last_login) VALUES (?, ?, 'aluno', 'active', ?, NOW())");
        $ins->execute([$name, $email, $ms_id]);
        $user_id = $db->lastInsertId();
        $role   = 'aluno';
        $status = 'active';
    } else {
        $user_id = $user['id'];
        $role    = $user['role'];
        $status  = $user['status'];
        $name    = $user['name'];

        $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?")->execute([$user_id]);
        
        // Atualiza microsoft_id se ainda não tiver
        if (empty($user['microsoft_id'])) {
            $db->prepare("UPDATE users SET microsoft_id = ? WHERE id = ?")->execute([$ms_id, $user_id]);
        }
    }

    // 4. Verificar bloqueio
    if ($status !== 'active') {
        jsonResponse([
            'success'  => false,
            'blocked'  => true,
            'message'  => 'Seu acesso ao Rango! está bloqueado. Entre em contato com a administração da Fatec.',
        ], 403);
    }

    // 5. Retornar dados do usuário autenticado
    jsonResponse([
        'success'    => true,
        'user' => [
            'id'    => (int) $user_id,
            'name'  => $name,
            'email' => $email,
            'role'  => $role,
            'status'=> $status,
        ]
    ]);

} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => 'Erro interno do servidor: ' . $e->getMessage()], 500);
}
