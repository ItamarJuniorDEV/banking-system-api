<?php

namespace SistemaBancario\Models;

use SistemaBancario\Utils\ValidationException;
use SistemaBancario\Database\QueryBuilder;

class Transacao extends Model
{
  protected string $table = 'transacoes';

  protected array $fillable = [
    'conta_origem_id',
    'conta_destino_id',
    'tipo_operacao',
    'valor',
    'tarifa',
    'descricao',
    'saldo_anterior',
    'saldo_posterior',
    'data_transacao',
    'status'
  ];

  public static function registrarDeposito(Conta $conta, float $valor, ?string $descricao = null): static
  {
    $saldoAnterior = $conta->getSaldo();

    return static::create([
      'conta_destino_id' => $conta->id,
      'tipo_operacao' => 'deposito',
      'valor' => $valor,
      'tarifa' => 0.00,
      'descricao' => $descricao ?? 'Depósito em conta',
      'saldo_anterior' => $saldoAnterior,
      'saldo_posterior' => $saldoAnterior + $valor,
      'data_transacao' => date('Y-m-d H:i:s'),
      'status' => 'concluida'
    ]);
  }

  public static function registrarSaque(Conta $conta, float $valor, float $tarifa = 0.00, ?string $descricao = null): static
  {
    $saldoAnterior = $conta->getSaldo();
    $valorTotal = $valor + $tarifa;

    return static::create([
      'conta_origem_id' => $conta->id,
      'tipo_operacao' => 'saque',
      'valor' => $valor,
      'tarifa' => $tarifa,
      'descricao' => $descricao ?? 'Saque em conta',
      'saldo_anterior' => $saldoAnterior,
      'saldo_posterior' => $saldoAnterior - $valorTotal,
      'data_transacao' => date('Y-m-d H:i:s'),
      'status' => 'concluida'
    ]);
  }

  public static function buscarPorConta(int $contaId, int $limite = 50): array
  {
    $data = static::query()
      ->where('conta_origem_id', '=', $contaId)
      ->orWhere('conta_destino_id', '=', $contaId)
      ->orderBy('data_transacao', 'DESC')
      ->limit($limite)
      ->get();

    $transacoes = [];
    foreach ($data as $item) {
      $model = new static($item);
      $model->exists = true;
      $transacoes[] = $model;
    }

    return $transacoes;
  }

  public function isDebito(int $contaId): bool
  {
    return $this->conta_origem_id == $contaId;
  }

  public function isCredito(int $contaId): bool
  {
    return $this->conta_destino_id == $contaId;
  }

  public function getValorFormatado(): string
  {
    return 'R$ ' . number_format($this->valor, 2, ',', '.');
  }

  public function getTarifaFormatada(): string
  {
    return 'R$ ' . number_format($this->tarifa, 2, ',', '.');
  }

  public function getDataFormatada(): string
  {
    return date('d/m/Y H:i:s', strtotime($this->data_transacao));
  }

  public function save(): bool
  {
    if (empty($this->status)) {
      $this->status = 'concluida';
    }

    if (empty($this->data_transacao)) {
      $this->data_transacao = date('Y-m-d H:i:s');
    }

    // Usar insert/update customizado sem updated_at
    if ($this->exists) {
      $data = $this->attributes;
      unset($data['id']);

      $affected = QueryBuilder::create($this->table)
        ->where('id', '=', $this->attributes['id'])
        ->update($data);

      return $affected > 0;
    } else {
      $data = $this->attributes;
      $data['created_at'] = date('Y-m-d H:i:s');

      $id = QueryBuilder::create($this->table)->insert($data);

      if ($id) {
        $this->attributes['id'] = $id;
        $this->exists = true;
        return true;
      }
    }

    return false;
  }
}
