<?php

require_once __DIR__ . '/../src/Database/Connection.php';
require_once __DIR__ . '/../src/Database/QueryBuilder.php';
require_once __DIR__ . '/../src/Utils/ValidationException.php';
require_once __DIR__ . '/../src/Models/Model.php';
require_once __DIR__ . '/../src/Models/Cliente.php';
require_once __DIR__ . '/../src/Models/Conta.php';
require_once __DIR__ . '/../src/Models/ContaCorrente.php';
require_once __DIR__ . '/../src/Models/ContaPoupanca.php';
require_once __DIR__ . '/../src/Services/ContaService.php';

use SistemaBancario\Models\Cliente;
use SistemaBancario\Services\ContaService;
use SistemaBancario\Utils\ValidationException;
use SistemaBancario\Database\Connection;

echo "=== TESTE DO CONTA SERVICE ===\n\n";

try {
  // 0. Garantir estrutura do banco
  echo "0. Preparando banco...\n";
  $sql = file_get_contents(__DIR__ . '/../database/create_tables.sql');
  $commands = array_filter(array_map('trim', explode(';', $sql)));

  foreach ($commands as $command) {
    if (!empty($command)) {
      Connection::query($command);
    }
  }
  echo "✅ Banco preparado\n\n";

  // 1. Criar cliente para testes
  echo "1. Criando cliente...\n";

  $cliente = Cliente::create([
    'nome' => 'João Service',
    'cpf' => '12312312312',
    'email' => 'joao.service.novo22@email.com',
    'telefone' => '11988887777',
    'ativo' => 1
  ]);

  echo "✅ Cliente criado: {$cliente->nome} (ID: {$cliente->id})\n\n";

  // 2. Testar abertura de conta corrente
  echo "2. Testando abertura de conta corrente...\n";

  $contaCorrente = ContaService::abrirContaCorrente($cliente->id, 1500.00);
  echo "✅ Conta corrente aberta: {$contaCorrente->numero_conta}\n";
  echo "   Limite cheque especial: R$ " . number_format($contaCorrente->getLimiteChequeEspecial(), 2, ',', '.') . "\n\n";

  // 3. Testar abertura de conta poupança
  echo "3. Testando abertura de conta poupança...\n";

  $contaPoupanca = ContaService::abrirContaPoupanca($cliente->id);
  echo "✅ Conta poupança aberta: {$contaPoupanca->numero_conta}\n\n";

  // 4. Testar erro: cliente já tem conta corrente
  echo "4. Testando erro: conta corrente duplicada...\n";

  try {
    ContaService::abrirContaCorrente($cliente->id);
    echo "❌ Deveria ter dado erro!\n";
  } catch (ValidationException $e) {
    echo "✅ Erro capturado: " . $e->getMessage() . "\n\n";
  }

  // 5. Testar consulta de saldo detalhado
  echo "5. Testando consulta de saldo detalhado...\n";

  // Fazer um depósito primeiro
  $contaCorrente->depositar(2000.00);

  $saldoDetalhado = ContaService::consultarSaldoDetalhado($contaCorrente->numero_conta);
  echo "✅ Saldo detalhado da conta corrente:\n";
  echo "   Saldo atual: {$saldoDetalhado['saldo_formatado']}\n";
  echo "   Saldo disponível: {$saldoDetalhado['saldo_disponivel_formatado']}\n";
  echo "   Limite cheque especial: R$ " . number_format($saldoDetalhado['limite_cheque_especial'], 2, ',', '.') . "\n";
  echo "   Usando cheque especial: " . ($saldoDetalhado['usando_cheque_especial'] ? 'Sim' : 'Não') . "\n\n";

  // 6. Testar alteração de limite
  echo "6. Testando alteração de limite...\n";

  $sucesso = ContaService::alterarLimiteChequeEspecial($contaCorrente->numero_conta, 2000.00);
  echo "✅ Limite alterado com sucesso\n";

  $novoSaldo = ContaService::consultarSaldoDetalhado($contaCorrente->numero_conta);
  echo "   Novo limite: R$ " . number_format($novoSaldo['limite_cheque_especial'], 2, ',', '.') . "\n\n";

  // 7. Testar bloqueio de conta
  echo "7. Testando bloqueio de conta...\n";

  ContaService::bloquearConta($contaCorrente->numero_conta, 'Teste de bloqueio');
  echo "✅ Conta bloqueada\n";

  // Verificar se operação falha com conta bloqueada
  try {
    ContaService::validarOperacao($contaCorrente->numero_conta, 'saque', 100.00);
    echo "❌ Deveria ter dado erro!\n";
  } catch (ValidationException $e) {
    echo "✅ Operação bloqueada: " . $e->getMessage() . "\n\n";
  }

  // 8. Testar desbloqueio
  echo "8. Testando desbloqueio...\n";

  ContaService::desbloquearConta($contaCorrente->numero_conta);
  echo "✅ Conta desbloqueada\n";

  // Verificar se operação funciona após desbloqueio
  $valida = ContaService::validarOperacao($contaCorrente->numero_conta, 'saque', 100.00);
  echo "✅ Operação validada após desbloqueio\n\n";

  // 9. Testar listagem de contas do cliente
  echo "9. Testando listagem de contas do cliente...\n";

  $contasCliente = ContaService::listarContasCliente($cliente->id);
  echo "✅ Contas do cliente {$contasCliente['cliente']['nome']}:\n";
  echo "   Total de contas: {$contasCliente['total_contas']}\n";

  foreach ($contasCliente['contas'] as $conta) {
    echo "   - {$conta['numero_conta']} ({$conta['tipo_conta']}) - {$conta['saldo_formatado']}\n";
  }
  echo "\n";

  // 10. Testar validações diversas
  echo "10. Testando validações...\n";

  // Teste: cliente inexistente
  try {
    ContaService::abrirContaCorrente(99999);
    echo "❌ Deveria ter dado erro!\n";
  } catch (ValidationException $e) {
    echo "✅ Cliente inexistente: " . $e->getMessage() . "\n";
  }

  // Teste: limite inválido
  try {
    ContaService::alterarLimiteChequeEspecial($contaCorrente->numero_conta, 15000.00);
    echo "❌ Deveria ter dado erro!\n";
  } catch (ValidationException $e) {
    echo "✅ Limite inválido: " . $e->getMessage() . "\n";
  }

  // Teste: valor de depósito muito alto
  try {
    ContaService::validarOperacao($contaCorrente->numero_conta, 'deposito', 100000.00);
    echo "❌ Deveria ter dado erro!\n";
  } catch (ValidationException $e) {
    echo "✅ Valor muito alto: " . $e->getMessage() . "\n";
  }

  echo "\n";

  // 11. Limpar dados de teste
  echo "11. Limpando dados de teste...\n";

  $contaCorrente->delete();
  $contaPoupanca->delete();
  $cliente->delete();

  echo "✅ Dados removidos\n\n";

  echo "🎉 CONTA SERVICE FUNCIONANDO PERFEITAMENTE!\n";
} catch (Exception $e) {
  echo "❌ ERRO: " . $e->getMessage() . "\n";
  echo "Linha: " . $e->getLine() . "\n";
  echo "Arquivo: " . $e->getFile() . "\n";
}
