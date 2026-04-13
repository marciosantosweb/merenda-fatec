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
                        <h6 class="mb-0 text-muted small">Alunos Ativos</h6>
                        <span class="h4 mb-0">342</span>
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
                        <span class="h4 mb-0">128</span>
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
                        <span class="h4 mb-0">210</span>
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
                        <span class="h4 mb-0">338</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Configurações principais -->
        <div class="col-lg-6" id="calendario">
            <div class="card card-fatec mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-calendar-alt text-danger me-2"></i> Calendário Acadêmico</h5>
                    <button class="btn btn-sm btn-outline-fatec">Substituir Arquivo</button>
                </div>
                <div class="card-body">
                    <p class="small text-muted">A IA processará o PDF para sugerir bloqueios automáticos.</p>
                    <div class="border border-2 border-dashed rounded p-4 text-center mb-3">
                        <i class="fas fa-file-pdf fs-2 text-danger mb-2"></i>
                        <h6>Calendário_2026_Fatec.pdf</h6>
                        <span class="badge bg-success">Processado com Sucesso</span>
                    </div>
                </div>
            </div>
            
            <!-- Inserção Manual de Dias Não Letivos -->
            <div class="card card-fatec mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-plus-circle text-danger me-2"></i> Adicionar Dia Não Letivo</h5>
                </div>
                <div class="card-body">
                    <form class="row g-2">
                        <div class="col-md-5">
                            <input type="date" class="form-control" name="data_bloqueio">
                        </div>
                        <div class="col-md-5">
                            <input type="text" class="form-control" placeholder="Motivo (ex: Emenda)">
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-fatec w-100"><i class="fas fa-save"></i></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card card-fatec mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-ban text-danger me-2"></i> Dias Sem Janta (Bloqueados)</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light sticky-top">
                                <tr>
                                    <th>Data</th>
                                    <th>Descrição</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>21/04/2026</td>
                                    <td>Tiradentes (Feriado)</td>
                                    <td><button class="btn btn-sm text-danger"><i class="fas fa-trash"></i></button></td>
                                </tr>
                                <tr>
                                    <td>22/04/2026</td>
                                    <td>Emenda de Feriado</td>
                                    <td><button class="btn btn-sm text-danger"><i class="fas fa-trash"></i></button></td>
                                </tr>
                                <!-- Outras datas seriam carregadas aqui do banco de dados -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4" id="configuracoes">
            <div class="card card-fatec mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-tools text-danger me-2"></i> Parâmetros Sistema</h5>
                </div>
                <div class="card-body">
                    <form action="<?= BASE_URL ?>post_save_settings.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Expiração Login (Dias)</label>
                            <input type="number" class="form-control" name="expiration" value="<?= htmlspecialchars($expiration) ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Início Janela Reserva</label>
                            <input type="time" class="form-control" name="start_time" value="<?= htmlspecialchars($start_time) ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Fim Janela Reserva</label>
                            <input type="time" class="form-control" name="end_time" value="<?= htmlspecialchars($end_time) ?>">
                        </div>
                        <button type="submit" class="btn btn-fatec w-100">Salvar Configurações</button>
                    </form>
                </div>
            </div>

            <!-- Cardápio do Dia -->
            <div class="card card-fatec mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-utensils text-danger me-2"></i> Cardápio do Dia</h5>
                </div>
                <div class="card-body">
                    <?php if (date('N') >= 6): ?>
                        <div class="alert alert-warning py-2 small mb-0">Fim de semana, não há cardápio.</div>
                    <?php else: ?>
                        <form action="<?= BASE_URL ?>post_save_menu.php" method="POST">
                            <div class="mb-3">
                                <label class="form-label small fw-bold">O que teremos hoje?</label>
                                <textarea class="form-control" name="menu_description" rows="3" placeholder="Ex: Arroz, feijão, frango e salada."><?= htmlspecialchars($menuText) ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-dark w-100">Salvar Cardápio</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
