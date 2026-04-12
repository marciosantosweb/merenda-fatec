<?php
/**
 * Script de Disparo Automático de Notificações
 * Deve ser configurado no Cron Job do servidor para rodar a cada 1 minuto.
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/Core/Database.php';

use App\Core\Database;

$db = Database::getConnection();
$today = date('Y-m-d');
$nowTime = date('H:i'); // Hora atual no formato HH:MM

// 1. Buscar configurações
$stmt = $db->query("SELECT * FROM notification_settings WHERE id = 1");
$config = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$config) exit("Configurações não encontradas.\n");

$scheduledTime = substr($config['scheduled_time'], 0, 5); // Formato HH:MM

// 2. Verificar se chegou a hora e se ainda não foi enviado hoje
if ($nowTime === $scheduledTime && $config['last_sent'] !== $today) {
    
    echo "Disparando notificações em massa para todos os alunos...\n";
    
    // Lista de mensagens cadastradas pelo admin
    $messages = [
        $config['message_1'],
        $config['message_2'],
        $config['message_3']
    ];

    // Lógica de envio (Aqui conectamos com o Firebase FCM no futuro)
    foreach ($messages as $msg) {
        if (!empty($msg)) {
            sendPushNotification($msg);
        }
    }

    // 3. Marcar como enviado hoje para não repetir no mesmo minuto
    $update = $db->prepare("UPDATE notification_settings SET last_sent = ? WHERE id = 1");
    $update->execute([$today]);
    
    echo "Sucesso! Notificações enviadas às $nowTime.\n";
} else {
    echo "Aguardando horário planejado ($scheduledTime). Hora atual: $nowTime.\n";
}

/**
 * Função para enviar Push via Firebase
 */
function sendPushNotification($message) {
    // Placeholder para integração com Firebase Cloud Messaging
    // Quando você tiver a SERVER_KEY do Firebase, basta preencher aqui.
    error_log("APP PUSH: $message");
}
