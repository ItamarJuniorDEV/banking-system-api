<?php

namespace SistemaBancario\Database;

/**
 * QUERY BUILDER SIMPLES
 * 
 * Construtor de queries SQL de forma programática.
 * Similar ao Query Builder do Laravel.
 */
class QueryBuilder
{
  private string $table = '';
  private array $selects = ['*'];
  private array $wheres = [];
  private array $joins = [];
  private array $orderBy = [];
  private ?int $limitValue = null;
  private array $bindings = [];

  /**
   * Definir tabela
   */
  public function table(string $table): self
  {
    $this->table = $table;
    return $this;
  }

  /**
   * Definir campos SELECT
   */
  public function select(array|string $columns = ['*']): self
  {
    $this->selects = is_array($columns) ? $columns : [$columns];
    return $this;
  }

  /**
   * Adicionar WHERE
   */
  public function where(string $column, string $operator, mixed $value): self
  {
    $this->wheres[] = [
      'column' => $column,
      'operator' => $operator,
      'value' => $value,
      'boolean' => 'AND'
    ];

    $this->bindings[] = $value;
    return $this;
  }

  /**
   * Adicionar OR WHERE
   */
  public function orWhere(string $column, string $operator, mixed $value): self
  {
    $this->wheres[] = [
      'column' => $column,
      'operator' => $operator,
      'value' => $value,
      'boolean' => 'OR'
    ];

    $this->bindings[] = $value;
    return $this;
  }

  /**
   * Adicionar JOIN
   */
  public function join(string $table, string $first, string $operator, string $second): self
  {
    $this->joins[] = [
      'type' => 'INNER',
      'table' => $table,
      'first' => $first,
      'operator' => $operator,
      'second' => $second
    ];

    return $this;
  }

  /**
   * Adicionar LEFT JOIN
   */
  public function leftJoin(string $table, string $first, string $operator, string $second): self
  {
    $this->joins[] = [
      'type' => 'LEFT',
      'table' => $table,
      'first' => $first,
      'operator' => $operator,
      'second' => $second
    ];

    return $this;
  }

  /**
   * Adicionar ORDER BY
   */
  public function orderBy(string $column, string $direction = 'ASC'): self
  {
    $this->orderBy[] = "{$column} {$direction}";
    return $this;
  }

  /**
   * Adicionar LIMIT
   */
  public function limit(int $value): self
  {
    $this->limitValue = $value;
    return $this;
  }

  /**
   * Construir query SELECT
   */
  public function toSql(): string
  {
    $sql = "SELECT " . implode(', ', $this->selects);
    $sql .= " FROM {$this->table}";

    // Adicionar JOINs
    foreach ($this->joins as $join) {
      $sql .= " {$join['type']} JOIN {$join['table']} ON {$join['first']} {$join['operator']} {$join['second']}";
    }

    // Adicionar WHEREs
    if (!empty($this->wheres)) {
      $sql .= " WHERE ";
      $whereStrings = [];

      foreach ($this->wheres as $index => $where) {
        $whereString = "{$where['column']} {$where['operator']} ?";

        if ($index > 0) {
          $whereString = "{$where['boolean']} {$whereString}";
        }

        $whereStrings[] = $whereString;
      }

      $sql .= implode(' ', $whereStrings);
    }

    // Adicionar ORDER BY
    if (!empty($this->orderBy)) {
      $sql .= " ORDER BY " . implode(', ', $this->orderBy);
    }

    // Adicionar LIMIT
    if ($this->limitValue !== null) {
      $sql .= " LIMIT {$this->limitValue}";
    }

    return $sql;
  }

  /**
   * Executar query e retornar todos os resultados
   */
  public function get(): array
  {
    $sql = $this->toSql();
    return Connection::fetchAll($sql, $this->bindings);
  }

  /**
   * Executar query e retornar primeiro resultado
   */
  public function first(): ?array
  {
    $this->limit(1);
    $sql = $this->toSql();
    return Connection::fetchOne($sql, $this->bindings);
  }

  /**
   * Buscar por ID
   */
  public function find(int $id): ?array
  {
    return $this->where('id', '=', $id)->first();
  }

  /**
   * Inserir registro
   */
  public function insert(array $data): int
  {
    $columns = array_keys($data);
    $placeholders = array_fill(0, count($data), '?');

    $sql = "INSERT INTO {$this->table} (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";

    return Connection::insert($sql, array_values($data));
  }

  /**
   * Atualizar registros
   */
  public function update(array $data): int
  {
    $sets = [];
    $bindings = [];

    foreach ($data as $column => $value) {
      $sets[] = "{$column} = ?";
      $bindings[] = $value;
    }

    // Adicionar bindings dos WHEREs
    $bindings = array_merge($bindings, $this->bindings);

    $sql = "UPDATE {$this->table} SET " . implode(', ', $sets);

    // Adicionar WHEREs
    if (!empty($this->wheres)) {
      $sql .= " WHERE ";
      $whereStrings = [];

      foreach ($this->wheres as $index => $where) {
        $whereString = "{$where['column']} {$where['operator']} ?";

        if ($index > 0) {
          $whereString = "{$where['boolean']} {$whereString}";
        }

        $whereStrings[] = $whereString;
      }

      $sql .= implode(' ', $whereStrings);
    }

    return Connection::execute($sql, $bindings);
  }

  /**
   * Deletar registros
   */
  public function delete(): int
  {
    $sql = "DELETE FROM {$this->table}";

    // Adicionar WHEREs
    if (!empty($this->wheres)) {
      $sql .= " WHERE ";
      $whereStrings = [];

      foreach ($this->wheres as $index => $where) {
        $whereString = "{$where['column']} {$where['operator']} ?";

        if ($index > 0) {
          $whereString = "{$where['boolean']} {$whereString}";
        }

        $whereStrings[] = $whereString;
      }

      $sql .= implode(' ', $whereStrings);
    }

    return Connection::execute($sql, $this->bindings);
  }

  /**
   * Contar registros
   */
  public function count(): int
  {
    $originalSelects = $this->selects;
    $this->selects = ['COUNT(*) as total'];

    $sql = $this->toSql();
    $result = Connection::fetchOne($sql, $this->bindings);

    $this->selects = $originalSelects;

    return (int) $result['total'];
  }

  /**
   * Verificar se existe
   */
  public function exists(): bool
  {
    return $this->count() > 0;
  }

  /**
   * Criar nova instância (método estático)
   */
  public static function create(string $table): self
  {
    return (new self())->table($table);
  }
}
