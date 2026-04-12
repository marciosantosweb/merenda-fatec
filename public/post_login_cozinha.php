<?php
/**
 * Processamento de Login da Cozinha
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Core/Database.php';

use App\Core\Database;

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_input = $_POST['user'] ?? '';
    $pass_input = $_POST['password'] ?? '';

    $db = Database::getConnection();
    
    // Buscar apenas usuário do tipo cozinha
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND role = 'cozinha' LIMIT 1");
    $stmt->execute([$user_input]);
    $user = $stmt->fetch();

    if ($user && password_verify($pass_input, $user['password'])) {
        // Sucesso
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = 'cozinha';

        header("Location: " . BASE_URL . "cozinha");
        exit;
    } else {
        // Erro
        header("Location: " . BASE_URL . "?error=1");
        exit;
    }
}
