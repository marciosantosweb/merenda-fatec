<?php
/**
 * Callback de Autenticação Microsoft
 */
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/Core/Database.php';

use App\Core\Database;

session_start();

$code = $_GET['code'] ?? null;

if (!$code) {
    die("Erro: Código de autenticação não recebido.");
}

// 1. Trocar código por Token de Acesso
$token_url = "https://login.microsoftonline.com/" . MS_TENANT_ID . "/oauth2/v2.0/token";

$data = [
    'client_id' => MS_CLIENT_ID,
    'scope' => 'User.Read',
    'code' => $code,
    'redirect_uri' => MS_REDIRECT_URI,
    'grant_type' => 'authorization_code',
    'client_secret' => MS_CLIENT_SECRET,
];

$ch = curl_init($token_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
$response = curl_exec($ch);
$token_data = json_decode($response, true);
curl_close($ch);

if (isset($token_data['error'])) {
    die("Erro no Token: " . $token_data['error_description']);
}

$access_token = $token_data['access_token'];

// 2. Buscar dados do usuário no Microsoft Graph
$graph_url = "https://graph.microsoft.com/v1.0/me";
$ch = curl_init($graph_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $access_token",
    "Content-Type: application/json"
]);
$user_response = curl_exec($ch);
$user_data = json_decode($user_response, true);
curl_close($ch);

$email = strtolower($user_data['mail'] ?? $user_data['userPrincipalName'] ?? '');
$name = $user_data['displayName'] ?? 'Usuário Fatec';

// 3. Validação de Regras de Negócio
if (empty($email)) {
    die("Erro: Não foi possível obter o e-mail da conta Microsoft.");
}

// Só o f189dir@cps.sp.gov.br pode ser admin
$role = ($email === strtolower(ADMIN_EMAIL_API)) ? 'admin' : 'aluno';

// Se não for admin e não for domínio @cps ou @fatec, bloquear (Opcional, mas seguro)
if ($role !== 'admin' && !strpos($email, '@fatec.sp.gov.br') && !strpos($email, '@cps.sp.gov.br')) {
    die("Acesso negado: Este sistema é exclusivo para alunos e funcionários da Fatec.");
}

// 4. Salvar ou Atualizar no Banco de Dados
$db = Database::getConnection();
$stmt = $db->prepare("SELECT id, status FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user) {
    if ($user['status'] === 'inactive') {
        die("Sua conta foi desativada pelo administrador.");
    }
    $user_id = $user['id'];
    $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?")->execute([$user_id]);
} else {
    // Primeiro acesso: Cadastrar
    $stmt = $db->prepare("INSERT INTO users (name, email, role, status, microsoft_id, last_login) VALUES (?, ?, ?, 'active', ?, NOW())");
    $stmt->execute([$name, $email, $role, $user_data['id']]);
    $user_id = $db->lastInsertId();
}

// 5. Iniciar Sessão e Direcionar
$_SESSION['user_id'] = $user_id;
$_SESSION['user_name'] = $name;
$_SESSION['user_role'] = $role;

header("Location: " . BASE_URL . ($role === 'admin' ? 'administrador' : 'login'));
exit;
