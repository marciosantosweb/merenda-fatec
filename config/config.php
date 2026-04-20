<?php
/**
 * Configuração de ambiente e variáveis globais
 * T - Teste (Banco local)
 * P - Produção (Banco remoto)
 */

define('ENV', 'P'); // Altere para 'P' em produção

if (ENV === 'T') {
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'merenda_fatec');
    define('DB_USER', 'root');
    define('DB_PASS', '');
} else {
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'etecsaosebas_fatecmerenda');
    define('DB_USER', 'etecsaosebas_merenda');
    define('DB_PASS', 'cap%ih-gyMs*4-hoz&Ef');
}

// Configurações Globais
if (ENV === 'P') {
    define('BASE_URL', 'https://www.etecsaosebastiao.com.br/fatec/merenda/');
} else {
    define('BASE_URL', 'http://localhost/MERENDA/');
}
define('ADMIN_EMAIL_API', 'marcio.santos01@cps.sp.gov.br');

// Fuso Horário Brasilia (Padrão para cálculos de janta)
date_default_timezone_set('America/Sao_Paulo');

// Chave API Serper (Dashboard: serper.dev)
define('SERPER_API_KEY', '24673ad8f1311ea4b0f0e95e431b8acd271626f7');

// Chave API OpenAI
define('OPENAI_API_KEY', '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');

// Chave API Gemini (Obrigatória para ler PDF sem erros)
define('GEMINI_API_KEY', 'AQ.Ab8RN6Jww-X_0VVB3LIOlESdgEpU7E1eZHkr84v2SvX5GRNi0w');

// Configurações Microsoft API
define('MS_CLIENT_ID', '12154503-57fa-4498-8c9a-4e75c09abfe5');
define('MS_TENANT_ID', 'common');
define('MS_CLIENT_SECRET', 'hFm8Q~K.8DHNPSnpQXf3LUKDNUmqYo9BdMUk_djm');
define('MS_REDIRECT_URI', BASE_URL . 'public/auth/callback');
