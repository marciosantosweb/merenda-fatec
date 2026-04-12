<?php include __DIR__ . '/partial_admin_menu.php'; ?>

<div class="container animated-fade">
    <div class="row">
        <div class="col-12 mb-4">
            <h1 class="h3 fatec-title"><i class="fas fa-chart-line text-danger"></i> Estatísticas de Consumo</h1>
            <p class="text-muted">Análise de dados de refeições e repetições por período.</p>
        </div>

        <!-- Filtros -->
        <div class="col-12 mb-4">
            <div class="card card-fatec">
                <div class="card-body">
                    <form class="row align-items-end">
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Data Início</label>
                            <input type="date" class="form-control" value="<?= date('Y-m-01') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Data Fim</label>
                            <input type="date" class="form-control" value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-md-4">
                            <button type="button" class="btn btn-fatec w-100">Filtrar Dados</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Gráficos Teóricos -->
        <div class="col-lg-8">
            <div class="card card-fatec mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Consumo Semanal (Total de Pratos)</h5>
                </div>
                <div class="card-body text-center p-5">
                    <i class="fas fa-chart-bar fs-1 text-muted opacity-25 mb-3"></i>
                    <p class="text-muted">Os gráficos serão renderizados aqui com Chart.js usando dados reais do banco.</p>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card card-fatec mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Top Gastos (Repetições)</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Apenas Janta
                            <span class="badge bg-primary rounded-pill">65%</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            1 Repetição
                            <span class="badge bg-success rounded-pill">20%</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            2 Repetições
                            <span class="badge bg-warning rounded-pill">10%</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            3 Repetições
                            <span class="badge bg-danger rounded-pill">5%</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
