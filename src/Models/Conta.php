<?php

namespace SistemaBancario\Models;

use SistemaBancario\Utils\ValidationException;

/**
 * MODELO BASE DE CONTA
 * 
 * Classe base para todos os tipos de conta bancária.
 */
abstract class Conta extends Model
{
  protected string $table = 'contas';

  protected array $fillable = [
    'numero_conta',
    'cliente_id',
    'tipo_conta',
    'saldo',
    'limite_cheque_especial',
    'bloqueada',
    'limite_diario'
  ];

  /**
   * Gerar número de conta único
   */
  public static function gerarNumeroConta(): string
  {
    do {
      $numero = rand(10000, 99999) . '-' . rand(0, 9);
      $existe = static::buscarPorNumero($numero);
    } while ($existe);

    return $numero;
  }

  /**
   * Buscar conta por número
   */
  public static function buscarPorNumero(string $numero): ?static
  {
    $data = static::query()
      ->where('numero_conta', '=', $numero)
      ->first();

    if ($data) {
      // Instanciar a classe correta baseada no tipo
      $className = match ($data['tipo_conta']) {
        'corrente' => ContaCorrente::class,
        'poupanca' => ContaPoupanca::class,
        default => static::class
      };

      $model = new $className($data);
      $model->exists = true;
      return $model;
    }

    return null;
  }

  /**
   * Buscar contas de um cliente
   */
  public static function buscarPorCliente(int $clienteId): array
  {
    $data = static::query()
      ->where('cliente_id', '=', $clienteId)
      ->orderBy('created_at', 'ASC')
      ->get();

    $contas = [];
    foreach ($data as $item) {
      $className = match ($item['tipo_conta']) {
        'corrente' => ContaCorrente::class,
        'poupanca' => ContaPoupanca::class,
        default => static::class
      };

      $model = new $className($item);
      $model->exists = true;
      $contas[] = $model;
    }

    return $contas;
  }

  /**
   * Obter cliente da conta
   */
  public function getCliente(): ?Cliente
  {
    if (!$this->cliente_id) {
      return null;
    }

    return Cliente::find($this->cliente_id);
  }

  /**
   * Verificar se conta está bloqueada
   */
  public function isBloqueada(): bool
  {
    return (bool) $this->bloqueada;
  }

  /**
   * Bloquear conta
   */
  public function bloquear(): bool
  {
    $this->bloqueada = 1;
    return $this->save();
  }

  /**
   * Desbloquear conta
   */
  public function desbloquear(): bool
  {
    $this->bloqueada = 0;
    return $this->save();
  }

  /**
   * Obter saldo atual
   */
  public function getSaldo(): float
  {
    return (float) $this->saldo;
  }

  /**
   * Obter saldo formatado
   */
  public function getSaldoFormatado(): string
  {
    return 'R$ ' . number_format($this->getSaldo(), 2, ',', '.');
  }

  /**
   * Verificar se tem saldo suficiente
   */
  public function temSaldoSuficiente(float $valor): bool
  {
    return $this->getSaldoDisponivel() >= $valor;
  }

  /**
   * Obter saldo disponível (inclui limite se for conta corrente)
   */
  abstract public function getSaldoDisponivel(): float;

  /**
   * Calcular tarifa para operação
   */
  abstract public function calcularTarifa(string $operacao): float;

  /**
   * Creditar valor na conta
   */
  public function creditar(float $valor): bool
  {
    if ($valor <= 0) {
      throw new ValidationException(['Valor deve ser maior que zero']);
    }

    $this->saldo += $valor;
    return $this->save();
  }

  /**
   * Debitar valor da conta
   */
  public function debitar(float $valor): bool
  {
    if ($valor <= 0) {
      throw new ValidationException(['Valor deve ser maior que zero']);
    }

    if ($this->isBloqueada()) {
      throw new ValidationException(['Conta está bloqueada']);
    }

    if (!$this->temSaldoSuficiente($valor)) {
      throw new ValidationException(['Saldo insuficiente']);
    }

    $this->saldo -= $valor;
    return $this->save();
  }

  /**
   * Transferir para outra conta
   */
  public function transferirPara(Conta $contaDestino, float $valor): bool
  {
    if ($this->numero_conta === $contaDestino->numero_conta) {
      throw new ValidationException(['Não é possível transferir para a mesma conta']);
    }

    // Calcular tarifa
    $tarifa = $this->calcularTarifa('transferencia');
    $valorTotal = $valor + $tarifa;

    // Verificar se tem saldo para valor + tarifa
    if (!$this->temSaldoSuficiente($valorTotal)) {
      throw new ValidationException(['Saldo insuficiente para valor + tarifa']);
    }

    // Executar transferência
    $this->debitar($valorTotal);
    $contaDestino->creditar($valor);

    return true;
  }

  /**
   * Validar antes de salvar
   */
  public function save(): bool
  {
    // Definir tipo_conta se não estiver definido
    if (empty($this->tipo_conta)) {
      if ($this instanceof ContaCorrente) {
        $this->tipo_conta = 'corrente';
      } elseif ($this instanceof ContaPoupanca) {
        $this->tipo_conta = 'poupanca';
      }
    }

    // Gerar número se não existir
    if (empty($this->numero_conta)) {
      $this->numero_conta = static::gerarNumeroConta();
    }

    if (empty($this->cliente_id)) {
      throw new ValidationException(['Cliente é obrigatório']);
    }

    if (empty($this->tipo_conta)) {
      throw new ValidationException(['Tipo de conta é obrigatório']);
    }

    // Verificar se cliente existe
    $cliente = Cliente::find($this->cliente_id);
    if (!$cliente) {
      throw new ValidationException(['Cliente não encontrado']);
    }

    return parent::save();
  }

  /**
   * Converter para array com dados formatados
   */
  public function toArray(): array
  {
    $data = parent::toArray();

    $data['saldo_formatado'] = $this->getSaldoFormatado();
    $data['saldo_disponivel'] = $this->getSaldoDisponivel();
    $data['bloqueada_texto'] = $this->isBloqueada() ? 'Sim' : 'Não';

    return $data;
  }
}
