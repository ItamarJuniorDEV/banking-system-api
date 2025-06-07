<?php

namespace SistemaBancario\Utils;

class Response
{
  public static function json(mixed $data, int $status = 200): void
  {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');

    $response = [
      'status' => $status >= 200 && $status < 300 ? 'success' : 'error',
      'data' => $data,
      'timestamp' => date('Y-m-d H:i:s')
    ];

    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
  }

  public static function error(string $message, int $status = 400): void
  {
    self::json(['error' => $message], $status);
  }

  public static function success(mixed $data): void
  {
    self::json($data, 200);
  }
}
