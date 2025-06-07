<?php

namespace SistemaBancario\Models;

use SistemaBancario\Database\QueryBuilder;
use Exception;

/**
 * CLASSE MODEL BASE
 * 
 * Classe base para todos os modelos do sistema.
 * Implementa padrão Active Record simplificado.
 */
abstract class Model
{
  protected string $table = '';
  protected string $primaryKey = 'id';
  protected array $fillable = [];
  protected array $attributes = [];
  protected bool $exists = false;

  public function __construct(array $attributes = [])
  {
    $this->fill($attributes);
  }

  /**
   * Preencher atributos
   */
  public function fill(array $attributes): self
  {
    foreach ($attributes as $key => $value) {
      if (in_array($key, $this->fillable) || empty($this->fillable)) {
        $this->attributes[$key] = $value;
      }
    }

    return $this;
  }

  /**
   * Getter mágico
   */
  public function __get(string $name): mixed
  {
    return $this->attributes[$name] ?? null;
  }

  /**
   * Setter mágico
   */
  public function __set(string $name, mixed $value): void
  {
    $this->attributes[$name] = $value;
  }

  /**
   * Verificar se atributo existe
   */
  public function __isset(string $name): bool
  {
    return isset($this->attributes[$name]);
  }

  /**
   * Salvar modelo
   */
  public function save(): bool
  {
    if ($this->exists) {
      return $this->update();
    }

    return $this->insert();
  }

  /**
   * Inserir novo registro
   */
  protected function insert(): bool
  {
    $data = $this->getAttributesForSave();
    $data['created_at'] = date('Y-m-d H:i:s');
    $data['updated_at'] = date('Y-m-d H:i:s');

    $id = QueryBuilder::create($this->table)->insert($data);

    if ($id) {
      $this->attributes[$this->primaryKey] = $id;
      $this->exists = true;
      return true;
    }

    return false;
  }

  /**
   * Atualizar registro existente
   */
  protected function update(): bool
  {
    $data = $this->getAttributesForSave();
    $data['updated_at'] = date('Y-m-d H:i:s');

    $affected = QueryBuilder::create($this->table)
      ->where($this->primaryKey, '=', $this->attributes[$this->primaryKey])
      ->update($data);

    return $affected > 0;
  }

  /**
   * Deletar modelo
   */
  public function delete(): bool
  {
    if (!$this->exists) {
      return false;
    }

    $affected = QueryBuilder::create($this->table)
      ->where($this->primaryKey, '=', $this->attributes[$this->primaryKey])
      ->delete();

    if ($affected > 0) {
      $this->exists = false;
      return true;
    }

    return false;
  }

  /**
   * Buscar por ID
   */
  public static function find(int $id): ?static
  {
    $instance = new static();

    $data = QueryBuilder::create($instance->table)
      ->where($instance->primaryKey, '=', $id)
      ->first();

    if ($data) {
      $model = new static($data);
      $model->exists = true;
      return $model;
    }

    return null;
  }

  /**
   * Buscar todos os registros
   */
  public static function all(): array
  {
    $instance = new static();

    $data = QueryBuilder::create($instance->table)->get();

    $models = [];
    foreach ($data as $item) {
      $model = new static($item);
      $model->exists = true;
      $models[] = $model;
    }

    return $models;
  }

  /**
   * Query builder
   */
  public static function query(): QueryBuilder
  {
    $instance = new static();
    return QueryBuilder::create($instance->table);
  }

  /**
   * Criar novo modelo
   */
  public static function create(array $attributes): static
  {
    $model = new static($attributes);
    $model->save();

    return $model;
  }

  /**
   * Obter atributos para salvar
   */
  protected function getAttributesForSave(): array
  {
    $data = $this->attributes;

    // Remover primary key se for auto-increment
    if (isset($data[$this->primaryKey]) && !$this->exists) {
      unset($data[$this->primaryKey]);
    }

    return $data;
  }

  /**
   * Converter para array
   */
  public function toArray(): array
  {
    return $this->attributes;
  }

  /**
   * Converter para JSON
   */
  public function toJson(): string
  {
    return json_encode($this->attributes, JSON_UNESCAPED_UNICODE);
  }

  /**
   * Verificar se modelo existe no banco
   */
  public function exists(): bool
  {
    return $this->exists;
  }

  /**
   * Recarregar modelo do banco
   */
  public function fresh(): ?static
  {
    if (!$this->exists) {
      return null;
    }

    return static::find($this->attributes[$this->primaryKey]);
  }
}
