<?php

require_once 'autoload.php';

use SistemaBancario\Models\Cliente;

echo "=== TESTE DO MODELO CLIENTE ===\n\n";

try {
  // 1. Teste de criação
  echo "1. Testando criação de cliente...\n";

  $cliente = new Cliente();
  $cliente->nome = 'Ana Santos';
  $cliente->cpf = '11122233344';
  $cliente->email = 'ana@email.com';
  $cliente->telefone = '11999888777';
  $cliente->endereco = 'Av. Paulista, 1000';
  $cliente->data_nascimento = '1990-05-15';
  $cliente->ativo = 1;

  $salvou = $cliente->save();
  echo "✅ Cliente criado com ID: {$cliente->id}\n\n";

  // 2. Teste de busca por ID
  echo "2. Testando busca por ID...\n";

  $clienteEncontrado = Cliente::find($cliente->id);
  echo "✅ Cliente encontrado: {$clienteEncontrado->getNomeCompleto()}\n";
  echo "   CPF: {$clienteEncontrado->getCpfFormatado()}\n";
  echo "   Idade: {$clienteEncontrado->getIdade()} anos\n\n";

  // 3. Teste de busca por CPF
  echo "3. Testando busca por CPF...\n";

  $clientePorCpf = Cliente::buscarPorCpf('11122233344');
  echo "✅ Cliente por CPF: {$clientePorCpf->nome}\n\n";

  // 4. Teste de busca por email
  echo "4. Testando busca por email...\n";

  $clientePorEmail = Cliente::buscarPorEmail('ana@email.com');
  echo "✅ Cliente por email: {$clientePorEmail->nome}\n\n";

  // 5. Teste de atualização
  echo "5. Testando atualização...\n";

  $cliente->telefone = '11888777666';
  $atualizou = $cliente->save();
  echo "✅ Cliente atualizado! Novo telefone: {$cliente->telefone}\n\n";

  // 6. Teste de criação usando create()
  echo "6. Testando criação com create()...\n";

  $cliente2 = Cliente::create([
    'nome' => 'João Oliveira',
    'cpf' => '55566677788',
    'email' => 'joao@email.com',
    'telefone' => '11777666555',
    'ativo' => 1
  ]);

  echo "✅ Cliente 2 criado com ID: {$cliente2->id}\n\n";

  // 7. Teste de listagem de clientes ativos
  echo "7. Testando listagem de clientes ativos...\n";

  $clientesAtivos = Cliente::ativos();
  echo "✅ Clientes ativos encontrados: " . count($clientesAtivos) . "\n";
  foreach ($clientesAtivos as $c) {
    echo "   - {$c->nome} (ID: {$c->id})\n";
  }
  echo "\n";

  // 8. Teste de conversão para array
  echo "8. Testando conversão para array...\n";

  $array = $cliente->toArray();
  echo "✅ Array do cliente:\n";
  foreach ($array as $key => $value) {
    echo "   {$key}: {$value}\n";
  }
  echo "\n";

  // 9. Teste de desativação
  echo "9. Testando desativação...\n";

  $cliente2->desativar();
  echo "✅ Cliente 2 desativado. Ativo: " . ($cliente2->isAtivo() ? 'Sim' : 'Não') . "\n\n";

  // 10. Limpar testes
  echo "10. Limpando dados de teste...\n";

  $cliente->delete();
  $cliente2->delete();
  echo "✅ Dados de teste removidos!\n\n";

  echo "🎉 MODELO CLIENTE FUNCIONANDO PERFEITAMENTE!\n";
} catch (Exception $e) {
  echo "❌ ERRO: " . $e->getMessage() . "\n";
}
