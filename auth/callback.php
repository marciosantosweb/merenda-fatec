<?php
/**
 * CALLBACK MICROSOFT - VERSÃO ULTRA LIMPA
 * ESTE ARQUIVO NÃO DEVE TER SESSION_START()
 */

// 1. SILÊNCIO TOTAL
error_reporting(0);
ini_set('display_errors', 0);

// 2. CAMINHOS
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__FILE__) . '/../');
}

require_once ROOT_PATH . 'config/config.php';
require_once ROOT_PATH . 'app/Core/Database.php';

use App\Core\Database;

// O código abaixo só roda se o index.php incluiu este arquivo
$code = $_GET['code'] ?? null;
if (!$code) {
    header("Location: " . BASE_URL);
    exit;
}

// 3. TROCA DE TOKEN
$token_url = "https://login.microsoftonline.com/" . MS_TENANT_ID . "/oauth2/v2.0/token";
$post_data = [
    'client_id' => MS_CLIENT_ID,
    'scope' => 'User.Read',
    'code' => $code,
    'redirect_uri' => MS_REDIRECT_URI,
    'grant_type' => 'authorization_code',
    'client_secret' => MS_CLIENT_SECRET,
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $token_url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

if (isset($data['access_token'])) {
    // 4. DADOS DO USUÁRIO
    $user_url = "https://graph.microsoft.com/v1.0/me";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $user_url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $data['access_token']]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $user_res = curl_exec($ch);
    curl_close($ch);

    $user = json_decode($user_res, true);
    $email = $user['mail'] ?? $user['userPrincipalName'];

    if (strpos($email, '@cps.sp.gov.br') !== false || strpos($email, '@fatec.sp.gov.br') !== false) {
        $db = Database::getConnection();
        
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $row = $stmt->fetch();

        if (!$row) {
            $ins = $db->prepare("INSERT INTO users (name, email, role, status, microsoft_id) VALUES (?, ?, 'admin', 'active', ?)");
            $ins->execute([$user['displayName'], $email, $user['id']]);
            $uid = $db->lastInsertId();
        } else {
            $uid = $row['id'];
        }

        // SETAR SESSÃO (index.php já iniciou a sessão)
        $_SESSION['user_id'] = $uid;
        $_SESSION['user_name'] = $user['displayName'];
        $_SESSION['user_role'] = 'admin';

        header("Location: " . BASE_URL . "administrador");
        exit;
    }
}

// Se chegou até aqui sem logar, volta pro login
header("Location: " . BASE_URL . "?error=failed");
exit;
