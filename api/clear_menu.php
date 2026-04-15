<?php
/**
 * API - Limpar Todo o Cardápio (Cozinha)
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Core/Database.php';

use App\Core\Database;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = Database::getConnection();
    
    // Deletar todos os registros da tabela menu
    $db->exec("DELETE FROM menu");

    // Redirecionar de volta
    header('Location: ' . BASE_URL . 'cozinha/cardapio?success=menu_cleared');
    exit;
}

header('Location: ' . BASE_URL . 'cozinha/cardapio');
exit;
