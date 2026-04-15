<?php
use App\Core\Database;
$db = Database::getConnection();

$today = date('Y-m-d');
$is_weekend = (date('w') == 0 || date('w') == 6);

// Verificar bloqueio acadêmico
$stmt = $db->prepare("SELECT description FROM blocked_days WHERE date = ?");
$stmt->execute([$today]);
$calendar_event = $stmt->fetch();

// Buscar Estatísticas do Dia
$stmtStats = $db->prepare("
    SELECT 
        COUNT(*) as total_reservas,
        SUM(repetitions) as total_repeticoes
    FROM reservations 
    WHERE date = ?
");
$stmtStats->execute([$today]);
$stats = $stmtStats->fetch();

$total_reservas = $stats['total_reservas'] ?? 0;
$total_repeticoes = $stats['total_repeticoes'] ?? 0;
$total_pratos = $total_reservas + $total_repeticoes;

// Buscar Cardápio
$stmtMenu = $db->prepare("SELECT description FROM menu WHERE date = ?");
$stmtMenu->execute([$today]);
$menu = $stmtMenu->fetch();
$menu_text = $menu ? $menu['description'] : 'Cardápio ainda não informado';

// Buscar Logs Recentes
$recentLogs = [];
try {
    $stmtLogs = $db->query("
        SELECT l.created_at, l.action, l.details, u.name 
        FROM activity_log l
        JOIN users u ON l.user_id = u.id
        ORDER BY l.created_at DESC
        LIMIT 10
    ");
    $recentLogs = $stmtLogs->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Fallback se a tabela não existir
    $stmtFallback = $db->prepare("
        SELECT r.updated_at as created_at, 'Registro' as action, CONCAT('Pratos: ', (r.repetitions + 1)) as details, u.name
        FROM reservations r
        JOIN users u ON r.user_id = u.id
        WHERE r.date = ?
        ORDER BY r.updated_at DESC
        LIMIT 10
    ");
    $stmtFallback->execute([$today]);
    $recentLogs = $stmtFallback->fetchAll(PDO::FETCH_ASSOC);
}
?>
<?php include __DIR__ . '/partial_cozinha_menu.php'; ?>

<div class="container pb-5">
    <?php if ($is_weekend || $calendar_event): ?>
        <div class="row min-vh-50 align-items-center justify-content-center">
            <div class="col-md-6 text-center py-5">
                <i class="fas fa-umbrella-beach fs-1 text-muted mb-4" style="font-size: 5rem;"></i>
                <h2 class="fatec-title h3">Atividades Suspensas</h2>
                <p class="lead text-muted">
                    <?= $is_weekend ? "Hoje é final de semana." : "Hoje é dia não letivo: <strong>" . htmlspecialchars($calendar_event['description']) . "</strong>" ?>
                </p>
                <p class="small text-muted">Não há registros de merenda ou contagem para este dia.</p>
                <a href="<?= BASE_URL ?>cozinha" class="btn btn-outline-danger mt-3 px-4">Atualizar Painel</a>
            </div>
        </div>
    <?php else: ?>
        <div class="row">
            <!-- Cardápio -->
            <div class="col-lg-12 mb-4">
                <div class="card card-fatec bg-gradient-red text-white">
                    <div class="card-body p-4 text-center">
                        <h2 class="h5 opacity-75">Cardápio do Dia</h2>
                        <h1 class="display-5 mb-0"><?= htmlspecialchars($menu_text) ?></h1>
                    </div>
                </div>
            </div>

            <!-- Estatísticas Diretas -->
            <div class="col-md-4 mb-4">
                <div class="card card-fatec h-100 p-4 text-center shadow-sm">
                    <h3 class="h6 text-muted fw-bold">REFEIÇÕES (BASE)</h3>
                    <p class="display-3 fw-bold mb-0 text-dark"><?= $total_reservas ?></p>
                    <small class="text-muted">Usuários confirmados hoje</small>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card card-fatec h-100 p-4 text-center shadow-sm">
                    <h3 class="h6 text-muted">REPETIÇÕES ESPERADAS</h3>
                    <p class="display-3 fw-bold mb-0 text-fatec-red"><?= $total_repeticoes ?></p>
                    <small class="text-muted">Somatória de extras pedidas</small>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card card-fatec h-100 p-4 text-center shadow-lg border-bottom border-5 border-danger">
                    <h3 class="h6 text-muted fw-bold text-dark">TOTAL DE PRATOS</h3>
                    <p class="display-3 fw-bold mb-0 text-dark"><?= $total_pratos ?></p>
                    <small class="text-dark fw-bold">Previsão de consumo total</small>
                </div>
            </div>

            <!-- Lista de Atividades -->
            <div class="col-12">
                <div class="card card-fatec shadow-sm">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 text-dark fw-bold"><i class="fas fa-stream me-2 text-danger"></i> Atividade Recente dos Alunos</h5>
                        <span class="badge bg-light text-dark border">Últimos 10 registros</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead class="bg-light text-muted small text-uppercase">
                                    <tr>
                                        <th class="ps-4">Horário</th>
                                        <th>Nome do Aluno</th>
                                        <th>Ação Efetuada</th>
                                        <th>Detalhes do Pedido</th>
                                        <th class="text-end pe-4">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($recentLogs) > 0): ?>
                                        <?php foreach ($recentLogs as $log): ?>
                                            <tr>
                                                <td class="ps-4 text-muted small"><?= date('H:i', strtotime($log['created_at'])) ?></td>
                                                <td class="fw-bold"><?= htmlspecialchars($log['name']) ?></td>
                                                <td><span class="badge bg-dark text-white rounded-pill px-3"><?= htmlspecialchars($log['action']) ?></span></td>
                                                <td class="text-muted small"><?= htmlspecialchars($log['details']) ?></td>
                                                <td class="text-end pe-4"><i class="fas fa-check-circle text-success" title="Sincronizado"></i></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-5 text-muted">Nenhuma atividade registrada no aplicativo hoje.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
