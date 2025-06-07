<?php

namespace SistemaBancario\Controllers;

use SistemaBancario\Utils\Response;
use SistemaBancario\Utils\ValidationException;

abstract class BaseController
{
  protected function getJsonInput(): array
  {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
      Response::error('JSON inválido: ' . json_last_error_msg(), 400);
    }

    return $data ?? [];
  }

  protected function validateRequired(array $data, array $required): void
  {
    foreach ($required as $field) {
      if (!isset($data[$field]) || empty($data[$field])) {
        Response::error("Campo obrigatório: {$field}", 400);
      }
    }
  }

  protected function handleException(\Exception $e): void
  {
    // DEBUG: Log do erro completo
    error_log("ERRO API: " . $e->getMessage() . " - Arquivo: " . $e->getFile() . " - Linha: " . $e->getLine());

    if ($e instanceof ValidationException) {
      Response::error($e->getMessage(), 400);
    }

    Response::error('Erro interno do servidor: ' . $e->getMessage(), 500);
  }
}
