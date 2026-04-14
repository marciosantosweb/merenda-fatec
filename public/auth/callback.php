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

// 3. Restrito exclusivamente ao e-mail de administrador
if ($email !== strtolower(ADMIN_EMAIL_API)) {
    ?>
    <!DOCTYPE html>
    <html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <title>Acesso Negado - Rango!</title>
        <style>
            * { margin:0; padding:0; box-sizing:border-box; }
            body { background:#111; color:#fff; font-family:sans-serif; display:flex; align-items:center; justify-content:center; min-height:100vh; }
            .box { text-align:center; padding: 40px 30px; }
            .icon { font-size:56px; margin-bottom:20px; }
            h1 { font-size:22px; margin-bottom:10px; color:#B50D11; }
            p { color:#888; font-size:14px; margin-bottom:6px; }
            small { color:#555; font-size:12px; }
            a { display:inline-block; margin-top:30px; padding:10px 28px; background:#B50D11; color:#fff; text-decoration:none; border-radius:8px; font-size:14px; }
        </style>
    </head>
    <body>
        <div class="box">
            <div class="icon">🚫</div>
            <h1>Acesso Negado</h1>
            <p>Este painel é exclusivo para o administrador do sistema.</p>
            <small>Conta autenticada: <?= htmlspecialchars($email) ?></small>
            <br>
            <a href="<?= BASE_URL ?>">Voltar ao início</a>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// A partir daqui, somente o e-mail admin chegará
$role = 'admin';

// 4. Salvar ou Atualizar no Banco de Dados
$db = Database::getConnection();
$stmt = $db->prepare("SELECT id, status FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user) {
    if ($user['status'] === 'inactive') {
        die("Sua conta foi desativada.");
    }
    $user_id = $user['id'];
    $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?")->execute([$user_id]);
} else {
    // Primeiro acesso: cadastrar como admin
    $stmt = $db->prepare("INSERT INTO users (name, email, role, status, microsoft_id, last_login) VALUES (?, ?, 'admin', 'active', ?, NOW())");
    $stmt->execute([$name, $email, $user_data['id']]);
    $user_id = $db->lastInsertId();
}

// 5. Iniciar Sessão e Direcionar para o painel
$_SESSION['user_id']   = $user_id;
$_SESSION['user_name'] = $name;
$_SESSION['user_role'] = 'admin';

header("Location: " . BASE_URL . "administrador");
exit;
