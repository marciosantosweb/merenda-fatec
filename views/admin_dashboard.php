<?php
// views/admin_dashboard.php
?>
<?php include __DIR__ . '/partial_admin_menu.php'; ?>

<?php
use App\Core\Database;
$db = Database::getConnection();

// Carregar configurações
$stmtConfig = $db->query("SELECT config_key, config_value FROM settings");
$settingsArray = $stmtConfig->fetchAll(PDO::FETCH_KEY_PAIR);

// Bloqueios
$stmt = $db->query("SELECT * FROM blocked_days ORDER BY date DESC LIMIT 10");
$blocked_days = $stmt->fetchAll();

// Estatísticas do dia
$hoje = date('Y-m-d');
$stmtStats = $db->prepare("
    SELECT 
        COUNT(*) as total_reservas,
        SUM(repetitions) as total_repeticoes
    FROM reservations 
    WHERE date = ?
");
$stmtStats->execute([$hoje]);
$stats = $stmtStats->fetch();

$total_reservas = $stats['total_reservas'] ?? 0;
$total_repeticoes = $stats['total_repeticoes'] ?? 0;
$total_pratos = $total_reservas + $total_repeticoes;

// Buscar Total de Usuários Ativos
$stmtUsers = $db->query("SELECT COUNT(*) FROM users WHERE status = 'active' AND role = 'aluno'");
$total_usuarios = $stmtUsers->fetchColumn();

$expiration = $settingsArray['login_expiration_days'] ?? 30;
$start_time = date('H:i', strtotime($settingsArray['reservation_start'] ?? '18:00:00'));
$end_time = date('H:i', strtotime($settingsArray['reservation_end'] ?? '19:30:00'));

// Carregar Menu do dia
$today = date('Y-m-d');
$stmtMenu = $db->prepare("SELECT description FROM menu WHERE date = ?");
$stmtMenu->execute([$today]);
$menuToday = $stmtMenu->fetch();
$menuText = $menuToday ? $menuToday['description'] : '';
?>

<div class="container my-5">
    <div class="row animated-fade" id="estatisticas">
        <div class="col-12 mb-4">
            <h1 class="h3">Painel Administrativo</h1>
            <p class="text-muted">Gestão do sistema de merenda noturna.</p>
        </div>

        <!-- Cards de Estatísticas Rápidas -->
        <div class="col-md-3 mb-4">
            <div class="card card-fatec p-3 border-start border-5 border-primary">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 bg-primary-subtle p-3 rounded-circle">
                        <i class="fas fa-user-graduate text-primary fs-4"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-0 text-muted small">Alunos/Usuários Ativos</h6>
                        <span class="h4 mb-0"><?= $total_usuarios ?></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card card-fatec p-3 border-start border-5 border-success">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 bg-success-subtle p-3 rounded-circle">
                        <i class="fas fa-check-circle text-success fs-4"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-0 text-muted small">Reservas Hoje</h6>
                        <span class="h4 mb-0"><?= $total_reservas ?></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card card-fatec p-3 border-start border-5 border-warning">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 bg-warning-subtle p-3 rounded-circle">
                        <i class="fas fa-redo text-warning fs-4"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-0 text-muted small">Repetições Totais</h6>
                        <span class="h4 mb-0"><?= $total_repeticoes ?></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card card-fatec p-3 border-start border-5 border-danger">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 bg-danger-subtle p-3 rounded-circle">
                        <i class="fas fa-hamburger text-danger fs-4"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-0 text-muted small">Pratos Produzir</h6>
                        <span class="h4 mb-0"><?= $total_pratos ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= Configurações principais ================= -->
        <!-- COLUNA 1: Calendário e Bloqueios -->
        <div class="col-lg-4" id="calendario">
            <div class="card card-fatec mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-calendar-alt text-danger me-2"></i> Calendário Acadêmico</h5>
                </div>
                <div class="card-body">
                    <p class="small text-muted">A IA processará o PDF para sugerir bloqueios automáticos.</p>
                    <div class="border border-2 border-dashed rounded p-4 text-center mb-3 bg-light">
                        <i class="fas fa-file-pdf fs-1 text-danger mb-2"></i>
                        <h6>Calendário_2026.pdf</h6>
                        <span class="badge bg-success mt-1"><i class="fas fa-check-circle"></i> Processado</span>
                    </div>
                    <button class="btn btn-sm btn-outline-fatec w-100"><i class="fas fa-upload me-2"></i> Substituir Arquivo</button>
                </div>
            </div>
            
            <!-- Inserção Manual de Dias Não Letivos -->
            <div class="card card-fatec mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-calendar-times text-danger me-2"></i> Adicionar Dia Não Letivo</h5>
                </div>
                <div class="card-body">
                    <form class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted mb-1">Data</label>
                            <input type="date" class="form-control" name="data_bloqueio">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted mb-1">Motivo / Descrição</label>
                            <input type="text" class="form-control" placeholder="ex: Emenda de Feriado">
                        </div>
                        <div class="col-12 mt-4">
                            <button class="btn btn-fatec w-100"><i class="fas fa-plus-circle me-2"></i> ADICIONAR BLOQUEIO</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- COLUNA 2: Cardápio e Parâmetros -->
        <div class="col-lg-4" id="configuracoes">
            <!-- Cardápio do Dia -->
            <div class="card card-fatec mb-4 border-danger border-opacity-50">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-utensils text-danger me-2"></i> Cardápio do Dia</h5>
                </div>
                <div class="card-body">
                    <?php if (date('N') >= 6): ?>
                        <div class="alert alert-warning py-3 text-center mb-0">
                            <i class="fas fa-glass-cheers fs-4 d-block mb-2 text-warning"></i>
                            <strong>Fim de semana</strong><br>Não há cardápio.
                        </div>
                    <?php else: ?>
                        <form action="<?= BASE_URL ?>post_save_menu.php" method="POST">
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted mb-2">O que teremos hoje?</label>
                                <textarea class="form-control border-danger border-opacity-25 bg-light" name="menu_description" rows="4" placeholder="Ex: Arroz, feijão, frango e salada."><?= htmlspecialchars($menuText) ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-dark w-100"><i class="fas fa-save me-2"></i> SALVAR CARDÁPIO</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Parâmetros Sistema -->
            <div class="card card-fatec mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-cogs text-danger me-2"></i> Parâmetros Sistema</h5>
                </div>
                <div class="card-body">
                    <form action="<?= BASE_URL ?>post_save_settings.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Expiração Login (Dias)</label>
                            <input type="number" class="form-control" name="expiration" value="<?= htmlspecialchars((string)$expiration) ?>">
                        </div>
                        <div class="row g-2 mb-4">
                            <div class="col-6">
                                <label class="form-label small fw-bold text-muted">Início Janela</label>
                                <input type="time" class="form-control" name="start_time" value="<?= htmlspecialchars($start_time) ?>">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold text-muted">Fim Janela</label>
                                <input type="time" class="form-control" name="end_time" value="<?= htmlspecialchars($end_time) ?>">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-outline-fade text-dark border-secondary w-100"><i class="fas fa-save me-2"></i> ATUALIZAR REGRAS</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- COLUNA 3: Dias Bloqueados Tabela -->
        <div class="col-lg-4">
            <div class="card card-fatec mb-4 h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <?php
                    $meses = [
                        '01' => 'JANEIRO', '02' => 'FEVEREIRO', '03' => 'MARÇO',
                        '04' => 'ABRIL', '05' => 'MAIO', '06' => 'JUNHO',
                        '07' => 'JULHO', '08' => 'AGOSTO', '09' => 'SETEMBRO',
                        '10' => 'OUTUBRO', '11' => 'NOVEMBRO', '12' => 'DEZEMBRO'
                    ];
                    $mesAtual = $meses[date('m')];
                    ?>
                    <h5 class="mb-0"><i class="fas fa-calendar-times text-danger me-2"></i> SEM JANTA (<?= $mesAtual ?>)</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 520px; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light sticky-top">
                                <tr>
                                    <th class="ps-4">Data</th>
                                    <th>Descrição</th>
                                    <th class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="ps-4 fw-bold text-muted">21/<?= date('m/Y') ?></td>
                                    <td>Tiradentes (Feriado)</td>
                                    <td class="text-center"><button class="btn btn-sm btn-outline-danger border-0"><i class="fas fa-trash"></i></button></td>
                                </tr>
                                <tr>
                                    <td class="ps-4 fw-bold text-muted">22/<?= date('m/Y') ?></td>
                                    <td>Emenda de Feriado</td>
                                    <td class="text-center"><button class="btn btn-sm btn-outline-danger border-0"><i class="fas fa-trash"></i></button></td>
                                </tr>
                                <!-- Outras datas seriam carregadas aqui do banco de dados -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
