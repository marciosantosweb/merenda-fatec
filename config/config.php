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
    define('BASE_URL', 'https://www.etecsaosebastiao.com.br/fatec/merenda/public/');
} else {
    define('BASE_URL', 'http://localhost/MERENDA/public/');
}
define('ADMIN_EMAIL_API', 'marcio.santos01@cps.sp.gov.br');

// Fuso Horário Brasilia (Padrão para cálculos de janta)
date_default_timezone_set('America/Sao_Paulo');

// Chave API Serper (Dashboard: serper.dev)
define('SERPER_API_KEY', '24673ad8f1311ea4b0f0e95e431b8acd271626f7');

// Configurações Microsoft API
define('MS_CLIENT_ID', '12154503-57fa-4498-8c9a-4e75c09abfe5');
define('MS_TENANT_ID', 'common');
define('MS_CLIENT_SECRET', 'hFm8Q~K.8DHNPSnpQXf3LUKDNUmqYo9BdMUk_djm');
define('MS_REDIRECT_URI', BASE_URL . 'auth/callback');
