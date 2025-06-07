<?php

namespace SistemaBancario\Utils;

/**
 * SISTEMA DE VALIDAÇÃO
 * 
 * Validadores para dados bancários e pessoais.
 */
class Validator
{
  /**
   * Validar CPF
   */
  public static function cpf(string $cpf): bool
  {
    // Remover caracteres não numéricos
    $cpf = preg_replace('/[^0-9]/', '', $cpf);

    // Verificar se tem 11 dígitos
    if (strlen($cpf) !== 11) {
      return false;
    }

    // Verificar sequências inválidas
    if (preg_match('/(\d)\1{10}/', $cpf)) {
      return false;
    }

    // Calcular primeiro dígito verificador
    $soma = 0;
    for ($i = 0; $i < 9; $i++) {
      $soma += $cpf[$i] * (10 - $i);
    }
    $digito1 = ($soma * 10) % 11;
    if ($digito1 === 10) $digito1 = 0;

    // Calcular segundo dígito verificador
    $soma = 0;
    for ($i = 0; $i < 10; $i++) {
      $soma += $cpf[$i] * (11 - $i);
    }
    $digito2 = ($soma * 10) % 11;
    if ($digito2 === 10) $digito2 = 0;

    // Verificar dígitos
    return $cpf[9] == $digito1 && $cpf[10] == $digito2;
  }

  /**
   * Validar email
   */
  public static function email(string $email): bool
  {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
  }

  /**
   * Validar telefone brasileiro
   */
  public static function telefone(string $telefone): bool
  {
    // Remover caracteres não numéricos
    $telefone = preg_replace('/[^0-9]/', '', $telefone);

    // Validar padrões brasileiros
    // Celular: 11 dígitos (11987654321)
    // Fixo: 10 dígitos (1134567890)
    return in_array(strlen($telefone), [10, 11]) &&
      preg_match('/^[1-9][0-9]/', $telefone);
  }

  /**
   * Validar valor monetário
   */
  public static function valorMonetario(mixed $valor): bool
  {
    if ($valor === null || $valor === '') {
      return false;
    }

    if (is_string($valor)) {
      // Aceitar formato brasileiro (vírgula como decimal)
      $valor = str_replace(',', '.', $valor);
      $valor = preg_replace('/[^0-9.]/', '', $valor);
    }

    if (is_numeric($valor)) {
      $valor = (float) $valor;
      return $valor >= 0 && $valor <= 999999999.99;
    }

    return false;
  }

  /**
   * Validar nome
   */
  public static function nome(string $nome): bool
  {
    $nome = trim($nome);

    if (strlen($nome) < 2 || strlen($nome) > 100) {
      return false;
    }

    // Permitir apenas letras, espaços e acentos
    return preg_match('/^[a-záâàãéêèíîìóôòõúûùç\s]+$/ui', $nome) === 1;
  }

  /**
   * Validar data de nascimento
   */
  public static function dataNascimento(string $data): bool
  {
    $timestamp = strtotime($data);

    if ($timestamp === false) {
      return false;
    }

    $hoje = new \DateTime();
    $nascimento = new \DateTime($data);

    // Não pode ser futuro
    if ($nascimento > $hoje) {
      return false;
    }

    // Idade máxima 120 anos
    $idade = $hoje->diff($nascimento)->y;
    return $idade <= 120;
  }

  /**
   * Validar número de conta
   */
  public static function numeroConta(string $numero): bool
  {
    // Formato: 12345-6 (5 dígitos + hífen + 1 dígito)
    return preg_match('/^\d{5}-\d$/', $numero);
  }

  /**
   * Validar tipo de conta
   */
  public static function tipoConta(string $tipo): bool
  {
    return in_array($tipo, ['corrente', 'poupanca']);
  }

  /**
   * Validar tipo de operação
   */
  public static function tipoOperacao(string $tipo): bool
  {
    $tipos = ['saque', 'deposito', 'transferencia', 'pix', 'ted', 'doc'];
    return in_array($tipo, $tipos);
  }

  /**
   * Sanitizar CPF (remover formatação)
   */
  public static function sanitizeCpf(string $cpf): string
  {
    return preg_replace('/[^0-9]/', '', $cpf);
  }

  /**
   * Sanitizar telefone (remover formatação)
   */
  public static function sanitizeTelefone(string $telefone): string
  {
    return preg_replace('/[^0-9]/', '', $telefone);
  }

  /**
   * Sanitizar valor monetário
   */
  public static function sanitizeValor(mixed $valor): float
  {
    if (is_string($valor)) {
      // Remover caracteres não numéricos exceto vírgula e ponto
      $valor = preg_replace('/[^0-9,.]/', '', $valor);

      // Se tem vírgula e ponto, assumir formato brasileiro (1.234,56)
      if (strpos($valor, ',') !== false && strpos($valor, '.') !== false) {
        $valor = str_replace('.', '', $valor); // Remove pontos (milhares)
        $valor = str_replace(',', '.', $valor); // Vírgula vira ponto decimal
      } elseif (strpos($valor, ',') !== false) {
        // Só vírgula, assumir decimal brasileiro
        $valor = str_replace(',', '.', $valor);
      }
    }

    return round((float) $valor, 2);
  }

  /**
   * Validar múltiplos campos
   */
  public static function validarCliente(array $dados): array
  {
    $erros = [];

    // Nome
    if (empty($dados['nome'])) {
      $erros[] = 'Nome é obrigatório';
    } elseif (!self::nome($dados['nome'])) {
      $erros[] = 'Nome inválido';
    }

    // CPF
    if (empty($dados['cpf'])) {
      $erros[] = 'CPF é obrigatório';
    } elseif (!self::cpf($dados['cpf'])) {
      $erros[] = 'CPF inválido';
    }

    // Email
    if (empty($dados['email'])) {
      $erros[] = 'Email é obrigatório';
    } elseif (!self::email($dados['email'])) {
      $erros[] = 'Email inválido';
    }

    // Telefone
    if (empty($dados['telefone'])) {
      $erros[] = 'Telefone é obrigatório';
    } elseif (!self::telefone($dados['telefone'])) {
      $erros[] = 'Telefone inválido';
    }

    // Data de nascimento (opcional)
    if (!empty($dados['data_nascimento']) && !self::dataNascimento($dados['data_nascimento'])) {
      $erros[] = 'Data de nascimento inválida';
    }

    return $erros;
  }

  /**
   * Validar dados de transação
   */
  public static function validarTransacao(array $dados): array
  {
    $erros = [];

    // Valor
    if (empty($dados['valor'])) {
      $erros[] = 'Valor é obrigatório';
    } elseif (!self::valorMonetario($dados['valor'])) {
      $erros[] = 'Valor inválido';
    } elseif ($dados['valor'] <= 0) {
      $erros[] = 'Valor deve ser maior que zero';
    }

    // Tipo de operação
    if (empty($dados['tipo_operacao'])) {
      $erros[] = 'Tipo de operação é obrigatório';
    } elseif (!self::tipoOperacao($dados['tipo_operacao'])) {
      $erros[] = 'Tipo de operação inválido';
    }

    return $erros;
  }
}
