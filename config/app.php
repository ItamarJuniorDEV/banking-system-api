 <?php

  /**
   * CONFIGURAÇÕES DA APLICAÇÃO
   * 
   * Arquivo central de configurações do sistema bancário.
   * Similiar ao config/app.php do Laravel.
   */

  return [
    // Nome da aplicação
    'name' => 'Sistema Bancário PHP',

    // Versão da aplicação
    'version' => '1.0.0',

    // Ambiente (development, production, testing)
    'env' => 'development',

    // Debug mode (true para desenvolvimento)
    'debug' => true,

    // URL base da aplicação
    'url' => 'http://localhost:8000',

    // Timezone da aplicação
    'timezone' => 'America/Sao_Paulo',

    // Configurações de log
    'log' => [
      'level' => 'debug', // debug, info, warning, error
      'file_path' => __DIR__ . '/../storage/logs/',
      'max_file_size' => 10 * 1024 * 1024, // 10MB
      'max_files' => 30, // Manter 30 arquivos de log
    ],

    // Configurações de segurança
    'security' => [
      'hash_algorithm' => 'sha256',
      'encrypt_key' => 'sistema-bancario-key-2025',
      'session_lifetime' => 3600, // 1 hora
    ],

    // Configurações do sistema bancário
    'banco' => [
      'nome' => 'Banco Digital PHP',
      'codigo' => '001',
      'cnpj' => '12.345.678/0001-90',

      // Limites padrão
      'limites_padrao' => [
        'saque_diario' => 5000.00,
        'transferencia_diaria' => 10000.00,
        'pix_diario' => 20000.00,
      ],

      // Tarifas padrão
      'tarifas_padrao' => [
        'saque_corrente' => 4.50,
        'saque_poupanca' => 0.00,
        'transferencia_corrente' => 8.50,
        'transferencia_poupanca' => 1.00,
        'ted' => 15.90,
        'doc' => 12.90,
      ],

      // Configurações de juros
      'juros' => [
        'cheque_especial_mensal' => 0.15, // 15% ao mês
        'poupanca_mensal' => 0.005, // 0.5% ao mês
      ],
    ],

    // Configurações de validação
    'validacao' => [
      'cpf_obrigatorio' => true,
      'email_obrigatorio' => true,
      'telefone_obrigatorio' => true,
      'endereco_obrigatorio' => false,
    ],

    // Configurações de API
    'api' => [
      'rate_limit' => 60, // Requests por minuto
      'cors_enabled' => true,
      'json_response' => true,
    ],
  ];
