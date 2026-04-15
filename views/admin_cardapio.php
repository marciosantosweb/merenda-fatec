<?php
// views/admin_cardapio.php
?>
<?php include __DIR__ . '/partial_admin_menu.php'; ?>

<?php
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

<div class="container my-5">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3"><i class="fas fa-utensils text-danger me-2"></i> Cardápio Mensal</h1>
            <p class="text-muted">A cozinha sincroniza esses dados via upload de IA. Você pode visualizar o cardápio do mês (<?= $nomeMesAtual ?>) abaixo.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card card-fatec h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Pratos Programados</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light sticky-top">
                                <tr>
                                    <th class="ps-4" width="150">Data</th>
                                    <th>Refeição Sugerida</th>
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
