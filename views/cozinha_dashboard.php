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
?>
<nav class="navbar navbar-dark bg-dark">
    <div class="container d-flex justify-content-between align-items-center">
        <span class="navbar-brand fatec-title d-flex align-items-center">
            <img src="<?= BASE_URL ?>img/logotipo.png" height="35" class="me-2" style="filter: brightness(0) invert(1);" alt="Logo">
            PAINEL COZINHA
        </span>
        <div class="d-flex align-items-center gap-4">
            <span class="text-white-50 small d-none d-md-inline"><?= date('d/m/Y') ?> | <span id="clock">--:--:--</span></span>
            <a href="<?= BASE_URL ?>cozinha/cardapio" class="btn btn-outline-light btn-sm fatec-title"><i class="fas fa-calendar-alt"></i> MENSAL</a>
            <a href="<?= BASE_URL ?>sair" class="btn btn-outline-danger btn-sm fatec-title"><i class="fas fa-sign-out-alt"></i> SAIR</a>
        </div>

    </div>
</nav>

<div class="container mt-4">
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
                        <a href="<?= BASE_URL ?>administrador" class="btn btn-light btn-sm mt-3 fw-bold">Painel Administrador</a>
                    </div>
                </div>
            </div>

            <!-- Estatísticas Diretas -->
            <div class="col-md-4 mb-4">
                <div class="card card-fatec h-100 p-4 text-center shadow-sm">
                    <h3 class="h6 text-muted">REFEIÇÕES (BASE)</h3>
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

            <!-- Lista de confirmados -->
            <div class="col-12">
                <div class="card card-fatec shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">Histórico de Alterações (Recentes)</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4">Horário</th>
                                        <th>Aluno</th>
                                        <th>Ação Realizada</th>
                                        <th class="text-end pe-4">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="ps-4">18:45</td>
                                        <td class="fw-bold">João Silva (ADS)</td>
                                        <td>Confirmou 2 repetições</td>
                                        <td class="text-end pe-4"><span class="badge bg-success-subtle text-success border border-success px-3">Registrado</span></td>
                                    </tr>
                                    <tr>
                                        <td class="ps-4">18:42</td>
                                        <td class="fw-bold">Maria Oliveira (Logística)</td>
                                        <td>Alterou para 1 repetição</td>
                                        <td class="text-end pe-4"><span class="badge bg-warning-subtle text-warning border border-warning px-3">Alterado</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function updateClock() {
    const clockEl = document.getElementById('clock');
    if (clockEl) {
        const now = new Date();
        clockEl.innerText = now.toLocaleTimeString('pt-BR');
    }
}
setInterval(updateClock, 1000);
updateClock();
</script>
