<?php

namespace SistemaBancario\Controllers;

use SistemaBancario\Models\Cliente;
use SistemaBancario\Utils\Response;

class ClienteController extends BaseController
{
  public function criar(): void
  {
    try {
      $data = $this->getJsonInput();

      $this->validateRequired($data, ['nome', 'cpf', 'email', 'telefone']);

      $cliente = Cliente::create([
        'nome' => $data['nome'],
        'cpf' => $data['cpf'],
        'email' => $data['email'],
        'telefone' => $data['telefone'],
        'ativo' => 1
      ]);

      Response::success([
        'id' => $cliente->id,
        'nome' => $cliente->nome,
        'cpf' => $cliente->cpf,
        'email' => $cliente->email,
        'telefone' => $cliente->telefone
      ]);
    } catch (\Exception $e) {
      $this->handleException($e);
    }
  }

  public function buscar(int $id): void
  {
    try {
      $cliente = Cliente::find($id);

      if (!$cliente) {
        Response::error('Cliente não encontrado', 404);
      }

      Response::success([
        'id' => $cliente->id,
        'nome' => $cliente->nome,
        'cpf' => $cliente->cpf,
        'email' => $cliente->email,
        'telefone' => $cliente->telefone,
        'ativo' => $cliente->ativo
      ]);
    } catch (\Exception $e) {
      $this->handleException($e);
    }
  }
}
