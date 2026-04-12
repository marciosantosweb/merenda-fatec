<?php 
include __DIR__ . '/partial_admin_menu.php'; 
use App\Core\Database;

$db = Database::getConnection();

// Lógica de Exclusão
if (isset($_GET['delete'])) {
    $stmt = $db->prepare("DELETE FROM academic_calendar WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    echo "<script>window.location.href='".BASE_URL."administrador/calendario';</script>";
}

// Lógica de Inserção Manual
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['manual_date'])) {
    $stmt = $db->prepare("INSERT IGNORE INTO academic_calendar (date, description) VALUES (?, ?)");
    $stmt->execute([$_POST['manual_date'], $_POST['manual_desc']]);
}

// Buscar dias bloqueados
$stmt = $db->query("SELECT * FROM academic_calendar ORDER BY date ASC");
$blockedDays = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container animated-fade">
    <div class="row">
        <div class="col-12 mb-4">
            <h1 class="h3 fatec-title"><i class="fas fa-calendar-alt text-danger"></i> Gestão de Calendário</h1>
            <p class="text-muted">As datas abaixo estão <strong>bloqueadas</strong> para reserva de merenda.</p>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="col-12">
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i> Calendário processado com inteligência! As datas acadêmicas foram identificadas.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        <?php endif; ?>

        <div class="col-lg-5">
            <!-- Upload PDF -->
            <div class="card card-fatec mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Importar Calendário PDF</h5>
                </div>
                <div class="card-body">
                    <form action="<?= BASE_URL ?>api/upload_calendar.php" method="POST" enctype="multipart/form-data" id="uploadForm">
                        <div id="dropZone" class="border border-2 border-dashed rounded p-4 text-center mb-3 position-relative" style="transition: all 0.3s; cursor: pointer;">
                            <i class="fas fa-file-invoice fs-1 text-danger opacity-50 mb-3" id="iconUpload"></i>
                            <p class="small text-muted mb-2" id="textUpload">Arraste o PDF aqui ou clique para selecionar</p>
                            <input type="file" name="calendar_pdf" id="fileInput" class="position-absolute top-0 start-0 w-100 h-100 opacity-0" style="cursor: pointer;" accept=".pdf" required>
                            <div id="fileInfo" class="mt-2 fw-bold text-danger d-none small"></div>
                        </div>
                        <button type="submit" class="btn btn-fatec w-100 shadow-sm py-2">
                            <i class="fas fa-robot me-2"></i> PROCESSAR COM IA
                        </button>
                    </form>
                </div>

                <script>
                    const dropZone = document.getElementById('dropZone');
                    const fileInput = document.getElementById('fileInput');
                    const textUpload = document.getElementById('textUpload');
                    const fileInfo = document.getElementById('fileInfo');
                    const iconUpload = document.getElementById('iconUpload');

                    // Highlight ao arrastar sobre
                    ['dragover', 'mouseenter'].forEach(event => {
                        dropZone.addEventListener(event, () => {
                            dropZone.classList.add('bg-light', 'border-danger');
                        });
                    });

                    ['dragleave', 'mouseleave', 'drop'].forEach(event => {
                        dropZone.addEventListener(event, () => {
                            dropZone.classList.remove('bg-light', 'border-danger');
                        });
                    });

                    // Mostrar nome do arquivo selecionado
                    fileInput.addEventListener('change', () => {
                        if (fileInput.files.length > 0) {
                            const fileName = fileInput.files[0].name;
                            textUpload.classList.add('d-none');
                            iconUpload.classList.remove('text-danger');
                            iconUpload.classList.add('text-success');
                            iconUpload.className = 'fas fa-check-circle fs-1 mb-3 text-success';
                            
                            fileInfo.innerText = "Arquivo: " + fileName;
                            fileInfo.classList.remove('d-none');
                        }
                    });
                </script>
            </div>

            <!-- Inserção Manual -->
            <div class="card card-fatec mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Bloqueio Manual de Data</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Data do Bloqueio</label>
                            <input type="date" name="manual_date" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Motivo / Descrição</label>
                            <input type="text" name="manual_desc" class="form-control" placeholder="Ex: Emenda de Feriado" required>
                        </div>
                        <button type="submit" class="btn btn-dark w-100 shadow-sm">Bloquear Data</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card card-fatec shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Dias Sem Merenda (Letivos/Suspensos)</h5>
                    <span class="badge bg-danger rounded-pill"><?= count($blockedDays) ?> dias</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 500px;">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light sticky-top">
                                <tr>
                                    <th class="ps-4">Data</th>
                                    <th>Descrição</th>
                                    <th class="text-end pe-4">Ação</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($blockedDays)): ?>
                                    <tr>
                                        <td colspan="3" class="text-center py-5 text-muted small">Nenhuma data bloqueada encontrada.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($blockedDays as $day): ?>
                                        <tr>
                                            <td class="ps-4 fw-bold text-danger"><?= date('d/m/Y', strtotime($day['date'])) ?></td>
                                            <td class="small"><?= htmlspecialchars($day['description']) ?></td>
                                            <td class="text-end pe-4">
                                                <a href="?delete=<?= $day['id'] ?>" class="btn btn-sm btn-outline-secondary" onclick="return confirm('Deseja liberar este dia para janta?')">
                                                    <i class="fas fa-trash-alt"></i>
                                                </a>
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
