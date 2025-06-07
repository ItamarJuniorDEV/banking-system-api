<?php

namespace SistemaBancario\Models;

use SistemaBancario\Utils\ValidationException;
use SistemaBancario\Database\QueryBuilder;

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

  public static function gerarNumeroConta(): string
  {
    do {
      $numero = rand(10000, 99999) . '-' . rand(0, 9);
      $existe = static::buscarPorNumero($numero);
    } while ($existe);

    return $numero;
  }

  public static function buscarPorNumero(string $numero): ?Conta
  {
    $data = QueryBuilder::create('contas')
      ->where('numero_conta', '=', $numero)
      ->first();

    if ($data) {
      $className = match ($data['tipo_conta']) {
        'corrente' => ContaCorrente::class,
        'poupanca' => ContaPoupanca::class,
        default => ContaCorrente::class
      };

      $model = new $className($data);
      $model->exists = true;
      return $model;
    }

    return null;
  }

  public static function buscarPorCliente(int $clienteId): array
  {
    $data = QueryBuilder::create('contas')
      ->where('cliente_id', '=', $clienteId)
      ->orderBy('created_at', 'ASC')
      ->get();

    $contas = [];
    foreach ($data as $item) {
      $className = match ($item['tipo_conta']) {
        'corrente' => ContaCorrente::class,
        'poupanca' => ContaPoupanca::class,
        default => ContaCorrente::class
      };

      $model = new $className($item);
      $model->exists = true;
      $contas[] = $model;
    }

    return $contas;
  }

  public function getCliente(): ?Cliente
  {
    return Cliente::find($this->cliente_id);
  }

  public function getSaldo(): float
  {
    return (float) $this->saldo;
  }

  public function getSaldoFormatado(): string
  {
    return 'R$ ' . number_format($this->getSaldo(), 2, ',', '.');
  }

  public function isBloqueada(): bool
  {
    return (bool) $this->bloqueada;
  }

  public function bloquear(): bool
  {
    $this->bloqueada = 1;
    return $this->save();
  }

  public function desbloquear(): bool
  {
    $this->bloqueada = 0;
    return $this->save();
  }

  public function creditar(float $valor): bool
  {
    $this->saldo += $valor;
    return $this->save();
  }

  public function debitar(float $valor): bool
  {
    $this->saldo -= $valor;
    return $this->save();
  }

  public function temSaldoSuficiente(float $valor): bool
  {
    return $this->getSaldoDisponivel() >= $valor;
  }

  abstract public function getSaldoDisponivel(): float;
  abstract public function calcularTarifa(string $operacao): float;
}
