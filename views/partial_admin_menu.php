<nav class="navbar navbar-expand-lg navbar-dark bg-fatec-gray mb-4">
    <div class="container">
        <a class="navbar-brand fatec-title d-flex align-items-center" href="<?= BASE_URL ?>administrador">
            <img src="<?= BASE_URL ?>img/logotipo.png" style="height: 60px; width: auto;" alt="Logo Fatec">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>administrador"><i class="fas fa-home"></i> Início</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>administrador/estatisticas"><i class="fas fa-chart-line"></i> Estatísticas</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>administrador/alunos"><i class="fas fa-users"></i> Alunos</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>administrador/calendario"><i class="fas fa-calendar-alt"></i> Calendário</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>administrador/notificacoes"><i class="fas fa-bell"></i> Avisos</a></li>
                <li class="nav-item"><a class="nav-link text-danger ms-lg-3" href="<?= BASE_URL ?>sair"><i class="fas fa-sign-out-alt"></i> Sair</a></li>
            </ul>
        </div>
    </div>
</nav>
