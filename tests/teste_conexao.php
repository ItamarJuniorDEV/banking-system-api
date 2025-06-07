<?php

require_once __DIR__ . '/../autoload.php';

use SistemaBancario\Database\Connection;

echo "=== TESTE DE CONEXÃO COM BANCO ===\n\n";

try {
    // 1. Testar conexão
    echo "1. Testando conexão com banco...\n";
    $pdo = Connection::getInstance();
    echo "✅ Conexão estabelecida!\n\n";

    // 2. Criar tabelas
    echo "2. Criando tabelas...\n";
    $sqlFile = __DIR__ . '/../database/create_tables.sql';

    if (!file_exists($sqlFile)) {
        throw new Exception("Arquivo SQL não encontrado: {$sqlFile}");
    }

    $sql = file_get_contents($sqlFile);

    if ($sql === false) {
        throw new Exception("Erro ao ler arquivo SQL");
    }

    $commands = array_filter(array_map('trim', explode(';', $sql)));

    foreach ($commands as $command) {
        if (!empty($command)) {
            Connection::query($command);
        }
    }

    echo "✅ Tabelas criadas!\n\n";

    // 3. Teste básico
    echo "3. Teste básico...\n";
    $info = Connection::getInfo();
    echo "   Database: {$info['database_file']}\n";
    echo "   Size: " . round($info['file_size'] / 1024, 2) . " KB\n\n";

    echo "🎉 BANCO CONFIGURADO COM SUCESSO!\n";
} catch (Exception $e) {
    echo "❌ ERRO: " . $e->getMessage() . "\n";
    echo "Debug - Arquivo atual: " . __FILE__ . "\n";
    echo "Debug - Diretório: " . __DIR__ . "\n";
}
