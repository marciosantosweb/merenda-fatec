<?php
// ATIVAR MODO DE DEPURAÇÃO TOTAL
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔍 Diagnóstico do Sistema</h1>";

// 1. Testar se o arquivo de config existe
if (file_exists('config/config.php')) {
    echo "✅ Arquivo 'config/config.php' encontrado!<br>";
    require_once 'config/config.php';
} else {
    echo "❌ Arquivo 'config/config.php' NÃO FOI ENCONTRADO.<br>";
}

// 2. Testar se o PDO (Banco de Dados) está habilitado no servidor
if (extension_loaded('pdo_mysql')) {
    echo "✅ Extensão PDO MySQL está instalada!<br>";
} else {
    echo "❌ Extensão PDO MySQL NÃO ESTÁ INSTALADA no servidor.<br>";
}

// 3. Testar a conexão real
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    echo "<h1>🎊 SUCESSO! A conexão com o banco de dados funcionou perfeitamente.</h1>";
} catch (Exception $e) {
    echo "<h1>🚨 ERRO DE CONEXÃO:</h1>";
    echo "<pre>" . $e->getMessage() . "</pre>";
}
