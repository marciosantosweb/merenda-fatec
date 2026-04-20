<?php
/**
 * API - Processamento de Cardápio via Gemini (Focado em Almoço)
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Core/Database.php';

use App\Core\Database;

$logFile = __DIR__ . '/../uploads/gemini_debug.log';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['menu_file']) && $_FILES['menu_file']['error'] === 0) {
        
        $uploadDir = __DIR__ . '/../uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        
        $ext = strtolower(pathinfo($_FILES['menu_file']['name'], PATHINFO_EXTENSION));
        $filePath = $uploadDir . 'cardapio_mensal.' . $ext;
        move_uploaded_file($_FILES['menu_file']['tmp_name'], $filePath);

        $db = Database::getConnection();
        $extractedMenu = [];
        
        if (defined('GEMINI_API_KEY') && !empty(GEMINI_API_KEY)) {
            $apiKey = GEMINI_API_KEY;
            $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key=" . $apiKey;
            
            $fileData = base64_encode(file_get_contents($filePath));
            $mimeType = ($ext === 'pdf') ? 'application/pdf' : 'image/' . $ext;
            
            $payload = [
                "contents" => [
                    [
                        "parts" => [
                            ["text" => "Você é um extrator de cardápios escolares. Use o ano de 2026. Analise o arquivo e extraia APENAS os itens referentes ao ALMOÇO (ou Refeição Principal). Ignore café da manhã, lanche da tarde ou outras refeições menores. Responda APENAS com um JSON puro no formato {'YYYY-MM-DD': 'Descrição do Almoço'}. Se o dia não tiver almoço ou for feriado, ignore."],
                            [
                                "inline_data" => [
                                    "mime_type" => $mimeType,
                                    "data" => $fileData
                                ]
                            ]
                        ]
                    ]
                ]
            ];

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($ch);
            curl_close($ch);
            
            file_put_contents($logFile, date('Y-m-d H:i:s') . " - [ALMOÇO ONLY] Response: " . $response . PHP_EOL, FILE_APPEND);
            
            $resData = json_decode($response, true);
            if (isset($resData['candidates'][0]['content']['parts'][0]['text'])) {
                $rawText = trim($resData['candidates'][0]['content']['parts'][0]['text']);
                $rawText = preg_replace('/^```json/', '', $rawText);
                $rawText = preg_replace('/```$/', '', $rawText);
                $extractedMenu = json_decode(trim($rawText), true);
            }
        }

        $count = 0;
        if (!empty($extractedMenu) && is_array($extractedMenu)) {
            foreach ($extractedMenu as $date => $description) {
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                    $stmt = $db->prepare("INSERT INTO menu (date, description) VALUES (?, ?) ON DUPLICATE KEY UPDATE description = ?");
                    $stmt->execute([$date, $description, $description]);
                    $count++;
                }
            }
        }

        header('Location: ' . BASE_URL . "cozinha/cardapio?success=api_processed&count=$count");
        exit;
    }
}

header('Location: ' . BASE_URL . 'cozinha/cardapio?error=upload_failed');
exit;
