<?php

require_once __DIR__ . '/../autoload.php';

use SistemaBancario\Utils\ValidationException;

echo "=== TESTE DE EXCEÇÃO DE VALIDAÇÃO ===\n\n";

try {
  echo "1. Testando ValidationException...\n";

  $erros = ['CPF inválido', 'Email inválido'];
  throw new ValidationException($erros);
} catch (ValidationException $e) {
  echo "✅ ValidationException capturada com sucesso!\n";
  echo "   Mensagem: " . $e->getMessage() . "\n";
  echo "   Erros: " . implode(', ', $e->getErrors()) . "\n";
  echo "   Código: " . $e->getCode() . "\n\n";
}

echo "🎉 EXCEÇÃO FUNCIONANDO!\n";
