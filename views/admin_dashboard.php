<?php
use App\Core\Database;
$db = Database::getConnection();

// Carregar configurações
$stmtConfig = $db->query("SELECT config_key, config_value FROM settings");
$settingsArray = $stmtConfig->fetchAll(PDO::FETCH_KEY_PAIR);

// Bloqueios Combinados (Manual + Automático IA)
$mesFiltro = date('m');
$stmt = $db->prepare("
    (SELECT id, date, description, 'manual' as type FROM blocked_days WHERE MONTH(date) = ?)
    UNION
    (SELECT id, date, description, 'ia' as type FROM academic_calendar WHERE MONTH(date) = ?)
    ORDER BY date ASC
");
$stmt->execute([$mesFiltro, $mesFiltro]);
$blocked_days = $stmt->fetchAll();

// Verificar se existe calendário IA carregado
$stmtCheckCal = $db->query("SELECT COUNT(*) FROM academic_calendar");
$has_academic_cal = $stmtCheckCal->fetchColumn() > 0;

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

// Carregar Menu do dia e verificar se foi via IA
$today = date('Y-m-d');
$stmtMenu = $db->prepare("SELECT description FROM menu WHERE date = ?");
$stmtMenu->execute([$today]);
$menuToday = $stmtMenu->fetch();
$menuText = $menuToday ? $menuToday['description'] : '';

// Verificação simples: se temos mais de 10 dias de menu para esse mês, assumimos que foi processamento em lote (IA)
$stmtIA = $db->prepare("SELECT COUNT(*) FROM menu WHERE MONTH(date) = ?");
$stmtIA->execute([date('m')]);
$is_ai_imported = $stmtIA->fetchColumn() > 10;
?>

<?php include __DIR__ . '/partial_admin_menu.php'; ?>

<div class="container my-5 pb-5">
    <div class="row animated-fade" id="estatisticas">
        <div class="col-12 mb-4 d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 fw-bold m-0">Painel Administrativo</h1>
                <p class="text-muted small mb-0">Gestão integrada do sistema de merenda.</p>
            </div>
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success py-2 px-3 mb-0 small"><i class="fas fa-check-circle me-1"></i> Ação realizada com sucesso!</div>
            <?php endif; ?>
        </div>

        <!-- Cards de Estatísticas Rápidas -->
        <div class="col-md-3 mb-4">
            <div class="card card-fatec p-3 border-start border-5 border-primary shadow-sm h-100">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 bg-primary-subtle p-3 rounded-circle" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-user-graduate text-primary fs-5"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-0 text-muted small">Alunos Ativos</h6>
                        <span class="h4 mb-0 fw-bold"><?= $total_usuarios ?></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card card-fatec p-3 border-start border-5 border-success shadow-sm h-100">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 bg-success-subtle p-3 rounded-circle" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-check-circle text-success fs-5"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-0 text-muted small">Reservas Hoje</h6>
                        <span class="h4 mb-0 fw-bold text-success"><?= $total_reservas ?></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card card-fatec p-3 border-start border-5 border-warning shadow-sm h-100">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 bg-warning-subtle p-3 rounded-circle" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-redo text-warning fs-5"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-0 text-muted small">Repetições Hoje</h6>
                        <span class="h4 mb-0 fw-bold"><?= $total_repeticoes ?></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card card-fatec p-3 border-start border-5 border-danger shadow-sm h-100">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 bg-danger-subtle p-3 rounded-circle" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-hamburger text-danger fs-5"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-0 text-muted small">Pratos Hoje</h6>
                        <span class="h4 mb-0 fw-bold text-danger"><?= $total_pratos ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= Configurações principais ================= -->
        <!-- COLUNA 1: Calendário e Bloqueios -->
        <div class="col-lg-4" id="calendario">
            <div class="card card-fatec mb-4 border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-calendar-alt text-danger me-2"></i> CALENDÁRIO ACADÊMICO</h5>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-3">A IA processará o PDF para sugerir bloqueios automáticos no cronograma móvel.</p>
                    
                    <form action="<?= BASE_URL ?>api/upload_calendar.php" method="POST" enctype="multipart/form-data" id="dashUploadForm">
                        <div id="dashDropZone" class="border border-2 border-dashed rounded-4 p-4 text-center mb-3 position-relative <?= $has_academic_cal ? 'bg-light border-success border-opacity-25' : 'bg-white' ?>" style="transition: all 0.3s; cursor: pointer;">
                            <?php if ($has_academic_cal): ?>
                                <i class="fas fa-file-pdf fs-1 text-success opacity-50 mb-2" id="iconUpload"></i>
                                <h6 class="fw-bold mb-1">Calendário do Ano</h6>
                                <span class="badge bg-success rounded-pill px-3 py-2"><i class="fas fa-check-circle me-1"></i> Processado via IA</span>
                            <?php else: ?>
                                <i class="fas fa-file-upload fs-1 text-muted opacity-25 mb-2" id="iconUpload"></i>
                                <h6 class="text-muted small" id="textUpload">Arraste o PDF ou clique</h6>
                            <?php endif; ?>
                            <input type="file" name="calendar_pdf" id="dashFileInput" class="position-absolute top-0 start-0 w-100 h-100 opacity-0" style="cursor: pointer;" accept=".pdf" required onchange="this.form.submit()">
                            <div id="fileInfo" class="mt-2 fw-bold text-danger d-none small"></div>
                        </div>
                    </form>
                    
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-danger w-100 py-2 rounded-pill fw-bold" onclick="document.getElementById('dashFileInput').click()">
                            <i class="fas fa-sync-alt me-1"></i> Carregar Arquivo
                        </button>
                        <?php if ($has_academic_cal): ?>
                            <form action="<?= BASE_URL ?>api/clear_calendar.php" method="POST" class="w-100" onsubmit="return confirm('Apagar todos os dias bloqueados pela IA?')">
                                <button type="submit" class="btn btn-sm btn-dark w-100 py-2 rounded-pill fw-bold">
                                    <i class="fas fa-trash-alt me-1"></i> Remover
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Inserção Manual de Dias Não Letivos -->
            <div class="card card-fatec mb-4 border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-calendar-times text-danger me-2"></i> ADICIONAR BLOQUEIO MANUAL</h5>
                </div>
                <div class="card-body">
                    <form action="<?= BASE_URL ?>public/post_admin_actions.php?action=add_block" method="POST" class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted mb-1">Data Específica</label>
                            <input type="date" class="form-control rounded-3" name="data_bloqueio" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted mb-1">Motivo (visível no app)</label>
                            <input type="text" class="form-control rounded-3" name="description" placeholder="ex: Reunião Pedagógica" required>
                        </div>
                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-danger w-100 py-2 fw-bold shadow-sm rounded-pill"><i class="fas fa-plus-circle me-2"></i> SALVAR BLOQUEIO</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- COLUNA 2: Cardápio e Parâmetros -->
        <div class="col-lg-4" id="configuracoes">
            <!-- Cardápio do Dia -->
            <div class="card card-fatec mb-4 border-0 shadow-sm overflow-hidden">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-utensils text-danger me-2"></i> CARDÁPIO DO DIA</h5>
                    <?php if ($is_ai_imported): ?>
                        <span class="badge bg-info-subtle text-info border border-info rounded-pill px-2" style="font-size: 0.65rem;">
                            <i class="fas fa-robot me-1"></i> IMPORTADO VIA IA
                        </span>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (date('N') >= 6): ?>
                        <div class="alert alert-warning py-4 text-center mb-0 rounded-4">
                            <i class="fas fa-glass-cheers fs-1 d-block mb-3 text-warning opacity-50"></i>
                            <strong>Hoje é Final de Semana</strong><br><span class="small opacity-75">O sistema de merenda não abre para reservas.</span>
                        </div>
                    <?php else: ?>
                        <form action="<?= BASE_URL ?>public/post_save_menu.php" method="POST">
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted mb-2">O que teremos hoje?</label>
                                <textarea class="form-control border-light-subtle bg-light rounded-4 p-3" name="menu_description" rows="5" placeholder="Ex: Arroz, feijão, frango e salada." style="resize: none;"><?= htmlspecialchars($menuText) ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-dark w-100 py-3 fw-bold rounded-pill shadow-sm"><i class="fas fa-save me-2"></i> SALVAR ALTERAÇÃO</button>
                            <?php if ($is_ai_imported): ?>
                                <div class="text-center mt-2">
                                    <small class="text-muted" style="font-size: 0.75rem;">Nota: O cardápio acima foi preenchido automaticamente via IA.</small>
                                </div>
                            <?php endif; ?>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Parâmetros Sistema -->
            <div class="card card-fatec mb-4 border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-cogs text-danger me-2"></i> PARÂMETROS GERAIS</h5>
                </div>
                <div class="card-body">
                    <form action="<?= BASE_URL ?>public/post_save_settings.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Expiração de Login (dias)</label>
                            <input type="number" class="form-control rounded-3" name="expiration" value="<?= htmlspecialchars((string)$expiration) ?>">
                        </div>
                        <div class="row g-2 mb-4">
                            <div class="col-6">
                                <label class="form-label small fw-bold text-muted">Abertura Janela</label>
                                <input type="time" class="form-control rounded-3" name="start_time" value="<?= htmlspecialchars($start_time) ?>">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold text-muted">Fechamento</label>
                                <input type="time" class="form-control rounded-3" name="end_time" value="<?= htmlspecialchars($end_time) ?>">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-outline-dark w-100 py-2 fw-bold rounded-pill"><i class="fas fa-save me-2"></i> ATUALIZAR REGRAS</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- COLUNA 3: Dias Bloqueados Tabela -->
        <div class="col-lg-4">
            <div class="card card-fatec mb-4 h-100 border-0 shadow-sm">
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
                    <h5 class="mb-0 fw-bold"><i class="fas fa-calendar-times text-danger me-2"></i> DIAS SEM JANTA (<?= $mesAtual ?>)</h5>
                    <span class="badge bg-light text-dark border rounded-pill"><?= count($blocked_days) ?> dias</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 800px; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light sticky-top">
                                <tr class="small text-muted text-uppercase">
                                    <th class="ps-4">Data</th>
                                    <th>Descrição</th>
                                    <th class="text-center">Tipo</th>
                                    <th class="text-end pe-4">Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($blocked_days) > 0): ?>
                                    <?php foreach ($blocked_days as $day): ?>
                                        <tr>
                                            <td class="ps-4 fw-bold text-muted small"><?= date('d/m/Y', strtotime($day['date'])) ?></td>
                                            <td class="small"><?= htmlspecialchars($day['description']) ?></td>
                                            <td class="text-center">
                                                <span class="badge <?= $day['type'] === 'ia' ? 'bg-primary-subtle text-primary border-primary' : 'bg-warning-subtle text-warning border-warning' ?> border" style="font-size: 0.65rem;">
                                                    <?= $day['type'] === 'ia' ? 'IA' : 'MANUAL' ?>
                                                </span>
                                            </td>
                                            <td class="text-end pe-4">
                                                <form action="<?= BASE_URL ?>public/post_admin_actions.php?action=remove_block&date=<?= $day['date'] ?>&type=<?= $day['type'] ?>" method="POST" onsubmit="return confirm('Liberar este dia para janta?')">
                                                    <button type="submit" class="btn btn-sm btn-outline-secondary border-0 text-muted">
                                                        <i class="fas fa-trash-alt me-1"></i> <span class="small">Liberar</span>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-5 text-muted small">Nenhum dia bloqueado para este mês.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // JS para Drag and Drop no Dashboard
    const dashDropZone = document.getElementById('dashDropZone');
    const dashFileInput = document.getElementById('dashFileInput');
    const dashUploadForm = document.getElementById('dashUploadForm');

    if (dashDropZone) {
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dashDropZone.addEventListener(eventName, e => {
                e.preventDefault();
                e.stopPropagation();
            }, false);
        });

        ['dragenter', 'dragover'].forEach(eventName => {
            dashDropZone.addEventListener(eventName, () => {
                dashDropZone.classList.add('border-danger', 'bg-danger-subtle');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dashDropZone.addEventListener(eventName, () => {
                dashDropZone.classList.remove('border-danger', 'bg-danger-subtle');
            }, false);
        });

        dashDropZone.addEventListener('drop', e => {
            const dt = e.dataTransfer;
            dashFileInput.files = dt.files;
            dashUploadForm.submit();
        }, false);
    }
</script>
