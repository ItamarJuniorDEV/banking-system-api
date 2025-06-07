<?php

namespace SistemaBancario\Utils;

use Exception;

/**
 * EXCEÇÃO DE VALIDAÇÃO
 */
class ValidationException extends Exception
{
    protected array $errors;
    
    public function __construct(array $errors, int $code = 400)
    {
        $this->errors = $errors;
        $message = 'Erro de validação: ' . implode(', ', $errors);
        
        parent::__construct($message, $code);
    }
    
    public function getErrors(): array
    {
        return $this->errors;
    }
}