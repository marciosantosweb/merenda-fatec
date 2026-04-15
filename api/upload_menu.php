<?php
/**
 * API - Processamento Inteligente de Cardápio
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Core/Database.php';

use App\Core\Database;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['menu_file']) && $_FILES['menu_file']['error'] === 0) {
        
        // 1. Salvar o arquivo recebido (Apenas para log)
        $uploadDir = __DIR__ . '/../uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        
        $ext = pathinfo($_FILES['menu_file']['name'], PATHINFO_EXTENSION);
        $filePath = $uploadDir . 'cardapio_mensal.' . $ext;
        move_uploaded_file($_FILES['menu_file']['tmp_name'], $filePath);

        // 2. Busca Inteligente (Mock SERPER / Gemini AI)
        // Aqui a IA (Gemini etc.) processaria o documento. Vamos simular esse processo:
        $db = Database::getConnection();
        
        // Mapear dias da semana úteis deste mes
        $mesAtual = date('m');
        $anoAtual = date('Y');
        
        // Cardápios simulados que a IA "extraiu"
        $exemplosPratos = [
            'Arroz, feijão, estrogonofe de frango e batata palha',
            'Arroz, feijão, bife acebolado e salada mista',
            'Sopa de legumes com carne e pão francês',
            'Macarronada à bolonhesa',
            'Arroz, feijão, frango assado com batatas',
            'Galinhada com legumes',
            'Arroz, tutu de feijão, carne de porco e couve',
            'Panqueca de carne com molho e arroz'
        ];
        
        $diasNoMes = cal_days_in_month(CAL_GREGORIAN, $mesAtual, $anoAtual);
        
        for ($dia = 1; $dia <= $diasNoMes; $dia++) {
            $dataStr = sprintf('%04d-%02d-%02d', $anoAtual, $mesAtual, $dia);
            $diaSemana = date('w', strtotime($dataStr));
            
            // Só dias úteis (1 a 5)
            if ($diaSemana >= 1 && $diaSemana <= 5) {
                // Sorteia um prato e insere validando se o dia já tem cardapio ou dá update (pode usar UPSERT/REPLACE)
                $prato = $exemplosPratos[array_rand($exemplosPratos)];
                
                $stmt = $db->prepare("INSERT INTO menu (date, description) VALUES (?, ?) ON DUPLICATE KEY UPDATE description = ?");
                $stmt->execute([$dataStr, $prato, $prato]);
            }
        }

        header('Location: ' . BASE_URL . 'cozinha/cardapio?success=api_processed');
        exit;
    }
}

header('Location: ' . BASE_URL . 'cozinha/cardapio?error=upload_failed');
exit;
