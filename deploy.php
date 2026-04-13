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

if (strpos($output, 'unknown index entry format') !== false || strpos($output, 'index file corrupt') !== false) {
    echo "<b>Aviso: Índice do Git local corrompido. Iniciando Auto-Reparo...</b>\n\n";
    shell_exec('rm -f .git/index');
    shell_exec('git clean -fd');
    shell_exec('git reset --hard origin/main');
    $output = shell_exec('git pull origin main 2>&1');
    echo "Reparo efetuado! Novo log do git pull:\n";
}

echo htmlspecialchars($output);

echo "</pre>";
echo "<p>✅ Completo. Se aparecer 'Already up to date' ou a lista de arquivos alterados, a atualização foi bem sucedida.</p>";
echo "<a href='public/index.php'>Voltar para o sistema</a>";
