<?php
/**
 * PROCESSADOR DE LOGIN COZINHA - VERSÃO DE PRODUÇÃO (BLINDADA)
 */

// 1. FORÇAR EXIBIÇÃO DE ERROS
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. DETECÇÃO INTELIGENTE DA RAIZ DO PROJETO
if (file_exists(dirname(__FILE__) . '/config/config.php')) {
    $root = dirname(__FILE__) . '/';
} else if (file_exists(dirname(__FILE__) . '/../config/config.php')) {
    $root = dirname(__FILE__) . '/../';
} else {
    $root = './'; // Fallback
}
define('ROOT_PATH', $root);

// 3. CARREGAR DEPENDÊNCIAS
require_once ROOT_PATH . 'config/config.php';
require_once ROOT_PATH . 'app/Core/Database.php';

use App\Core\Database;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_input = $_POST['user'] ?? '';
    $pass_input = $_POST['password'] ?? '';

    try {
        $db = Database::getConnection();
        
        // Buscar apenas usuário do tipo cozinha
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND role = 'cozinha' LIMIT 1");
        $stmt->execute([$user_input]);
        $user = $stmt->fetch();

        if ($user && password_verify($pass_input, $user['password'])) {
            // Sucesso no Login
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = 'cozinha';

            // Redireciona para o Painel da Cozinha (URL Amigável via HTACCESS)
            header("Location: " . BASE_URL . "cozinha");
            exit;
        } else {
            // Erro de Usuário/Senha
            header("Location: " . BASE_URL . "?error=invalid_credentials");
            exit;
        }
    } catch (Exception $e) {
        // MOSTRAR O ERRO REAL NA TELA
        echo "<h1>🚨 Erro de Processamento:</h1>";
        echo "<pre>" . $e->getMessage() . "</pre>";
        echo "<hr>Caminho da Tentativa: " . ROOT_PATH;
        exit;
    }
} else {
    // Acesso direto via URL - Redireciona para a Home de Login
    header("Location: " . BASE_URL);
    exit;
}
?>
