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
<?php include __DIR__ . '/partial_cozinha_menu.php'; ?>

<div class="container pb-5">
    <div class="row">
        <!-- Coluna de Upload para IA -->
        <div class="col-md-5 mb-4">
            <div class="card card-fatec h-100 border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="mb-0 text-danger fw-bold"><i class="fas fa-magic me-2"></i> ATUALIZAR CARDÁPIO MENSAL</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small">Arraste e solte o arquivo do cardápio oficial (PDF ou Imagem) abaixo. Nossa IA irá processar os pratos e organizar todo o calendário de <?= $nomeMesAtual ?> automaticamente.</p>
                    
                    <?php if (isset($_GET['success']) && $_GET['success'] === 'api_processed'): ?>
                        <div class="alert alert-success py-3 small shadow-sm"><i class="fas fa-check-circle me-1"></i> Cardápio processado com sucesso! Os pratos já foram sincronizados com o aplicativo.</div>
                    <?php endif; ?>

                    <?php if (isset($_GET['success']) && $_GET['success'] === 'menu_cleared'): ?>
                        <div class="alert alert-warning py-3 small shadow-sm"><i class="fas fa-trash-alt me-1"></i> Cardápio removido com sucesso. Os dias estão em branco no aplicativo.</div>
                    <?php endif; ?>
                    
                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-danger py-3 small shadow-sm"><i class="fas fa-exclamation-triangle me-1"></i> Erro ao processar arquivo. Verifique se o formato é válido.</div>
                    <?php endif; ?>

                    <form action="<?= BASE_URL ?>api/upload_menu.php" method="POST" enctype="multipart/form-data" id="upload-form">
                        <!-- Drag and Drop Area -->
                        <div id="drop-area" class="border border-2 border-dashed rounded-4 p-5 text-center bg-light mb-3 transition-all" style="cursor: pointer;">
                            <input type="file" id="fileElem" name="menu_file" accept=".pdf,.png,.jpg,.jpeg" required style="display:none" onchange="handleFiles(this.files)">
                            <div class="py-4">
                                <i class="fas fa-cloud-upload-alt fs-1 text-danger opacity-50 mb-3"></i>
                                <h6 class="fw-bold">Arraste o arquivo aqui</h6>
                                <p class="text-muted small mb-0">ou clique para selecionar no computador</p>
                                <div id="file-name" class="mt-3 badge bg-dark d-none"></div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-danger w-100 py-3 fw-bold rounded-pill shadow-sm" id="submit-btn" disabled>
                            <i class="fas fa-robot me-2"></i> RE-SINCRONIZAR CARDÁPIO COM IA
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Coluna do Cardápio do Mês -->
        <div class="col-md-7 mb-4">
            <div class="card card-fatec h-100 border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-clipboard-list text-danger me-2"></i> Cardápio Atual (<?= $nomeMesAtual ?>)</h5>
                    <div class="d-flex gap-2 align-items-center">
                        <span class="badge bg-danger rounded-pill"><?= count($cardapioMensal) ?> dias</span>
                        <?php if (count($cardapioMensal) > 0): ?>
                            <form action="<?= BASE_URL ?>api/clear_menu.php" method="POST" onsubmit="return confirm('Tem certeza que deseja APAGAR TODO o cardápio? Isso não pode ser desfeito.')">
                                <button type="submit" class="btn btn-sm btn-outline-danger opacity-75 hover-opacity-100" title="Apagar tudo">
                                    <i class="fas fa-trash-alt me-1"></i> Limpar Tudo
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 550px; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light sticky-top">
                                <tr class="small text-uppercase text-muted">
                                    <th class="ps-4" width="130">Data</th>
                                    <th>Refeição no Aplicativo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($cardapioMensal) > 0): ?>
                                    <?php foreach ($cardapioMensal as $dia): ?>
                                        <tr>
                                            <td class="ps-4 fw-bold text-muted small"><?= date('d/m/Y', strtotime($dia['date'])) ?></td>
                                            <td class="small"><?= htmlspecialchars($dia['description']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="2" class="text-center py-5 text-muted">
                                            <i class="fas fa-utensils fs-1 d-block mb-3 opacity-25"></i>
                                            Ainda não há cardápio registrado para este mês.<br>
                                            <small>Utilize o formulário ao lado para carregar o cardápio.</small>
                                        </td>
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

<style>
#drop-area.highlight { border-color: #B50D11 !important; background-color: #fff5f5 !important; }
.transition-all { transition: all 0.3s ease; }
</style>

<script>
let dropArea = document.getElementById('drop-area');
let fileElem = document.getElementById('fileElem');
let fileNameDisplay = document.getElementById('file-name');
let submitBtn = document.getElementById('submit-btn');

// Drag and drop prevent defaults
['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
  dropArea.addEventListener(eventName, preventDefaults, false);
});

function preventDefaults (e) {
  e.preventDefault();
  e.stopPropagation();
}

// Highlight area
['dragenter', 'dragover'].forEach(eventName => {
  dropArea.addEventListener(eventName, () => dropArea.classList.add('highlight'), false);
});

['dragleave', 'drop'].forEach(eventName => {
  dropArea.addEventListener(eventName, () => dropArea.classList.remove('highlight'), false);
});

// Handle dropped files
dropArea.addEventListener('drop', handleDrop, false);
dropArea.addEventListener('click', () => fileElem.click());

function handleDrop(e) {
  let dt = e.dataTransfer;
  let files = dt.files;
  handleFiles(files);
}

function handleFiles(files) {
  if (files.length > 0) {
    fileElem.files = files; // Sync input file
    fileNameDisplay.innerText = files[0].name;
    fileNameDisplay.classList.remove('d-none');
    submitBtn.disabled = false;
  }
}
</script>
