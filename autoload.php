<?php

/**
 * AUTOLOADER PSR-4 PERSONALIZADO
 * 
 * Este autoloader segue o padrão PSR-4 e carrega automaticamente
 * as classes do sistema bancário sem precisar de require manual.
 * 
 * COMO FUNCIONA:
 * - Namespace: SistemaBancario\Models\Cliente
 * - Arquivo: src/Models/Cliente.php
 * 
 * MAPEAMENTO:
 * SistemaBancario\ → src/
 */

spl_autoload_register(function ($className) {
  $prefix = 'SistemaBancario\\';

  $baseDir = __DIR__ . '/src/';

  $len = strlen($prefix);
  if (strncmp($prefix, $className, $len) !== 0) {

    return;
  }

  $relativeClass = substr($className, $len);

  $file = $baseDir . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';

  if (file_exists($file)) {
    require $file;
  }
});

date_default_timezone_set('America/Sao_Paulo');

error_reporting(E_ALL);
ini_set('display_errors', 1);

ini_set('default_charset', 'UTF-8');
mb_internal_encoding('UTF-8');

echo "Autoloader PSR-4 carregado com sucesso!\n";
