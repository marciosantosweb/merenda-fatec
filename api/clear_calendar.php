<?php
/**
 * API - Limpar Todo o Calendário Acadêmico (IA)
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Core/Database.php';

use App\Core\Database;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = Database::getConnection();
    
    // 1. Limpar tabela de calendário IA
    $db->exec("DELETE FROM academic_calendar");

    // 2. Limpar nome do arquivo nas configurações
    $db->exec("DELETE FROM settings WHERE config_key = 'last_calendar_file'");

    // 3. Remover arquivo físico se existir
    $filePath = __DIR__ . '/../uploads/calendario_atual.pdf';
    if (file_exists($filePath)) {
        unlink($filePath);
    }

    // Redirecionar de volta para a página de calendário
    header('Location: ' . BASE_URL . 'administrador/calendario?success=calendar_wiped');
    exit;
}

header('Location: ' . BASE_URL . 'administrador/calendario');
exit;
