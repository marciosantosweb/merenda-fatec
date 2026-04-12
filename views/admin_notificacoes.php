<?php
include __DIR__ . '/partial_admin_menu.php';
use App\Core\Database;

$db = Database::getConnection();

// Buscar dados atuais
$stmt = $db->query("SELECT * FROM notification_settings WHERE id = 1");
$config = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<div class="container animated-fade">
    <div class="row">
        <div class="col-12 mb-4">
            <h1 class="h3 fatec-title"><i class="fas fa-bell text-danger me-2"></i> Configuração de Notificações</h1>
            <p class="text-muted">Defina as mensagens que serão enviadas automaticamente para todos os alunos no horário planejado.</p>
        </div>

        <div class="col-lg-8">
            <div class="card card-fatec shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Mensagens do Dia</h5>
                </div>
                <div class="card-body">
                    <form action="<?= BASE_URL ?>api/save_notifications.php" method="POST">
                        <div class="mb-4">
                            <label class="form-label fw-bold small">Notificação 1 (Abertura)</label>
                            <input type="text" name="msg1" class="form-control" value="<?= $config['message_1'] ?? '' ?>" placeholder="Ex: Janela aberta! Faça sua reserva.">
                            <div class="form-text">Enviada assim que o sistema abre para reservas.</div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold small">Notificação 2 (Lembrete)</label>
                            <input type="text" name="msg2" class="form-control" value="<?= $config['message_2'] ?? '' ?>" placeholder="Ex: Faltam 30 minutos para encerrar!">
                            <div class="form-text">Enviada no meio da janela de tempo.</div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold small">Notificação 3 (Última Chamada)</label>
                            <input type="text" name="msg3" class="form-control" value="<?= $config['message_3'] ?? '' ?>" placeholder="Ex: Corra! A janta encerra em 5 minutos.">
                            <div class="form-text">Enviada pouco antes do encerramento.</div>
                        </div>

                        <hr>

                        <div class="row align-items-end">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small">Horário de Disparo em Massa</label>
                                <input type="time" name="scheduled_time" class="form-control form-control-lg" value="<?= $config['scheduled_time'] ?? '19:15' ?>">
                            </div>
                            <div class="col-md-6 mb-3 text-end">
                                <button type="submit" class="btn btn-fatec btn-lg w-100">SALVAR CONFIGURAÇÃO</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card card-fatec bg-light border-0">
                <div class="card-body">
                    <h6><i class="fas fa-info-circle text-danger me-2"></i> Como funciona?</h6>
                    <p class="small text-muted">O sistema utiliza um <strong>Cron Job</strong> para verificar o horário configurado. Quando o relógio do servidor bater o horário, ele enviará os pushes via <strong>Firebase (FCM)</strong> para todos os aplicativos móveis instalados.</p>
                    <hr>
                    <div class="alert alert-warning py-2 small">
                        <strong>Dica:</strong> Programe o disparo para 15 ou 20 minutos antes do fechamento da janta (ex: 19:15).
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
