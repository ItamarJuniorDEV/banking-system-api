<?php

namespace SistemaBancario\Controllers;

use SistemaBancario\Models\Cliente;
use SistemaBancario\Models\ContaCorrente;
use SistemaBancario\Models\ContaPoupanca;
use SistemaBancario\Models\Conta;
use SistemaBancario\Utils\Response;

class ContaController extends BaseController
{
  public function abrirCorrente(): void
  {
    try {
      $data = $this->getJsonInput();

      $this->validateRequired($data, ['cliente_id']);

      $cliente = Cliente::find($data['cliente_id']);
      if (!$cliente) {
        Response::error('Cliente não encontrado', 404);
        return;
      }

      // Verificar se já tem conta corrente - USAR $data['cliente_id'] em vez de $cliente->id
      $contas = Conta::buscarPorCliente((int)$data['cliente_id']);
      foreach ($contas as $conta) {
        if ($conta instanceof ContaCorrente) {
          Response::error('Cliente já possui conta corrente', 400);
          return;
        }
      }

      $conta = ContaCorrente::create([
        'numero_conta' => Conta::gerarNumeroConta(),
        'cliente_id' => (int)$data['cliente_id'],
        'tipo_conta' => 'corrente',
        'saldo' => 0,
        'limite_cheque_especial' => $data['limite_cheque_especial'] ?? 1500,
        'bloqueada' => 0,
        'limite_diario' => $data['limite_diario'] ?? 5000
      ]);

      Response::success([
        'numero_conta' => $conta->numero_conta,
        'tipo' => 'corrente',
        'saldo' => $conta->getSaldoFormatado(),
        'limite_cheque_especial' => 'R$ ' . number_format($conta->limite_cheque_especial, 2, ',', '.'),
        'cliente' => $cliente->nome
      ]);
    } catch (\Exception $e) {
      $this->handleException($e);
    }
  }

  public function abrirPoupanca(): void
  {
    try {
      $data = $this->getJsonInput();

      $this->validateRequired($data, ['cliente_id']);

      $cliente = Cliente::find($data['cliente_id']);
      if (!$cliente) {
        Response::error('Cliente não encontrado', 404);
        return;
      }

      $conta = ContaPoupanca::create([
        'numero_conta' => Conta::gerarNumeroConta(),
        'cliente_id' => (int)$data['cliente_id'],
        'tipo_conta' => 'poupanca',
        'saldo' => 0,
        'limite_cheque_especial' => 0,
        'bloqueada' => 0,
        'limite_diario' => $data['limite_diario'] ?? 3000
      ]);

      Response::success([
        'numero_conta' => $conta->numero_conta,
        'tipo' => 'poupanca',
        'saldo' => $conta->getSaldoFormatado(),
        'cliente' => $cliente->nome
      ]);
    } catch (\Exception $e) {
      $this->handleException($e);
    }
  }

  public function consultarSaldo(string $numero): void
  {
    try {
      $conta = Conta::buscarPorNumero($numero);

      if (!$conta) {
        Response::error('Conta não encontrada', 404);
        return;
      }

      Response::success([
        'numero_conta' => $conta->numero_conta,
        'tipo' => $conta->tipo_conta,
        'saldo' => $conta->getSaldoFormatado(),
        'saldo_disponivel' => 'R$ ' . number_format($conta->getSaldoDisponivel(), 2, ',', '.'),
        'bloqueada' => $conta->isBloqueada()
      ]);
    } catch (\Exception $e) {
      $this->handleException($e);
    }
  }
}
