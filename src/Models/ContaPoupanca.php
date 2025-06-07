<?php

namespace SistemaBancario\Models;

/**
 * MODELO CONTA POUPANÇA
 * 
 * Conta poupança com rendimentos e tarifas reduzidas.
 */
class ContaPoupanca extends Conta
{
  protected string $tipo_conta = 'poupanca';

  /**
   * Tarifas da conta poupança (menores que conta corrente)
   */
  private const TARIFAS = [
    'saque' => 0.00,
    'transferencia' => 1.00,
    'ted' => 10.90,
    'doc' => 8.90,
    'pix' => 0.00,
    'deposito' => 0.00,
  ];

  /**
   * Taxa de rendimento mensal
   */
  private const RENDIMENTO_MENSAL = 0.005; // 0.5% ao mês

  /**
   * Criar nova conta poupança
   */
  public static function criar(int $clienteId): static
  {
    $conta = new static();
    $conta->fill([
      'numero_conta' => static::gerarNumeroConta(),
      'cliente_id' => $clienteId,
      'tipo_conta' => 'poupanca',
      'saldo' => 0.00,
      'limite_cheque_especial' => 0.00,
      'bloqueada' => 0,
      'limite_diario' => 3000.00
    ]);

    $conta->save();

    return $conta;
  }

  /**
   * Obter saldo disponível (apenas o saldo, sem cheque especial)
   */
  public function getSaldoDisponivel(): float
  {
    return $this->getSaldo();
  }

  /**
   * Calcular tarifa para operação
   */
  public function calcularTarifa(string $operacao): float
  {
    return self::TARIFAS[$operacao] ?? 0.00;
  }

  /**
   * Calcular rendimento mensal
   */
  public function calcularRendimento(): float
  {
    return $this->getSaldo() * self::RENDIMENTO_MENSAL;
  }

  /**
   * Aplicar rendimento mensal
   */
  public function aplicarRendimento(): bool
  {
    $rendimento = $this->calcularRendimento();

    if ($rendimento > 0) {
      $this->saldo += $rendimento;
      return $this->save();
    }

    return true;
  }

  /**
   * Sacar da poupança (sem tarifa)
   */
  public function sacar(float $valor): bool
  {
    // Verificar limite diário
    if ($valor > $this->limite_diario) {
      throw new \Exception('Valor excede limite diário de saque');
    }

    return $this->debitar($valor);
  }

  /**
   * Depositar na poupança (sem tarifa)
   */
  public function depositar(float $valor): bool
  {
    return $this->creditar($valor);
  }

  /**
   * Fazer PIX (sem tarifa)
   */
  public function pix(Conta $contaDestino, float $valor): bool
  {
    if ($this->numero_conta === $contaDestino->numero_conta) {
      throw new \InvalidArgumentException('Não é possível fazer PIX para a mesma conta');
    }

    $this->debitar($valor);
    $contaDestino->creditar($valor);

    return true;
  }

  /**
   * Fazer TED (com tarifa menor)
   */
  public function ted(Conta $contaDestino, float $valor): bool
  {
    $tarifa = $this->calcularTarifa('ted');
    $valorTotal = $valor + $tarifa;

    $this->debitar($valorTotal);
    $contaDestino->creditar($valor);

    return true;
  }

  /**
   * Transferir para conta corrente do mesmo cliente (sem tarifa)
   */
  public function transferirParaContaCorrente(float $valor): bool
  {
    // Buscar conta corrente do mesmo cliente
    $contas = static::buscarPorCliente($this->cliente_id);

    $contaCorrente = null;
    foreach ($contas as $conta) {
      if ($conta instanceof ContaCorrente) {
        $contaCorrente = $conta;
        break;
      }
    }

    if (!$contaCorrente) {
      throw new \Exception('Cliente não possui conta corrente');
    }

    // Transferir sem tarifa
    $this->debitar($valor);
    $contaCorrente->creditar($valor);

    return true;
  }

  /**
   * Projeção de rendimento futuro
   */
  public function projetarRendimento(int $meses): array
  {
    $saldoAtual = $this->getSaldo();
    $projecao = [];

    for ($mes = 1; $mes <= $meses; $mes++) {
      $rendimento = $saldoAtual * self::RENDIMENTO_MENSAL;
      $saldoAtual += $rendimento;

      $projecao[] = [
        'mes' => $mes,
        'rendimento' => $rendimento,
        'saldo_projetado' => $saldoAtual,
        'rendimento_formatado' => 'R$ ' . number_format($rendimento, 2, ',', '.'),
        'saldo_formatado' => 'R$ ' . number_format($saldoAtual, 2, ',', '.')
      ];
    }

    return $projecao;
  }

  /**
   * Verificar se pode debitar (poupança não tem cheque especial)
   */
  public function debitar(float $valor): bool
  {
    if ($valor > $this->getSaldo()) {
      throw new \Exception('Saldo insuficiente. Conta poupança não possui cheque especial');
    }

    return parent::debitar($valor);
  }

  /**
   * Converter para array com dados específicos da conta poupança
   */
  public function toArray(): array
  {
    $data = parent::toArray();

    $data['rendimento_mensal'] = $this->calcularRendimento();
    $data['rendimento_mensal_formatado'] = 'R$ ' . number_format($this->calcularRendimento(), 2, ',', '.');
    $data['taxa_rendimento'] = self::RENDIMENTO_MENSAL * 100 . '%';
    $data['tarifas'] = self::TARIFAS;
    $data['limite_cheque_especial'] = 0.00; // Sempre zero

    return $data;
  }
}
