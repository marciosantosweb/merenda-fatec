<?php
use App\Core\Database;
$db = Database::getConnection();

// Buscar horários configurados no banco
$stmt = $db->query("SELECT config_key, config_value FROM settings");
$settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

$start = $settings['reservation_start'] ?? '18:00:00';
$end   = $settings['reservation_end'] ?? '19:30:00';
$now   = date('H:i:s');

// Lógica de Janela
$is_open = ($now >= $start && $now <= $end);
$is_weekend = (date('w') == 0 || date('w') == 6);
$is_before = ($now < $start);
$is_after = ($now > $end);

// Verificar se hoje é um dia bloqueado no calendário acadêmico
$stmt_cal = $db->prepare("SELECT id FROM academic_calendar WHERE date = ?");
$stmt_cal->execute([date('Y-m-d')]);
$is_blocked = $stmt_cal->fetch();

// MODOS DE TESTE VISUAL
if (isset($_GET['teste'])) {
    $is_weekend = false;
    if ($_GET['teste'] == '1') {
        $is_open = true; $is_before = false; $is_after = false;
    } elseif ($_GET['teste'] == 'fechado') {
        $is_open = false; $is_before = false; $is_after = true;
    } elseif ($_GET['teste'] == 'antes') {
        $is_open = false; $is_before = true; $is_after = false;
    }
}
?>

<style>
    body { background-color: #f8f9fa; font-family: 'Raleway', sans-serif; }
    .mobile-container {
        max-width: 400px;
        min-height: 100vh;
        margin: 0 auto;
        background: #fff;
        box-shadow: 0 0 30px rgba(0,0,0,0.1);
        display: flex;
        flex-direction: column;
        position: relative;
    }
    .app-header {
        background: var(--dark-gray);
        color: white;
        padding: 40px 20px 20px;
        border-radius: 0 0 30px 30px;
        text-align: center;
        border-bottom: 5px solid var(--accent-red);
    }
    .status-card {
        margin-top: -30px;
        background: white;
        border-radius: 20px;
        padding: 20px;
        box-shadow: 0 10px 20px rgba(0,0,0,0.05);
        text-align: center;
    }
    .timer {
        font-size: 2.5rem;
        font-weight: bold;
        color: var(--primary-red);
    }
    .btn-reserve {
        background: var(--accent-red);
        color: white;
        border: none;
        padding: 15px 30px;
        border-radius: 50px;
        font-weight: bold;
        font-size: 1.1rem;
        width: 100%;
        box-shadow: 0 5px 15px rgba(181, 13, 17, 0.3);
        margin-top: 20px;
        transition: all 0.3s;
    }
    .btn-reserve:disabled {
        background: #ccc;
        box-shadow: none;
        cursor: not-allowed;
        transform: scale(0.98);
    }
    .menu-card {
        background: #fdfdfd;
        border-left: 4px solid var(--accent-red);
        padding: 15px;
        margin-top: 20px;
        border-radius: 10px;
        position: relative;
        cursor: pointer;
        transition: all 0.2s;
    }
    .menu-card:active { transform: scale(0.98); background: #f8f8f8; }
    .btn-month-view {
        position: absolute;
        top: 15px;
        right: 15px;
        color: var(--accent-red);
        font-size: 0.9rem;
    }
    .nav-bottom {
        height: 70px;
        background: white;
        display: flex;
        justify-content: space-around;
        align-items: center;
        border-top: 1px solid #eee;
        margin-top: auto;
    }
    .nav-item-sub { text-align: center; color: #999; font-size: 0.7rem; }
    .nav-item-sub.active { color: var(--accent-red); }
</style>

<div class="mobile-container">
    <div class="app-header">
        <img src="<?= BASE_URL ?>public/img/logotipo.png" height="50" alt="Logo">
        <p class="mt-2 small opacity-75">Olá, Aluno(a)!</p>
    </div>

    <div class="px-3">
        <div class="status-card">
            <?php if ($is_weekend): ?>
                <span class="badge bg-secondary mb-2 px-3 rounded-pill uppercase">Fim de Semana</span>
                <p class="small text-muted mb-0">O sistema de merenda abre de Segunda a Sexta.</p>
                <div class="timer text-muted opacity-50">--:--</div>
            <?php elseif ($is_blocked): ?>
                <span class="badge bg-dark mb-2 px-3 rounded-pill uppercase">DIA NÃO LETIVO</span>
                <p class="small text-muted mb-0">Hoje não haverá janta devido ao feriado/recesso.</p>
                <div class="timer text-muted opacity-50"><i class="fas fa-calendar-times"></i></div>
            <?php elseif ($is_open): ?>
                <span class="badge bg-success mb-2 px-3 rounded-pill">JANELA ABERTA</span>
                <p class="small text-muted mb-0 text-uppercase fw-bold">Tempo Restante:</p>
                <div class="timer" id="app-timer">--:--:--</div>
            <?php elseif ($is_before): ?>
                <span class="badge bg-warning text-dark mb-2 px-3 rounded-pill">AGUARDANDO ABERTURA</span>
                <p class="small text-muted mb-0">A janela de reserva abre hoje às <?= substr($start, 0, 5) ?></p>
                <div class="timer text-muted" style="font-size: 1.5rem;">Aguarde...</div>
            <?php else: ?>
                <span class="badge bg-danger mb-2 px-3 rounded-pill">JANELA ENCERRADA</span>
                <p class="small text-muted mb-0">As reservas hoje encerraram às <?= substr($end, 0, 5) ?></p>
                <div class="timer text-muted" style="font-size: 1.5rem;">Amanhã às <?= substr($start, 0, 5) ?></div>
            <?php endif; ?>
        </div>

        <div class="mt-4">
            <?php if (!$is_weekend && !$is_blocked): ?>
                <div class="menu-card animated-fade" onclick="openMonthModal()">
                    <div class="btn-month-view">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <p class="mb-0 fw-bold pe-5">Arroz, Feijão, Proteína e Guarnição</p>
                    <small class="text-muted text-uppercase" style="font-size: 0.65rem;">Cozinha Fatec São Sebastião</small>
                    <div class="mt-2 text-primary small fw-bold" style="font-size: 0.7rem;">
                        <i class="fas fa-eye me-1"></i> VER CARDÁPIO DO MÊS
                    </div>
                </div>
                
                <?php if ($is_open): ?>
                    <div class="mt-4">
                         <div class="d-flex align-items-center justify-content-between bg-light p-3 rounded-3">
                             <span class="small fw-bold">Deseja repetir?</span>
                             <select class="form-select form-select-sm w-auto border-0 bg-transparent fw-bold text-danger shadow-none">
                                 <option>Apenas Janta</option>
                                 <option>+1 Repetição</option>
                                 <option>+2 Repetições</option>
                                 <option>+3 Repetições</option>
                             </select>
                         </div>
                    </div>
                    <button class="btn btn-reserve">CONFIRMAR JANTA</button>
                <?php else: ?>
                    <button class="btn btn-reserve" disabled>FORA DO HORÁRIO</button>
                    <p class="text-center mt-3 text-danger small fw-bold"><i class="fas fa-lock me-1"></i> Reservas Indisponíveis</p>
                <?php endif; ?>
            <?php else: ?>
                <!-- Estado de Fim de Semana - Sem Cardápio -->
                <div class="text-center mt-5 opacity-50">
                    <i class="fas fa-mug-hot fs-1 mb-3"></i>
                    <p>Voltaremos na Segunda-feira!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="nav-bottom">
        <div class="nav-item-sub active"><i class="fas fa-home d-block fs-5"></i>Início</div>
        <div class="nav-item-sub"><i class="fas fa-history d-block fs-5"></i>Histórico</div>
        <div class="nav-item-sub"><i class="fas fa-user d-block fs-5"></i>Perfil</div>
    </div>
</div>

<!-- Modal Cardápio Mensal -->
<div class="modal fade" id="monthMenuModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable mx-3">
        <div class="modal-content border-0 rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-danger"><i class="fas fa-calendar-alt me-2"></i>Cardápio Mensal</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-3">
                <p class="text-muted small mb-4">Veja as refeições planejadas para este mês.</p>
                <div id="monthMenuContent">
                    <!-- Conteúdo via JS -->
                    <div class="text-center py-5">
                        <div class="spinner-border text-danger" role="status"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function updateAppTimer() {
        const timerEl = document.getElementById('app-timer');
        if (!timerEl) return;

        const now = new Date();
        const endStr = "<?= $end ?>";
        const parts = endStr.split(':');
        
        const end = new Date();
        end.setHours(parseInt(parts[0]), parseInt(parts[1]), parseInt(parts[2]));
        
        const isTest = <?= isset($_GET['teste']) ? 'true' : 'false' ?>;
        
        let diff;
        if (isTest) {
            // No modo de teste, vamos fazer a contagem regressiva até o fim do dia atual
            // Isso garante que o tempo "mova" porque o fim é fixo e o 'now' avança.
            const fakeEnd = new Date();
            fakeEnd.setHours(23, 59, 59);
            diff = fakeEnd - now;
        } else {
            diff = end - now;
        }

        if (diff < 0 && !isTest) {
            window.location.reload(); 
            return;
        }

        const h = Math.floor(diff / 3600000).toString().padStart(2, '0');
        const m = Math.floor((diff % 3600000) / 60000).toString().padStart(2, '0');
        const s = Math.floor((diff % 60000) / 1000).toString().padStart(2, '0');

        timerEl.innerText = `${h}:${m}:${s}`;
    }
    <?php if ($is_open): ?>
    setInterval(updateAppTimer, 1000);
    updateAppTimer();
    <?php endif; ?>

    function openMonthModal() {
        const modal = new bootstrap.Modal(document.getElementById('monthMenuModal'));
        modal.show();
        
        // Simulação de busca do cardápio mensal
        // Na prática, buscaríamos via fetch
        const content = document.getElementById('monthMenuContent');
        const monthNames = ["Janeiro", "Fevereiro", "Março", "Abril", "Maio", "Junho", "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro"];
        const currentMonth = monthNames[new Date().getMonth()];
        
        let html = `<h6 class="fw-bold mb-3 text-uppercase small">${currentMonth} / ${new Date().getFullYear()}</h6>`;
        
        // Lista fictícia para demonstração
        for(let i=1; i<=5; i++) {
            html += `
                <div class="p-3 mb-2 rounded-3 bg-light border-start border-3 border-danger">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="fw-bold small">Quarta-feira, 1${i} / 04</span>
                    </div>
                    <p class="small mb-0 text-muted">Arroz, Feijão, Frango Grelhado, Purê de Batata e Salada de Alface.</p>
                </div>
            `;
        }
        
        setTimeout(() => {
            content.innerHTML = html;
        }, 500);
    }
</script>
