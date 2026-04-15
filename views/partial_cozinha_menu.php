<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4 shadow-sm">
    <div class="container">
        <a class="navbar-brand fatec-title d-flex align-items-center" href="<?= BASE_URL ?>cozinha">
            <img src="<?= BASE_URL ?>public/img/logotipo.png" style="height: 45px; width: auto; filter: brightness(0) invert(1);" alt="Logo Fatec">
            <span class="ms-3 border-start ps-3 text-uppercase small opacity-75 d-none d-md-inline">Painel Cozinha</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavCozinha">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNavCozinha">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>cozinha"><i class="fas fa-home me-1"></i> Início</a></li>
                <li class="nav-item"><a class="nav-link bg-danger bg-opacity-25 rounded-pill px-3 ms-lg-2" href="<?= BASE_URL ?>cozinha/cardapio"><i class="fas fa-calendar-alt me-1"></i> ATUALIZAR CARDÁPIO</a></li>
                <li class="nav-item ms-lg-4 d-none d-lg-block">
                    <span class="text-white-50 small border-start ps-3" id="nav-clock">--:--:--</span>
                </li>
                <li class="nav-item"><a class="nav-link text-danger ms-lg-3" href="<?= BASE_URL ?>sair"><i class="fas fa-sign-out-alt"></i> Sair</a></li>
            </ul>
        </div>
    </div>
</nav>

<script>
function updateNavClock() {
    const clockEl = document.getElementById('nav-clock');
    if (clockEl) {
        const now = new Date();
        clockEl.innerText = now.toLocaleTimeString('pt-BR');
    }
}
setInterval(updateNavClock, 1000);
updateNavClock();
</script>
