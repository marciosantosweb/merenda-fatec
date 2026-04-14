<?php
// views/admin_alunos.php
use App\Core\Database;
$db = Database::getConnection();

// Silent DB Migration: add last_login if not exists
try {
    $db->exec("ALTER TABLE users ADD COLUMN last_login TIMESTAMP NULL");
} catch (\Exception $e) {}

// Fetch all non-admin, non-system users
$stmt = $db->query("SELECT * FROM users WHERE role IN ('aluno') ORDER BY name ASC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include __DIR__ . '/partial_admin_menu.php'; ?>

<div class="container my-5">
<div class="row animated-fade">
    <div class="col-12 mb-4">
        <h1 class="h3 fatec-title mb-2"><i class="fas fa-users text-danger me-2"></i> GESTÃO DE USUÁRIOS</h1>
        <div class="alert alert-light border-start border-4 border-danger py-2 mb-0 shadow-sm">
            <i class="fas fa-info-circle text-danger me-2"></i>
            <span class="text-muted">Habilite ou desabilite o acesso de estudantes ou professores. Conta desativada não consegue realizar novas reservas.</span>
        </div>
    </div>

    <!-- Busca e Filtro -->
    <div class="col-12 mb-4">
        <div class="card card-fatec shadow-sm">
            <div class="card-body p-2">
                <div class="d-flex gap-2">
                    <div class="input-group input-group-lg flex-grow-1">
                        <span class="input-group-text bg-white border-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" id="searchInput" class="form-control border-0 shadow-none" placeholder="Buscar por nome ou e-mail...">
                    </div>
                    <select id="filterStatus" class="form-select form-select-lg" style="max-width: 180px;">
                        <option value="">Todos os Status</option>
                        <option value="Ativo">Ativos</option>
                        <option value="Inativo">Inativos</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabela de Usuários -->
    <div class="col-12">
        <div class="card card-fatec shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="usersTable">
                        <thead class="bg-light">
                            <tr style="cursor: pointer; user-select: none;">
                                <th class="ps-4" onclick="sortTable(0)">Nome do Usuário <i class="fas fa-sort text-muted ms-1"></i></th>
                                <th onclick="sortTable(1)">E-mail <i class="fas fa-sort text-muted ms-1"></i></th>
                                <th onclick="sortTable(2)">Último Acesso <i class="fas fa-sort text-muted ms-1"></i></th>
                                <th onclick="sortTable(3)">Status <i class="fas fa-sort text-muted ms-1"></i></th>
                                <th class="text-center px-4">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="fas fa-users fa-3x mb-3 d-block opacity-25"></i>
                                    Nenhum usuário cadastrado ainda.<br>
                                    <small>Os usuários aparecem aqui automaticamente após o primeiro login pelo aplicativo.</small>
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach($users as $user):
                                $lastLogin = $user['last_login'] ? date('d/m/y H:i', strtotime($user['last_login'])) : 'Nunca';
                                $isActive  = $user['status'] === 'active';
                            ?>
                            <tr class="<?= !$isActive ? 'bg-light-subtle' : '' ?>" data-user-id="<?= $user['id'] ?>">
                                <td class="ps-4 fw-bold <?= !$isActive ? 'text-muted' : '' ?>">
                                    <?= htmlspecialchars($user['name']) ?>
                                </td>
                                <td class="<?= !$isActive ? 'text-muted' : '' ?>">
                                    <?= htmlspecialchars($user['email']) ?>
                                </td>
                                <td class="text-muted">
                                    <small><?= $lastLogin ?></small>
                                </td>
                                <td class="status-cell">
                                    <?php if($isActive): ?>
                                        <span class="badge bg-success-subtle text-success border border-success px-3">Ativo</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary px-3">Inativo</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center px-4">
                                    <?php if($isActive): ?>
                                        <button class="btn btn-sm btn-outline-danger w-100 toggle-btn"
                                                data-user-id="<?= $user['id'] ?>"
                                                data-action="deactivate">
                                            DESATIVAR
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-success w-100 toggle-btn"
                                                data-user-id="<?= $user['id'] ?>"
                                                data-action="activate">
                                            ATIVAR ACESSO
                                        </button>
                                    <?php endif; ?>
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
// ─── Search + Status Filter ───────────────────────────────────────────────────
function applyFilters() {
    const search = document.getElementById('searchInput').value.toLowerCase();
    const statusFilter = document.getElementById('filterStatus').value.toLowerCase();
    const rows = document.querySelectorAll('#usersTable tbody tr[data-user-id]');

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const statusText = row.querySelector('.status-cell').textContent.trim().toLowerCase();
        const matchesSearch = text.includes(search);
        const matchesStatus = !statusFilter || statusText.includes(statusFilter);
        row.style.display = (matchesSearch && matchesStatus) ? '' : 'none';
    });
}

document.getElementById('searchInput').addEventListener('keyup', applyFilters);
document.getElementById('filterStatus').addEventListener('change', applyFilters);

// ─── Column Sorting ───────────────────────────────────────────────────────────
let currentSortCol = -1;
let currentSortAsc = true;

function sortTable(n) {
    const tbody = document.querySelector('#usersTable tbody');
    const rows  = Array.from(tbody.querySelectorAll('tr[data-user-id]'));
    const icons = document.querySelectorAll('#usersTable thead .fas');

    icons.forEach(ic => ic.className = 'fas fa-sort text-muted ms-1');

    if (currentSortCol === n) {
        currentSortAsc = !currentSortAsc;
    } else {
        currentSortCol = n;
        currentSortAsc = true;
    }

    const targetIcon = document.querySelectorAll('#usersTable thead th')[n].querySelector('.fas');
    if (targetIcon) targetIcon.className = `fas fa-sort-${currentSortAsc ? 'up' : 'down'} text-danger ms-1`;

    rows.sort((a, b) => {
        const valA = a.cells[n].innerText.trim();
        const valB = b.cells[n].innerText.trim();
        const c = valA.localeCompare(valB, 'pt-BR', { numeric: true, sensitivity: 'base' });
        return currentSortAsc ? c : -c;
    });

    tbody.append(...rows);
}

// ─── Toggle Ativar/Desativar ──────────────────────────────────────────────────
document.querySelectorAll('.toggle-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const userId = this.dataset.userId;
        const action = this.dataset.action;
        const row    = this.closest('tr');

        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

        fetch('<?= BASE_URL ?>post_toggle_user.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `user_id=${userId}&action=${action}`
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const isNowActive = data.new_status === 'active';
                const statusCell  = row.querySelector('.status-cell');

                // Update status badge
                statusCell.innerHTML = isNowActive
                    ? '<span class="badge bg-success-subtle text-success border border-success px-3">Ativo</span>'
                    : '<span class="badge bg-secondary-subtle text-secondary border border-secondary px-3">Inativo</span>';

                // Update button
                this.dataset.action = isNowActive ? 'deactivate' : 'activate';
                this.className = isNowActive
                    ? 'btn btn-sm btn-outline-danger w-100 toggle-btn'
                    : 'btn btn-sm btn-success w-100 toggle-btn';
                this.textContent = isNowActive ? 'DESATIVAR' : 'ATIVAR ACESSO';

                // Update row style
                row.className = isNowActive ? '' : 'bg-light-subtle';
            } else {
                alert(data.message || 'Erro ao alterar status.');
                this.disabled = false;
                this.textContent = action === 'activate' ? 'ATIVAR ACESSO' : 'DESATIVAR';
            }
        })
        .catch(() => {
            alert('Erro de conexão.');
            this.disabled = false;
        });
    });
});
</script>
