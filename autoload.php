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
    // Prefixo do namespace do projeto
    $prefix = 'SistemaBancario\\';
    
    // Diretório base onde estão as classes
    $baseDir = __DIR__ . '/src/';
    
    // Verifica se a classe usa o namespace do projeto
    $len = strlen($prefix);
    if (strncmp($prefix, $className, $len) !== 0) {
        // Não é uma classe do nosso projeto, ignora
        return;
    }
    
    // Remove o prefixo do namespace
    $relativeClass = substr($className, $len);
    
    // Converte namespace para caminho de arquivo
    $file = $baseDir . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';
    
    // Se o arquivo existe, carrega
    if (file_exists($file)) {
        require $file;
    }
});

// Timezone padrão
date_default_timezone_set('America/Sao_Paulo');

// Configurações de erro para desenvolvimento
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configurações de charset
ini_set('default_charset', 'UTF-8');
mb_internal_encoding('UTF-8');

echo "Autoloader PSR-4 carregado com sucesso!\n";