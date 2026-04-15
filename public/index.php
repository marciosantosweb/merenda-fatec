<?php
/**
 * Roteador Ultra-Robusto - MERENDA FATEC
 */

// 1. DIAGNÓSTICO ATIVADO
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Detecção Dinâmica de Raiz
$scriptPath = dirname(__FILE__);
if (file_exists($scriptPath . '/config/config.php')) {
    $root = $scriptPath . '/';
} else if (file_exists($scriptPath . '/../config/config.php')) {
    $root = $scriptPath . '/../';
} else {
    $root = $scriptPath . '/'; // Fallback
}
define('ROOT_PATH', $root);

require_once ROOT_PATH . 'config/config.php';
require_once ROOT_PATH . 'app/Core/Database.php';

// Configuração Global de Sessão (Alcance total do projeto)
if (session_status() === PHP_SESSION_NONE) {
    $currentPath = parse_url(BASE_URL, PHP_URL_PATH);
    $rootPath = str_replace('public/', '', $currentPath);
    session_set_cookie_params([
        'path' => $rootPath,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// --- BLINDAGEM DE SEGURANÇA ADMIN ---
// Constantemente verifica se uma sessão ativa de admin ainda corresponde ao e-mail autorizado
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
    $sessionEmail = $_SESSION['user_email'] ?? null;
    
    // Se não tiver o email na sessão (sessão antiga), vamos buscar no banco para validar e atualizar a sessão
    if (!$sessionEmail && isset($_SESSION['user_id'])) {
        $db = App\Core\Database::getConnection();
        $stmt = $db->prepare("SELECT email FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $sessionEmail = $stmt->fetchColumn();
        $_SESSION['user_email'] = $sessionEmail;
    }

    if (strtolower($sessionEmail) !== strtolower(ADMIN_EMAIL_API)) {
        // E-mail atual logado é DIFERENTE do autorizado no config.php. Expulsando.
        session_destroy();
        header("Location: " . BASE_URL . "login?error=unauthorized");
        exit;
    }
}

// 2. ROTEAMENTO
$url = isset($_GET['url']) ? $_GET['url'] : '';
$url = strtok($url, '?');
$parts = explode('/', trim($url, '/'));
$page = $parts[0] ?? 'login';
$subpage = $parts[1] ?? '';

// --- INTERCEPTAÇÃO DE ACESSO ---
if ($page === 'auth' && $subpage === 'callback') {
    // Tenta 3 lugares diferentes para achar o arquivo de autenticação
    $locations = [
        ROOT_PATH . 'auth/callback.php',
        dirname(__FILE__) . '/../auth/callback.php',
        dirname(__FILE__) . '/auth/callback.php'
    ];
    
    $found = false;
    foreach ($locations as $loc) {
        if (file_exists($loc)) {
            include $loc;
            $found = true;
            break;
        }
    }
    
    if (!$found) {
        die("<h1>🚨 Erro de Estrutura:</h1> Não encontramos o arquivo de autenticação em nenhuma das pastas esperadas.<br>Por favor, garanta que a pasta <b>auth</b> foi subida para o servidor.");
    }
    exit;
}

// Redirecionamento se já logado
if (isset($_SESSION['user_id']) && ($page === 'login' || empty($page))) {
    $target = ($_SESSION['user_role'] === 'admin') ? 'administrador' : 'cozinha';
    header("Location: " . BASE_URL . $target);
    exit;
}

if ($page === 'sair') {
    session_destroy();
    header("Location: " . BASE_URL);
    exit;
}

// 3. ASSETS
$assetsBase = BASE_URL;
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>MERENDA - Fatec São Sebastião</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/style.css?v=<?= time() ?>">
</head>
<body class="bg-light">

<?php
// 4. VIEWS
if (!isset($_SESSION['user_id']) && $page !== 'login') {
    include ROOT_PATH . 'views/login.php';
} else {
    switch ($page) {
        case 'login': include ROOT_PATH . 'views/login.php'; break;
        case 'cozinha': 
            if (empty($subpage)) {
                include ROOT_PATH . 'views/cozinha_dashboard.php';
            } else {
                $viewPath = ROOT_PATH . "views/cozinha_{$subpage}.php";
                if (file_exists($viewPath)) {
                    include $viewPath;
                } else {
                    include ROOT_PATH . 'views/cozinha_dashboard.php';
                }
            }
            break;
        case 'administrador':
            if (empty($subpage)) {
                include ROOT_PATH . 'views/admin_dashboard.php';
            } else {
                $viewPath = ROOT_PATH . "views/admin_{$subpage}.php";
                if (file_exists($viewPath)) {
                    include $viewPath;
                } else {
                    include ROOT_PATH . 'views/admin_dashboard.php';
                }
            }
            break;
        default: include ROOT_PATH . 'views/login.php'; break;
    }
}
?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>js/main.js"></script>
</body>
</html>
