<?php

namespace SistemaBancario\Models;

/**
 * MODELO CLIENTE
 * 
 * Representa um cliente do banco.
 */
class Cliente extends Model
{
  protected string $table = 'clientes';

  protected array $fillable = [
    'nome',
    'cpf',
    'email',
    'telefone',
    'endereco',
    'data_nascimento',
    'ativo'
  ];

  /**
   * Buscar cliente por CPF
   */
  public static function buscarPorCpf(string $cpf): ?static
  {
    $data = static::query()
      ->where('cpf', '=', $cpf)
      ->first();

    if ($data) {
      $model = new static($data);
      $model->exists = true;
      return $model;
    }

    return null;
  }

  /**
   * Buscar cliente por email
   */
  public static function buscarPorEmail(string $email): ?static
  {
    $data = static::query()
      ->where('email', '=', $email)
      ->first();

    if ($data) {
      $model = new static($data);
      $model->exists = true;
      return $model;
    }

    return null;
  }

  /**
   * Buscar clientes ativos
   */
  public static function ativos(): array
  {
    $data = static::query()
      ->where('ativo', '=', 1)
      ->orderBy('nome', 'ASC')
      ->get();

    $models = [];
    foreach ($data as $item) {
      $model = new static($item);
      $model->exists = true;
      $models[] = $model;
    }

    return $models;
  }

  /**
   * Ativar cliente
   */
  public function ativar(): bool
  {
    $this->ativo = 1;
    return $this->save();
  }

  /**
   * Desativar cliente
   */
  public function desativar(): bool
  {
    $this->ativo = 0;
    return $this->save();
  }

  /**
   * Verificar se cliente está ativo
   */
  public function isAtivo(): bool
  {
    return (bool) $this->ativo;
  }

  /**
   * Obter nome formatado
   */
  public function getNomeCompleto(): string
  {
    return $this->nome ?? '';
  }

  /**
   * Obter CPF formatado
   */
  public function getCpfFormatado(): string
  {
    $cpf = $this->cpf ?? '';

    if (strlen($cpf) === 11) {
      return substr($cpf, 0, 3) . '.' .
        substr($cpf, 3, 3) . '.' .
        substr($cpf, 6, 3) . '-' .
        substr($cpf, 9, 2);
    }

    return $cpf;
  }

  /**
   * Obter idade
   */
  public function getIdade(): ?int
  {
    if (!$this->data_nascimento) {
      return null;
    }

    $nascimento = new \DateTime($this->data_nascimento);
    $hoje = new \DateTime();

    return $hoje->diff($nascimento)->y;
  }

  /**
   * Validar dados antes de salvar
   */
  public function save(): bool
  {
    // Validações básicas
    if (empty($this->nome)) {
      throw new \Exception('Nome é obrigatório');
    }

    if (empty($this->cpf)) {
      throw new \Exception('CPF é obrigatório');
    }

    if (empty($this->email)) {
      throw new \Exception('Email é obrigatório');
    }

    // Verificar CPF único
    $clienteExistente = static::buscarPorCpf($this->cpf);
    if ($clienteExistente && $clienteExistente->id !== $this->id) {
      throw new \Exception('CPF já cadastrado');
    }

    // Verificar email único
    $clienteExistente = static::buscarPorEmail($this->email);
    if ($clienteExistente && $clienteExistente->id !== $this->id) {
      throw new \Exception('Email já cadastrado');
    }

    return parent::save();
  }
}
