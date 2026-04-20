<?php
/**
 * API - Processamento de Calendário via Gemini (Modelo de Produção 2026)
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Core/Database.php';

use App\Core\Database;

$logFile = __DIR__ . '/../uploads/gemini_debug.log';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['calendar_pdf']) && $_FILES['calendar_pdf']['error'] === 0) {
        
        $uploadDir = __DIR__ . '/../uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        
        $ext = strtolower(pathinfo($_FILES['calendar_pdf']['name'], PATHINFO_EXTENSION));
        $filePath = $uploadDir . 'calendario_atual.' . $ext;
        move_uploaded_file($_FILES['calendar_pdf']['tmp_name'], $filePath);

        $db = Database::getConnection();
        $holidaysFound = [];
        
        if (defined('GEMINI_API_KEY') && !empty(GEMINI_API_KEY)) {
            $apiKey = GEMINI_API_KEY;
            $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key=" . $apiKey;
            
            $fileData = base64_encode(file_get_contents($filePath));
            $mimeType = ($ext === 'pdf') ? 'application/pdf' : 'image/' . $ext;
            
            $payload = [
                "contents" => [
                    [
                        "parts" => [
                            ["text" => "Extraia feriados de 2026 deste calendário. Responda APENAS JSON: [{'date': 'YYYY-MM-DD', 'desc': 'Feriado'}]"],
                            ["inline_data" => ["mime_type" => $mimeType, "data" => $fileData]]
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
            
            file_put_contents($logFile, date('Y-m-d H:i:s') . " - [CALENDÁRIO - FLASH] Response: " . $response . PHP_EOL, FILE_APPEND);
            
            $resData = json_decode($response, true);
            if (isset($resData['candidates'][0]['content']['parts'][0]['text'])) {
                $rawText = trim($resData['candidates'][0]['content']['parts'][0]['text']);
                $rawText = preg_replace('/^```json/', '', $rawText);
                $rawText = preg_replace('/```$/', '', $rawText);
                $decoded = json_decode(trim($rawText), true);
                $holidaysFound = isset($decoded['holidays']) ? $decoded['holidays'] : $decoded;
            }
        }

        if (!empty($holidaysFound)) {
            foreach ($holidaysFound as $h) {
                if (isset($h['date']) && isset($h['desc'])) {
                    $stmt = $db->prepare("INSERT IGNORE INTO academic_calendar (date, description) VALUES (?, ?)");
                    $stmt->execute([$h['date'], $h['desc']]);
                }
            }
        }

        header('Location: ' . BASE_URL . 'administrador/calendario?success=api_processed');
        exit;
    }
}

header('Location: ' . BASE_URL . 'administrador/calendario?error=upload_failed');
exit;
