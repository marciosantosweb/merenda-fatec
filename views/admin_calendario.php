<?php 
include __DIR__ . '/partial_admin_menu.php'; 
use App\Core\Database;

$db = Database::getConnection();

// Lógica de Exclusão unificada
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_date'])) {
    $date = $_POST['delete_date'];
    $type = $_POST['delete_type'];
    $table = ($type === 'ia') ? 'academic_calendar' : 'blocked_days';
    
    $stmt = $db->prepare("DELETE FROM $table WHERE date = ?");
    $stmt->execute([$date]);
    echo "<script>window.location.href='".BASE_URL."administrador/calendario?success=removed';</script>";
}

// Inserção Manual
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['manual_date'])) {
    $stmt = $db->prepare("INSERT INTO blocked_days (date, description) VALUES (?, ?) ON DUPLICATE KEY UPDATE description = ?");
    $stmt->execute([$_POST['manual_date'], $_POST['manual_desc'], $_POST['manual_desc']]);
}

// Buscar nome do último arquivo
$stmtFile = $db->query("SELECT config_value FROM settings WHERE config_key = 'last_calendar_file'");
$lastFile = $stmtFile->fetchColumn() ?: 'Nenhum arquivo processado';

// Buscar dias bloqueados combinados
$stmt = $db->query("
    (SELECT date, description, 'manual' as type FROM blocked_days)
    UNION
    (SELECT date, description, 'ia' as type FROM academic_calendar)
    ORDER BY date ASC
");
$blockedDays = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container animated-fade my-5">
    <div class="row">
        <div class="col-12 mb-4">
            <h1 class="h3 fw-bold"><i class="fas fa-calendar-alt text-danger me-2"></i> GESTÃO DE CALENDÁRIO</h1>
            <p class="text-muted small">Gerencie datas suspensas, feriados e emendas identificadas pela IA ou incluídas manualmente.</p>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="col-12">
                <div class="alert alert-success border-0 shadow-sm mb-4"><i class="fas fa-check-circle me-1"></i> Ação realizada com sucesso!</div>
            </div>
        <?php endif; ?>

        <div class="col-lg-5">
            <!-- Upload PDF -->
            <div class="card card-fatec mb-4 border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold small">IMPORTAR CALENDÁRIO (PDF)</h5>
                </div>
                <div class="card-body">
                    <p class="small text-muted">Último arquivo: <strong class="text-danger"><?= htmlspecialchars($lastFile) ?></strong></p>
                    <form action="<?= BASE_URL ?>api/upload_calendar.php" method="POST" enctype="multipart/form-data" id="uploadForm">
                        <div id="dropZone" class="border border-2 border-dashed rounded-4 p-4 text-center mb-3 bg-light position-relative" style="transition: all 0.3s; cursor: pointer;">
                            <i class="fas fa-file-invoice fs-1 text-danger opacity-50 mb-3" id="iconUpload"></i>
                            <p class="small text-muted mb-2" id="textUpload">Arraste o PDF novo aqui ou clique</p>
                            <input type="file" name="calendar_pdf" id="fileInput" class="position-absolute top-0 start-0 w-100 h-100 opacity-0" style="cursor: pointer;" accept=".pdf" required>
                            <div id="fileInfo" class="mt-2 fw-bold text-danger d-none small"></div>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-danger w-100 shadow-sm py-2 rounded-pill fw-bold">
                                <i class="fas fa-robot me-2"></i> ATUALIZAR VIA IA
                            </button>
                            <?php if ($lastFile !== 'Nenhum arquivo processado'): ?>
                                <button type="button" class="btn btn-outline-dark px-3 rounded-pill fw-bold d-flex align-items-center gap-2" onclick="if(confirm('Limpar calendário importado?')) document.getElementById('clearForm').submit();">
                                    <i class="fas fa-trash-alt"></i> <span class="small">LIMPAR TUDO</span>
                                </button>
                            <?php endif; ?>
                        </div>
                    </form>

                    <?php if ($lastFile !== 'Nenhum arquivo processado'): ?>
                        <form id="clearForm" action="<?= BASE_URL ?>api/clear_calendar.php" method="POST" class="d-none"></form>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Inserção Manual -->
            <div class="card card-fatec mb-4 border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold small">BLOQUEIO MANUAL</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Data</label>
                            <input type="date" name="manual_date" class="form-control rounded-3" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Descrição</label>
                            <input type="text" name="manual_desc" class="form-control rounded-3" placeholder="Ex: Emenda de Feriado" required>
                        </div>
                        <button type="submit" class="btn btn-dark w-100 shadow-sm rounded-pill fw-bold">SALVAR DATA NO CALENDÁRIO</button>
                    </form>
                </div>
            </div>

        </div>

        <div class="col-lg-7">
            <div class="card card-fatec shadow-sm border-0">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-0">
                    <h5 class="mb-0 fw-bold">DIAS SEM MERENDA (NÃO LETIVOS/SUSPENSOS)</h5>
                    <span class="badge bg-danger rounded-pill"><?= count($blockedDays) ?> dias</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 700px; overflow-y: auto;">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="bg-light sticky-top">
                                <tr class="small text-muted">
                                    <th class="ps-4">DATA</th>
                                    <th>DESCRIÇÃO / MOTIVO</th>
                                    <th class="text-center">ORIGEM</th>
                                    <th class="text-end pe-4">AÇÃO</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($blockedDays)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-5 text-muted small">Nenhuma data suspensa encontrada.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($blockedDays as $day): ?>
                                        <tr>
                                            <td class="ps-4 fw-bold text-danger small"><?= date('d/m/Y', strtotime($day['date'])) ?></td>
                                            <td class="small"><?= htmlspecialchars($day['description']) ?></td>
                                            <td class="text-center">
                                                <span class="badge <?= $day['type'] === 'ia' ? 'bg-primary-subtle text-primary' : 'bg-warning-subtle text-dark' ?> border rounded-pill px-2" style="font-size: 0.6rem;">
                                                    <?= $day['type'] === 'ia' ? 'IA' : 'MANUAL' ?>
                                                </span>
                                            </td>
                                            <td class="text-end pe-4">
                                                <form method="POST" onsubmit="return confirm('Liberar este dia para janta?')">
                                                    <input type="hidden" name="delete_date" value="<?= $day['date'] ?>">
                                                    <input type="hidden" name="delete_type" value="<?= $day['type'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-secondary border-0 text-muted opacity-75">
                                                        <i class="fas fa-trash-alt me-1"></i> <span class="small">Liberar</span>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
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
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('fileInput');
    const textUpload = document.getElementById('textUpload');
    const fileInfo = document.getElementById('fileInfo');
    const iconUpload = document.getElementById('iconUpload');

    // Impedir comportamento padrão para drag & drop
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, e => {
            e.preventDefault();
            e.stopPropagation();
        }, false);
    });

    // Highlight ao arrastar sobre
    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => {
            dropZone.classList.add('border-danger', 'bg-danger-subtle');
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => {
            dropZone.classList.remove('border-danger', 'bg-danger-subtle');
        }, false);
    });

    // Tratar o DROP do arquivo
    dropZone.addEventListener('drop', e => {
        const dt = e.dataTransfer;
        const files = dt.files;
        fileInput.files = files; // Atribui o arquivo arrastado ao input hidden
        handleFiles(files);
    }, false);

    // Tratar a seleção por CLICK
    fileInput.addEventListener('change', function() {
        handleFiles(this.files);
    });

    function handleFiles(files) {
        if (files.length > 0) {
            const fileName = files[0].name;
            textUpload.classList.add('d-none');
            iconUpload.className = 'fas fa-check-circle fs-1 mb-3 text-success';
            fileInfo.innerText = "Pronto: " + fileName;
            fileInfo.classList.remove('d-none');
        }
    }
</script>
