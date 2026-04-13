<?php
/**
 * Script para Atualização Automática no HostGator via Git Pull
 */
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h2>Atualizando o servidor HostGator a partir do GitHub...</h2>";
echo "<pre>";

// Executa o git pull
$output = shell_exec('git pull origin main 2>&1');

echo htmlspecialchars($output);

echo "</pre>";
echo "<p>✅ Completo. Se aparecer 'Already up to date' ou a lista de arquivos alterados, a atualização foi bem sucedida.</p>";
echo "<a href='public/index.php'>Voltar para o sistema</a>";
