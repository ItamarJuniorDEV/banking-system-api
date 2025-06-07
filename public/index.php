<?php

// Includes manuais (como fazíamos nos testes)
require_once __DIR__ . '/../src/Database/Connection.php';
require_once __DIR__ . '/../src/Database/QueryBuilder.php';
require_once __DIR__ . '/../src/Models/Model.php';
require_once __DIR__ . '/../src/Models/Cliente.php';
require_once __DIR__ . '/../src/Models/Conta.php';
require_once __DIR__ . '/../src/Models/ContaCorrente.php';
require_once __DIR__ . '/../src/Models/ContaPoupanca.php';
require_once __DIR__ . '/../src/Utils/ValidationException.php';
require_once __DIR__ . '/../src/Utils/Response.php';
require_once __DIR__ . '/../src/Controllers/BaseController.php';
require_once __DIR__ . '/../src/Controllers/ClienteController.php';
require_once __DIR__ . '/../src/Controllers/ContaController.php';
require_once __DIR__ . '/../src/Router.php';

use SistemaBancario\Router;
use SistemaBancario\Controllers\ClienteController;
use SistemaBancario\Controllers\ContaController;
use SistemaBancario\Utils\Response;

// Headers CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(200);
  exit;
}

try {
  $router = new Router();

  $router->post('/clientes', function () {
    $controller = new ClienteController();
    $controller->criar();
  });

  $router->get('/clientes/{id}', function ($id) {
    $controller = new ClienteController();
    $controller->buscar((int)$id);
  });

  $router->post('/contas/corrente', function () {
    $controller = new ContaController();
    $controller->abrirCorrente();
  });

  $router->post('/contas/poupanca', function () {
    $controller = new ContaController();
    $controller->abrirPoupanca();
  });

  $router->get('/contas/{numero}/saldo', function ($numero) {
    $controller = new ContaController();
    $controller->consultarSaldo($numero);
  });

  $router->dispatch();
} catch (\Exception $e) {
  Response::error('Erro interno: ' . $e->getMessage(), 500);
}
