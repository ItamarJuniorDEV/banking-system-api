<?php

echo "=== TESTE DEBUG DA API ===\n";

function testarAPI($url, $metodo = 'GET', $dados = null)
{
  echo "Testando: $metodo $url\n";

  $contexto = [
    'http' => [
      'method' => $metodo,
      'header' => 'Content-Type: application/json',
      'content' => $dados ? json_encode($dados) : null,
      'ignore_errors' => true
    ]
  ];

  $resultado = file_get_contents($url, false, stream_context_create($contexto));

  if ($resultado === false) {
    echo "❌ ERRO: Não conseguiu conectar\n";
    return null;
  }

  echo "Resposta bruta: $resultado\n";

  $json = json_decode($resultado, true);
  if (json_last_error() !== JSON_ERROR_NONE) {
    echo "❌ ERRO JSON: " . json_last_error_msg() . "\n";
    return null;
  }

  return $json;
}

// 1. Teste simples
echo "1. Testando rota inexistente...\n";
$teste1 = testarAPI('http://localhost:8000/teste');

echo "\n2. Testando busca de cliente inexistente...\n";
$teste2 = testarAPI('http://localhost:8000/clientes/999');

echo "\n3. Testando criação de cliente...\n";
$timestamp = time();
$teste3 = testarAPI('http://localhost:8000/clientes', 'POST', [
  'nome' => 'João Teste ' . $timestamp,
  'cpf' => '1234567890' . substr($timestamp, -1),
  'email' => "joao.teste.{$timestamp}@email.com",
  'telefone' => '11999999999'
]);

if ($teste3 && $teste3['status'] === 'success') {
  $clienteId = $teste3['data']['id'];
  echo "✅ Cliente criado com ID: {$clienteId}\n";

  echo "\n4. Testando abertura de conta corrente...\n";
  $teste4 = testarAPI('http://localhost:8000/contas/corrente', 'POST', [
    'cliente_id' => $clienteId,
    'limite_cheque_especial' => 2000
  ]);

  if ($teste4 && $teste4['status'] === 'success') {
    $numeroConta = $teste4['data']['numero_conta'];
    echo "✅ Conta criada: {$numeroConta}\n";

    echo "\n5. Testando consulta de saldo...\n";
    $teste5 = testarAPI("http://localhost:8000/contas/{$numeroConta}/saldo");

    if ($teste5 && $teste5['status'] === 'success') {
      echo "✅ Saldo consultado: " . $teste5['data']['saldo'] . "\n";
    }
  }
}

echo "\n=== FIM DOS TESTES ===\n";
