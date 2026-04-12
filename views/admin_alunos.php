<?php include __DIR__ . '/partial_admin_menu.php'; ?>

<div class="container animated-fade">
    <div class="row">
        <!-- Título e Instrução -->
        <div class="col-12 mb-4">
            <h1 class="h3 fatec-title mb-2"><i class="fas fa-users text-danger me-2"></i> GESTÃO DE ALUNOS</h1>
            <div class="alert alert-light border-start border-4 border-danger py-2 mb-0 shadow-sm">
                <i class="fas fa-info-circle text-danger me-2"></i>
                <span class="text-muted">Habilite ou desabilite o acesso de estudantes ao sistema de merenda. Alunos desativados não conseguirão realizar novas reservas.</span>
            </div>
        </div>

        <!-- Busca Ampla -->
        <div class="col-12 mb-4">
            <div class="card card-fatec shadow-sm">
                <div class="card-body p-2">
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-white border-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" class="form-control border-0 shadow-none" placeholder="Buscar aluno por nome, e-mail ou RA...">
                        <button class="btn btn-fatec px-5 rounded-3">PESQUISAR</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabela de Alunos -->
        <div class="col-12">
            <div class="card card-fatec shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Nome do Estudante</th>
                                    <th>E-mail Acadêmico</th>
                                    <th>Último Acesso</th>
                                    <th>Status do Acesso</th>
                                    <th class="text-center px-4">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Exemplo Ativo -->
                                <tr>
                                    <td class="ps-4 fw-bold">João Silva (ADS)</td>
                                    <td>joao.silva@fatec.sp.gov.br</td>
                                    <td>Ontem, 19:45</td>
                                    <td><span class="badge bg-success-subtle text-success border border-success px-3">Ativo</span></td>
                                    <td class="text-center px-4">
                                        <button class="btn btn-sm btn-outline-danger w-100">DESATIVAR</button>
                                    </td>
                                </tr>
                                <!-- Exemplo Inativo -->
                                <tr class="bg-light-subtle">
                                    <td class="ps-4 fw-bold text-muted">Maria Oliveira (Logística)</td>
                                    <td class="text-muted">maria.oliveira@fatec.sp.gov.br</td>
                                    <td class="text-muted">10/04, 18:30</td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary px-3 mb-1">Inativo</span>
                                            <small class="text-danger" style="font-size: 0.7rem;">Desativado em: 11/04/2026</small>
                                        </div>
                                    </td>
                                    <td class="text-center px-4">
                                        <button class="btn btn-sm btn-success w-100">ATIVAR ACESSO</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
