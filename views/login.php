<?php
// Lógica para Login Microsoft
$authUrl = "https://login.microsoftonline.com/" . MS_TENANT_ID . "/oauth2/v2.0/authorize?" . http_build_query([
    'client_id' => MS_CLIENT_ID,
    'response_type' => 'code',
    'redirect_uri' => MS_REDIRECT_URI,
    'response_mode' => 'query',
    'scope' => 'User.Read',
    'state' => 'merenda_login'
]);
?>
<div class="container-fluid min-vh-100 d-flex align-items-center justify-content-center p-0">
    <div class="row g-0 w-100 min-vh-100">
        <!-- Coluna da Esquerda (Branding) -->
        <div class="col-lg-6 d-none d-lg-flex flex-column justify-content-center align-items-center bg-fatec-gray text-white p-5 border-end border-5 border-danger">
            <div class="animated-fade text-center">
                <!-- Logo Branco para fundo escuro -->
                <img src="<?= BASE_URL ?>img/logotipo.png" alt="Fatec Logo" class="img-fluid mb-4" style="max-width: 400px;">
                <h1 class="display-4 fatec-title">Sistema Merenda</h1>
                <p class="lead">Controle de Refeições e Cardápio Digital</p>
            </div>
        </div>

        <!-- Coluna da Direita (Login) -->
        <div class="col-lg-6 d-flex align-items-center justify-content-center bg-white">
            <div class="p-4 p-md-5 w-100" style="max-width: 500px;">
                <div class="text-center mb-5 d-lg-none">
                     <img src="<?= BASE_URL ?>img/logotipo.png" alt="Fatec Logo" class="img-fluid mb-3" style="max-width: 250px;">
                </div>
                
                <h2 class="mb-3">Bem-vindo(a)</h2>
                <p class="text-muted mb-4">Escolha a sua forma de acesso ao sistema.</p>

                <!-- Login Microsoft (Azure AD) -->
                <div class="card card-fatec mb-4 border border-1">
                    <div class="card-body p-4">
                        <h5 class="card-title h6 mb-3">Acesso Administrativo (Fatec / Microsoft)</h5>
                        <a href="<?= $authUrl ?>" class="btn btn-fatec w-100 d-flex align-items-center justify-content-center gap-2">
                            <i class="fab fa-microsoft fs-5"></i>
                            Entrar com Email Corporativo
                        </a>
                        <div class="mt-3 text-center">
                            <small class="text-muted">Acesso exclusivo para @cps.sp.gov.br e alunos.</small>
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-center my-4">
                    <hr class="flex-grow-1">
                    <span class="px-3 text-muted">OU</span>
                    <hr class="flex-grow-1">
                </div>

                <!-- Login Cozinha (Tradicional) -->
                <div class="card card-fatec border-0 bg-light">
                    <div class="card-body p-4">
                        <h5 class="card-title h6 mb-3">Acesso Cozinha</h5>
                        <form action="<?= BASE_URL ?>post_login_cozinha.php" method="POST">
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Usuário</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-user text-muted"></i></span>
                                    <input type="text" name="user" class="form-control border-start-0" placeholder="Digite seu usuário..." required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Senha</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-lock text-muted"></i></span>
                                    <input type="password" name="password" class="form-control border-start-0" placeholder="••••••••" required>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-dark w-100 fatec-title">Entrar</button>
                        </form>
                    </div>
                </div>

                <div class="text-center mt-5">
                    <p class="small text-dark fw-bold mb-0">Desenvolvido por NTI - Etec São Sebastião</p>
                    <p class="small text-muted mb-0">© <?= date('Y') ?> Fatec São Sebastião</p>
                    <p class="small text-muted">Controle de Merenda Noturna</p>
                </div>
            </div>
        </div>
    </div>
</div>
