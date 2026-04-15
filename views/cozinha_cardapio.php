<?php
// views/cozinha_cardapio.php
use App\Core\Database;
$db = Database::getConnection();

// Buscar o cardápio do mês atual
$mesAtual = date('m');
$anoAtual = date('Y');
$stmt = $db->prepare("SELECT date, description FROM menu WHERE MONTH(date) = ? AND YEAR(date) = ? ORDER BY date ASC");
$stmt->execute([$mesAtual, $anoAtual]);
$cardapioMensal = $stmt->fetchAll(PDO::FETCH_ASSOC);

$meses = [
    '01' => 'JANEIRO', '02' => 'FEVEREIRO', '03' => 'MARÇO',
    '04' => 'ABRIL', '05' => 'MAIO', '06' => 'JUNHO',
    '07' => 'JULHO', '08' => 'AGOSTO', '09' => 'SETEMBRO',
    '10' => 'OUTUBRO', '11' => 'NOVEMBRO', '12' => 'DEZEMBRO'
];
$nomeMesAtual = $meses[$mesAtual];
?>
<nav class="navbar navbar-dark bg-dark mb-4">
    <div class="container d-flex justify-content-between align-items-center">
        <a href="<?= BASE_URL ?>cozinha" class="navbar-brand fatec-title d-flex align-items-center text-decoration-none">
            <i class="fas fa-arrow-left me-2"></i> VOLTAR
        </a>
        <div class="d-flex align-items-center gap-4">
            <span class="text-white-50 small d-none d-md-inline"><?= date('d/m/Y') ?></span>
            <a href="<?= BASE_URL ?>sair" class="btn btn-outline-danger btn-sm fatec-title"><i class="fas fa-sign-out-alt"></i> SAIR</a>
        </div>
    </div>
</nav>

<div class="container">
    <div class="row">
        <!-- Coluna de Upload para IA -->
        <div class="col-md-4 mb-4">
            <div class="card card-fatec h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 text-danger"><i class="fas fa-robot me-2"></i> Processar via IA</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small">Faça o upload do cardápio mensal em PDF ou Imagem. Nossa IA analisará o arquivo e organizará os pratos do mês automaticamente no sistema.</p>
                    
                    <?php if (isset($_GET['success'])): ?>
                        <div class="alert alert-success py-2 small"><i class="fas fa-check-circle me-1"></i> Cardápio processado com sucesso!</div>
                    <?php endif; ?>
                    
                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-danger py-2 small"><i class="fas fa-exclamation-triangle me-1"></i> Erro ao processar arquivo.</div>
                    <?php endif; ?>

                    <form action="<?= BASE_URL ?>api/upload_menu.php" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Arquivo do Cardápio</label>
                            <input type="file" class="form-control" name="menu_file" accept=".pdf,.png,.jpg,.jpeg" required>
                        </div>
                        <button type="submit" class="btn btn-fatec w-100">
                            <i class="fas fa-magic me-2"></i> ORGANIZAR COM IA
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Coluna do Cardápio do Mês -->
        <div class="col-md-8 mb-4">
            <div class="card card-fatec h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-calendar-alt text-danger me-2"></i> Cardápio Mensal - <?= $nomeMesAtual ?></h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light sticky-top">
                                <tr>
                                    <th class="ps-4" width="150">Data</th>
                                    <th>Refeição</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($cardapioMensal) > 0): ?>
                                    <?php foreach ($cardapioMensal as $dia): ?>
                                        <tr>
                                            <td class="ps-4 fw-bold text-muted"><?= date('d/m/Y', strtotime($dia['date'])) ?></td>
                                            <td><?= htmlspecialchars($dia['description']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="2" class="text-center py-4 text-muted">Ainda não há cardápio registrado para este mês.</td>
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
