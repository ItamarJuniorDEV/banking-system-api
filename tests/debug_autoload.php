<?php

require_once __DIR__ . '/../autoload.php';

echo "=== DEBUG DO AUTOLOADER ===\n\n";

// Testar caminho do Model
$modelPath = __DIR__ . '/../src/Models/Model.php';
echo "1. Caminho do Model.php: {$modelPath}\n";
echo "2. Arquivo existe? " . (file_exists($modelPath) ? 'SIM' : 'NÃO') . "\n\n";

// Listar arquivos na pasta Models
echo "3. Arquivos na pasta Models:\n";
$modelDir = __DIR__ . '/../src/Models/';
if (is_dir($modelDir)) {
  $files = scandir($modelDir);
  foreach ($files as $file) {
    if ($file !== '.' && $file !== '..') {
      echo "   - {$file}\n";
    }
  }
} else {
  echo "   Pasta não existe!\n";
}

echo "\n4. Tentando carregar classe Model...\n";

// Testar manualmente
if (file_exists($modelPath)) {
  require_once $modelPath;
  echo "✅ Model.php carregado manualmente!\n";

  if (class_exists('SistemaBancario\\Models\\Model')) {
    echo "✅ Classe Model encontrada!\n";
  } else {
    echo "❌ Classe Model não encontrada após require!\n";
  }
} else {
  echo "❌ Arquivo Model.php não existe!\n";
}
