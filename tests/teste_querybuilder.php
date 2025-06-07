<?php

require_once 'autoload.php';

use SistemaBancario\Database\QueryBuilder;

echo "=== TESTE DO QUERY BUILDER ===\n\n";

try {
  // 1. Teste de INSERT
  echo "1. Testando INSERT...\n";

  $qb = new QueryBuilder();
  $clienteId = $qb->table('clientes')->insert([
    'nome' => 'Maria Silva',
    'cpf' => '12345678900',
    'email' => 'maria@email.com',
    'telefone' => '11987654321',
    'endereco' => 'Rua das Flores, 123'
  ]);

  echo "✅ Cliente inserido com ID: {$clienteId}\n\n";

  // 2. Teste de SELECT simples
  echo "2. Testando SELECT simples...\n";

  $cliente = QueryBuilder::create('clientes')->find($clienteId);
  echo "✅ Cliente encontrado: {$cliente['nome']}\n\n";

  // 3. Teste de WHERE
  echo "3. Testando WHERE...\n";

  $cliente = QueryBuilder::create('clientes')
    ->where('cpf', '=', '12345678900')
    ->first();

  echo "✅ Cliente por CPF: {$cliente['nome']}\n\n";

  // 4. Teste de SELECT específico
  echo "4. Testando SELECT específico...\n";

  $clientes = QueryBuilder::create('clientes')
    ->select(['nome', 'email'])
    ->where('ativo', '=', 1)
    ->get();

  echo "✅ Clientes ativos encontrados: " . count($clientes) . "\n";
  foreach ($clientes as $c) {
    echo "   - {$c['nome']} ({$c['email']})\n";
  }
  echo "\n";

  // 5. Teste de UPDATE
  echo "5. Testando UPDATE...\n";

  $updated = QueryBuilder::create('clientes')
    ->where('id', '=', $clienteId)
    ->update([
      'telefone' => '11999887766',
      'updated_at' => date('Y-m-d H:i:s')
    ]);

  echo "✅ Registros atualizados: {$updated}\n\n";

  // 6. Teste de COUNT
  echo "6. Testando COUNT...\n";

  $total = QueryBuilder::create('clientes')->count();
  echo "✅ Total de clientes: {$total}\n\n";

  // 7. Teste de ORDER BY
  echo "7. Testando ORDER BY...\n";

  $clientes = QueryBuilder::create('clientes')
    ->select(['nome', 'created_at'])
    ->orderBy('created_at', 'DESC')
    ->get();

  echo "✅ Clientes ordenados por data:\n";
  foreach ($clientes as $c) {
    echo "   - {$c['nome']} ({$c['created_at']})\n";
  }
  echo "\n";

  // 8. Teste de SQL gerado
  echo "8. Testando SQL gerado...\n";

  $sql = QueryBuilder::create('clientes')
    ->select(['nome', 'email'])
    ->where('ativo', '=', 1)
    ->where('created_at', '>', '2025-01-01')
    ->orderBy('nome', 'ASC')
    ->limit(10)
    ->toSql();

  echo "✅ SQL gerado:\n   {$sql}\n\n";

  // 9. Limpar teste
  echo "9. Limpando dados de teste...\n";

  $deleted = QueryBuilder::create('clientes')
    ->where('id', '=', $clienteId)
    ->delete();

  echo "✅ Registros deletados: {$deleted}\n\n";

  echo "🎉 QUERY BUILDER FUNCIONANDO PERFEITAMENTE!\n";
} catch (Exception $e) {
  echo "❌ ERRO: " . $e->getMessage() . "\n";
}
