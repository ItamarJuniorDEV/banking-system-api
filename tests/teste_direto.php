<?php

echo "=== TESTE DIRETO (SEM AUTOLOADER) ===\n\n";

// Carregar classes manualmente na ordem certa
require_once __DIR__ . '/../src/Database/Connection.php';
require_once __DIR__ . '/../src/Database/QueryBuilder.php';
require_once __DIR__ . '/../src/Models/Model.php';
require_once __DIR__ . '/../src/Models/Cliente.php';
require_once __DIR__ . '/../src/Models/Conta.php';
require_once __DIR__ . '/../src/Models/ContaCorrente.php';
require_once __DIR__ . '/../src/Models/Transacao.php';

use SistemaBancario\Models\Cliente;
use SistemaBancario\Models\ContaCorrente;
use SistemaBancario\Models\Transacao;

try {
  echo "1. Criando cliente...\n";

  $cliente = Cliente::create([
    'nome' => 'Teste Direto',
    'cpf' => '99999999999',
    'email' => 'teste.direto2@email.com',
    'telefone' => '11888888888',
    'ativo' => 1
  ]);

  echo "✅ Cliente criado: {$cliente->nome}\n\n";

  echo "2. Criando conta...\n";
  $conta = ContaCorrente::criar($cliente->id);
  echo "✅ Conta criada: {$conta->numero_conta}\n\n";

  echo "3. Fazendo depósito...\n";
  $conta->depositar(500.00);
  $transacao = Transacao::registrarDeposito($conta, 500.00);
  echo "✅ Depósito: {$transacao->getValorFormatado()}\n\n";

  echo "🎉 FUNCIONOU COM REQUIRE DIRETO!\n";
} catch (Exception $e) {
  echo "❌ ERRO: " . $e->getMessage() . "\n";
}
