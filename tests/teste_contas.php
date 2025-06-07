<?php

require_once __DIR__ . '/../autoload.php';

use SistemaBancario\Models\Cliente;
use SistemaBancario\Models\ContaCorrente;
use SistemaBancario\Models\ContaPoupanca;
use SistemaBancario\Database\Connection;

echo "=== TESTE DO SISTEMA DE CONTAS ===\n\n";

try {
  // 0. Garantir que as tabelas existem
  echo "0. Verificando estrutura do banco...\n";

  try {
    // Tentar criar as tabelas (se não existirem)
    $sql = file_get_contents(__DIR__ . '/../database/create_tables.sql');
    $commands = array_filter(array_map('trim', explode(';', $sql)));

    foreach ($commands as $command) {
      if (!empty($command)) {
        Connection::query($command);
      }
    }
    echo "✅ Estrutura do banco verificada\n\n";
  } catch (Exception $e) {
    echo "   ⚠️ Erro na verificação: " . $e->getMessage() . "\n\n";
  }

  // 1. Criar cliente para testes
  echo "1. Criando cliente para testes...\n";

  $cliente = Cliente::create([
    'nome' => 'Pedro Santos',
    'cpf' => '12345678901',  // CPF diferente para evitar conflito
    'email' => 'pedro.teste@email.com',  // Email único
    'telefone' => '11888777666',
    'ativo' => 1
  ]);

  echo "✅ Cliente criado: {$cliente->nome} (ID: {$cliente->id})\n\n";

  // 2. Criar conta corrente
  echo "2. Criando conta corrente...\n";

  $contaCorrente = ContaCorrente::criar($cliente->id, 1000.00);
  echo "✅ Conta corrente criada: {$contaCorrente->numero_conta}\n";
  echo "   Saldo: {$contaCorrente->getSaldoFormatado()}\n";
  echo "   Limite cheque especial: R$ " . number_format($contaCorrente->getLimiteChequeEspecial(), 2, ',', '.') . "\n\n";

  // 3. Criar conta poupança
  echo "3. Criando conta poupança...\n";

  $contaPoupanca = ContaPoupanca::criar($cliente->id);
  echo "✅ Conta poupança criada: {$contaPoupanca->numero_conta}\n";
  echo "   Saldo: {$contaPoupanca->getSaldoFormatado()}\n\n";

  // 4. Testar depósitos
  echo "4. Testando depósitos...\n";

  $contaCorrente->depositar(2000.00);
  echo "✅ Depósito R$ 2.000,00 na conta corrente\n";
  echo "   Novo saldo: {$contaCorrente->getSaldoFormatado()}\n";

  $contaPoupanca->depositar(5000.00);
  echo "✅ Depósito R$ 5.000,00 na conta poupança\n";
  echo "   Novo saldo: {$contaPoupanca->getSaldoFormatado()}\n\n";

  // 5. Testar saques
  echo "5. Testando saques...\n";

  $contaCorrente->sacar(500.00);
  echo "✅ Saque R$ 500,00 da conta corrente (tarifa: R$ 4,50)\n";
  echo "   Novo saldo: {$contaCorrente->getSaldoFormatado()}\n";

  $contaPoupanca->sacar(1000.00);
  echo "✅ Saque R$ 1.000,00 da conta poupança (sem tarifa)\n";
  echo "   Novo saldo: {$contaPoupanca->getSaldoFormatado()}\n\n";

  // 6. Testar PIX
  echo "6. Testando PIX...\n";

  $contaCorrente->pix($contaPoupanca, 300.00);
  echo "✅ PIX R$ 300,00 da conta corrente para poupança\n";
  echo "   Saldo conta corrente: {$contaCorrente->getSaldoFormatado()}\n";
  echo "   Saldo conta poupança: {$contaPoupanca->getSaldoFormatado()}\n\n";

  // 7. Testar cheque especial
  echo "7. Testando cheque especial...\n";

  echo "   Saldo atual conta corrente: {$contaCorrente->getSaldoFormatado()}\n";
  echo "   Saldo disponível (com cheque especial): R$ " . number_format($contaCorrente->getSaldoDisponivel(), 2, ',', '.') . "\n";

  $contaCorrente->sacar(2000.00); // Vai usar cheque especial
  echo "✅ Saque R$ 2.000,00 (usando cheque especial)\n";
  echo "   Novo saldo: {$contaCorrente->getSaldoFormatado()}\n";
  echo "   Usando cheque especial: " . ($contaCorrente->isUsandoChequeEspecial() ? 'Sim' : 'Não') . "\n";
  echo "   Valor usado: R$ " . number_format($contaCorrente->getValorUsadoChequeEspecial(), 2, ',', '.') . "\n\n";

  // 8. Testar rendimento da poupança
  echo "8. Testando rendimento da poupança...\n";

  $rendimento = $contaPoupanca->calcularRendimento();
  echo "   Saldo atual: {$contaPoupanca->getSaldoFormatado()}\n";
  echo "   Rendimento mensal: R$ " . number_format($rendimento, 2, ',', '.') . "\n";

  $contaPoupanca->aplicarRendimento();
  echo "✅ Rendimento aplicado!\n";
  echo "   Novo saldo: {$contaPoupanca->getSaldoFormatado()}\n\n";

  // 9. Testar projeção de rendimento
  echo "9. Projeção de rendimento (3 meses)...\n";

  $projecao = $contaPoupanca->projetarRendimento(3);
  foreach ($projecao as $mes) {
    echo "   Mês {$mes['mes']}: {$mes['rendimento_formatado']} → {$mes['saldo_formatado']}\n";
  }
  echo "\n";

  // 10. Testar busca por número
  echo "10. Testando busca por número...\n";

  $contaEncontrada = ContaCorrente::buscarPorNumero($contaCorrente->numero_conta);
  echo "✅ Conta encontrada: {$contaEncontrada->numero_conta} (Tipo: " . get_class($contaEncontrada) . ")\n\n";

  // 11. Testar busca por cliente
  echo "11. Testando busca por cliente...\n";

  $contasCliente = ContaCorrente::buscarPorCliente($cliente->id);
  echo "✅ Contas do cliente encontradas: " . count($contasCliente) . "\n";
  foreach ($contasCliente as $conta) {
    echo "   - {$conta->numero_conta} ({$conta->tipo_conta}) - {$conta->getSaldoFormatado()}\n";
  }
  echo "\n";

  // 12. Limpar testes
  echo "12. Limpando dados de teste...\n";

  $contaCorrente->delete();
  $contaPoupanca->delete();
  $cliente->delete();
  echo "✅ Dados de teste removidos!\n\n";

  echo "🎉 SISTEMA DE CONTAS FUNCIONANDO PERFEITAMENTE!\n";
} catch (Exception $e) {
  echo "❌ ERRO: " . $e->getMessage() . "\n";
}
