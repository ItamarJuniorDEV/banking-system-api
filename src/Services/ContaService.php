<?php

namespace SistemaBancario\Services;

use SistemaBancario\Models\Cliente;
use SistemaBancario\Models\ContaCorrente;
use SistemaBancario\Models\ContaPoupanca;
use SistemaBancario\Models\Conta;
use SistemaBancario\Utils\ValidationException;

/**
 * SERVICE DE CONTAS
 * 
 * Contém a lógica de negócio para operações com contas bancárias.
 */
class ContaService
{
  /**
   * Abrir nova conta corrente
   */
  public static function abrirContaCorrente(int $clienteId, float $limiteChequeEspecial = 500.00): ContaCorrente
  {
    // Validar se cliente existe e está ativo
    $cliente = Cliente::find($clienteId);
    if (!$cliente) {
      throw new ValidationException(['Cliente não encontrado']);
    }

    if (!$cliente->isAtivo()) {
      throw new ValidationException(['Cliente está inativo']);
    }

    // Verificar se cliente já tem conta corrente
    $contasExistentes = Conta::buscarPorCliente($clienteId);
    foreach ($contasExistentes as $conta) {
      if ($conta instanceof ContaCorrente) {
        throw new ValidationException(['Cliente já possui conta corrente']);
      }
    }

    // Validar limite do cheque especial
    if ($limiteChequeEspecial < 0 || $limiteChequeEspecial > 10000) {
      throw new ValidationException(['Limite do cheque especial deve estar entre R$ 0 e R$ 10.000']);
    }

    // Criar conta corrente
    $conta = ContaCorrente::criar($clienteId, $limiteChequeEspecial);

    return $conta;
  }

  /**
   * Abrir nova conta poupança
   */
  public static function abrirContaPoupanca(int $clienteId): ContaPoupanca
  {
    // Validar se cliente existe e está ativo
    $cliente = Cliente::find($clienteId);
    if (!$cliente) {
      throw new ValidationException(['Cliente não encontrado']);
    }

    if (!$cliente->isAtivo()) {
      throw new ValidationException(['Cliente está inativo']);
    }

    // Verificar se cliente já tem conta poupança
    $contasExistentes = Conta::buscarPorCliente($clienteId);
    foreach ($contasExistentes as $conta) {
      if ($conta instanceof ContaPoupanca) {
        throw new ValidationException(['Cliente já possui conta poupança']);
      }
    }

    // Criar conta poupança
    $conta = ContaPoupanca::criar($clienteId);

    return $conta;
  }

  /**
   * Alterar limite do cheque especial
   */
  public static function alterarLimiteChequeEspecial(string $numeroConta, float $novoLimite): bool
  {
    $conta = Conta::buscarPorNumero($numeroConta);

    if (!$conta) {
      throw new ValidationException(['Conta não encontrada']);
    }

    if (!$conta instanceof ContaCorrente) {
      throw new ValidationException(['Apenas contas correntes possuem cheque especial']);
    }

    if ($conta->isBloqueada()) {
      throw new ValidationException(['Conta está bloqueada']);
    }

    if ($novoLimite < 0 || $novoLimite > 10000) {
      throw new ValidationException(['Limite deve estar entre R$ 0 e R$ 10.000']);
    }

    // Verificar se não está usando mais que o novo limite
    $valorUsado = $conta->getValorUsadoChequeEspecial();
    if ($valorUsado > $novoLimite) {
      throw new ValidationException([
        'Não é possível reduzir limite. Valor em uso: R$ ' .
          number_format($valorUsado, 2, ',', '.')
      ]);
    }

    return $conta->alterarLimiteChequeEspecial($novoLimite);
  }

  /**
   * Bloquear conta
   */
  public static function bloquearConta(string $numeroConta, ?string $motivo = null): bool
  {
    $conta = Conta::buscarPorNumero($numeroConta);

    if (!$conta) {
      throw new ValidationException(['Conta não encontrada']);
    }

    if ($conta->isBloqueada()) {
      throw new ValidationException(['Conta já está bloqueada']);
    }

    $sucesso = $conta->bloquear();

    if ($sucesso && $motivo) {
      // Log do motivo do bloqueio (seria implementado com sistema de logs)
      error_log("Conta {$numeroConta} bloqueada. Motivo: {$motivo}");
    }

    return $sucesso;
  }

  /**
   * Desbloquear conta
   */
  public static function desbloquearConta(string $numeroConta): bool
  {
    $conta = Conta::buscarPorNumero($numeroConta);

    if (!$conta) {
      throw new ValidationException(['Conta não encontrada']);
    }

    if (!$conta->isBloqueada()) {
      throw new ValidationException(['Conta não está bloqueada']);
    }

    // Verificar se cliente está ativo
    $cliente = $conta->getCliente();
    if (!$cliente || !$cliente->isAtivo()) {
      throw new ValidationException(['Cliente está inativo']);
    }

    return $conta->desbloquear();
  }

  /**
   * Consultar saldo detalhado
   */
  public static function consultarSaldoDetalhado(string $numeroConta): array
  {
    $conta = Conta::buscarPorNumero($numeroConta);

    if (!$conta) {
      throw new ValidationException(['Conta não encontrada']);
    }

    $saldoDetalhado = [
      'numero_conta' => $conta->numero_conta,
      'tipo_conta' => $conta->tipo_conta,
      'saldo_atual' => $conta->getSaldo(),
      'saldo_formatado' => $conta->getSaldoFormatado(),
      'saldo_disponivel' => $conta->getSaldoDisponivel(),
      'saldo_disponivel_formatado' => 'R$ ' . number_format($conta->getSaldoDisponivel(), 2, ',', '.'),
      'conta_bloqueada' => $conta->isBloqueada(),
    ];

    // Informações específicas por tipo de conta
    if ($conta instanceof ContaCorrente) {
      $saldoDetalhado['limite_cheque_especial'] = $conta->getLimiteChequeEspecial();
      $saldoDetalhado['usando_cheque_especial'] = $conta->isUsandoChequeEspecial();
      $saldoDetalhado['valor_usado_cheque_especial'] = $conta->getValorUsadoChequeEspecial();
      $saldoDetalhado['limite_disponivel_cheque_especial'] = $conta->getLimiteDisponivelChequeEspecial();
    } elseif ($conta instanceof ContaPoupanca) {
      $saldoDetalhado['rendimento_mensal'] = $conta->calcularRendimento();
      $saldoDetalhado['rendimento_mensal_formatado'] = 'R$ ' . number_format($conta->calcularRendimento(), 2, ',', '.');
    }

    return $saldoDetalhado;
  }

  /**
   * Listar todas as contas de um cliente
   */
  public static function listarContasCliente(int $clienteId): array
  {
    $cliente = Cliente::find($clienteId);
    if (!$cliente) {
      throw new ValidationException(['Cliente não encontrado']);
    }

    $contas = Conta::buscarPorCliente($clienteId);

    $resultado = [];
    foreach ($contas as $conta) {
      $dadosConta = [
        'numero_conta' => $conta->numero_conta,
        'tipo_conta' => $conta->tipo_conta,
        'saldo' => $conta->getSaldo(),
        'saldo_formatado' => $conta->getSaldoFormatado(),
        'saldo_disponivel' => $conta->getSaldoDisponivel(),
        'bloqueada' => $conta->isBloqueada(),
        'data_abertura' => $conta->created_at
      ];

      $resultado[] = $dadosConta;
    }

    return [
      'cliente' => [
        'id' => $cliente->id,
        'nome' => $cliente->nome,
        'cpf' => $cliente->getCpfFormatado()
      ],
      'contas' => $resultado,
      'total_contas' => count($resultado)
    ];
  }

  /**
   * Validar se operação pode ser realizada
   */
  public static function validarOperacao(string $numeroConta, string $tipoOperacao, float $valor): bool
  {
    $conta = Conta::buscarPorNumero($numeroConta);

    if (!$conta) {
      throw new ValidationException(['Conta não encontrada']);
    }

    if ($conta->isBloqueada()) {
      throw new ValidationException(['Conta está bloqueada']);
    }

    // Verificar se cliente está ativo
    $cliente = $conta->getCliente();
    if (!$cliente || !$cliente->isAtivo()) {
      throw new ValidationException(['Cliente está inativo']);
    }

    // Validações específicas por tipo de operação
    switch ($tipoOperacao) {
      case 'saque':
      case 'transferencia':
        if (!$conta->temSaldoSuficiente($valor)) {
          throw new ValidationException(['Saldo insuficiente']);
        }

        if ($valor > $conta->limite_diario) {
          throw new ValidationException(['Valor excede limite diário']);
        }
        break;

      case 'deposito':
        if ($valor <= 0) {
          throw new ValidationException(['Valor deve ser maior que zero']);
        }

        if ($valor > 50000) {
          throw new ValidationException(['Valor excede limite máximo para depósito']);
        }
        break;
    }

    return true;
  }
}
