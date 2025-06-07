 <?php

  /**
   * CONFIGURAÇÕES DO BANCO DE DADOS
   * 
   * Configurações para conexão com SQLite usando PDO.
   * Similar ao config/database.php do Laravel.
   */

  return [
    // Conexão padrão
    'default' => 'sqlite',

    // Configurações das conexões
    'connections' => [
      'sqlite' => [
        'driver' => 'sqlite',
        'database' => __DIR__ . '/../database/banco.sqlite',
        'prefix' => '',
        'foreign_key_constraints' => true,

        // Opções PDO específicas do SQLite
        'options' => [
          PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
          PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
          PDO::ATTR_EMULATE_PREPARES => false,
          PDO::ATTR_STRINGIFY_FETCHES => false,
        ],

        // Configurações de performance
        'pragma' => [
          'journal_mode' => 'WAL',          // Write-Ahead Logging
          'synchronous' => 'NORMAL',        // Balanço entre segurança e performance
          'cache_size' => -64000,           // 64MB de cache
          'foreign_keys' => 'ON',           // Habilitar foreign keys
          'temp_store' => 'MEMORY',         // Armazenar dados temporários em memória
        ],
      ],

      // Configuração para testes (banco em memória)
      'testing' => [
        'driver' => 'sqlite',
        'database' => ':memory:',
        'prefix' => '',
        'foreign_key_constraints' => true,

        'options' => [
          PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
          PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
          PDO::ATTR_EMULATE_PREPARES => false,
        ],
      ],
    ],

    // Configurações de migrations
    'migrations' => [
      'table' => 'migrations',
      'path' => __DIR__ . '/../database/migrations',
    ],

    // Configurações de seeders
    'seeders' => [
      'path' => __DIR__ . '/../database/seeders',
    ],

    // Pool de conexões 
    'pool' => [
      'enabled' => false,
      'max_connections' => 10,
      'min_connections' => 1,
    ],

    // Configurações de backup
    'backup' => [
      'enabled' => true,
      'path' => __DIR__ . '/../storage/backups/',
      'frequency' => 'daily', // daily, weekly, monthly
      'keep_files' => 30, // Manter 30 backups
    ],
  ];
