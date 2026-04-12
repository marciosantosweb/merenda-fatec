<?php
/**
 * API v1 - Realizar Reserva
 */
use App\Core\Database;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_POST['user_id'] ?? null;
    $repetitions = $_POST['repetitions'] ?? 0;
    $today = date('Y-m-d');

    if (!$userId) {
        echo json_encode(['success' => false, 'message' => 'Usuário inválido']);
        exit;
    }

    $db = Database::getConnection();

    try {
        // Upsert da reserva (insere ou atualiza se já existir pro dia)
        $stmt = $db->prepare("
            INSERT INTO reservations (user_id, date, repetitions) 
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE repetitions = ?, modifications = modifications + 1
        ");
        
        $stmt->execute([$userId, $today, $repetitions, $repetitions]);

        echo json_encode([
            'success' => true, 
            'message' => 'Reserva confirmada!',
            'date' => $today
        ]);

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erro no banco: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
}
