<?php

// CARREGAMENTO DIRETO DAS CLASSES (substitui autoloader que não está funcionando)

require_once __DIR__ . '/src/Database/Connection.php';
require_once __DIR__ . '/src/Database/QueryBuilder.php';
require_once __DIR__ . '/src/Utils/ValidationException.php';
require_once __DIR__ . '/src/Models/Model.php';
require_once __DIR__ . '/src/Models/Cliente.php';
require_once __DIR__ . '/src/Models/Conta.php';
require_once __DIR__ . '/src/Models/ContaCorrente.php';
require_once __DIR__ . '/src/Models/ContaPoupanca.php';
require_once __DIR__ . '/src/Models/Transacao.php';

// Configurações
date_default_timezone_set('America/Sao_Paulo');
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Classes carregadas com sucesso!\n";
