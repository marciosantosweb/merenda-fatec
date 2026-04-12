<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Core/Database.php';

// Router inteligente
$url = isset($_GET['url']) ? $_GET['url'] : 'login';
$parts = explode('/', rtrim($url, '/'));
$page = $parts[0];
$subpage = $parts[1] ?? '';

// Cabeçalho Comum
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MERENDA - Fatec São Sebastião</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@400;700&family=Raleway:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/style.css?v=<?= time() ?>">
</head>
<body>

<?php
// Lógica de Views
switch ($page) {
    case 'login':
    case 'home':
    case '':
        include __DIR__ . '/../views/login.php';
        break;
    
    case 'administrador':
        // Sub-roteamento Administrativo
        if (empty($subpage)) {
            include __DIR__ . '/../views/admin_dashboard.php';
        } else {
            $viewPath = __DIR__ . "/../views/admin_{$subpage}.php";
            if (file_exists($viewPath)) {
                include $viewPath;
            } else {
                echo "<div class='container py-5 text-center'><h1>404</h1><p>Página '{$subpage}' não encontrada.</p></div>";
            }
        }
        break;

    case 'cozinha':
        include __DIR__ . '/../views/cozinha_dashboard.php';
        break;

    case 'sair':
        include __DIR__ . '/logout.php';
        break;

    case 'aplicativo':
        include __DIR__ . '/../views/app_mobile.php';
        break;

    case 'post_login_cozinha.php':
        include __DIR__ . '/post_login_cozinha.php';
        break;

    case 'auth':
        if ($subpage === 'callback') {
            include __DIR__ . '/auth/callback.php';
        }
        break;

    default:
        echo "<div class='container py-5 text-center'><h1>404</h1><p>Caminho não encontrado.</p></div>";
        break;
}
?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>public/js/main.js"></script>
</body>
</html>
