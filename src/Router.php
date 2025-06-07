<?php

namespace SistemaBancario;

use SistemaBancario\Utils\Response;

class Router
{
  private array $routes = [];
  private string $basePath = '';

  public function __construct(string $basePath = '')
  {
    $this->basePath = rtrim($basePath, '/');
  }

  public function post(string $path, callable $handler): void
  {
    $this->addRoute('POST', $path, $handler);
  }

  public function get(string $path, callable $handler): void
  {
    $this->addRoute('GET', $path, $handler);
  }

  public function put(string $path, callable $handler): void
  {
    $this->addRoute('PUT', $path, $handler);
  }

  public function delete(string $path, callable $handler): void
  {
    $this->addRoute('DELETE', $path, $handler);
  }

  private function addRoute(string $method, string $path, callable $handler): void
  {
    $this->routes[] = [
      'method' => $method,
      'path' => $this->basePath . $path,
      'handler' => $handler
    ];
  }

  public function dispatch(): void
  {
    $method = $_SERVER['REQUEST_METHOD'];
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    foreach ($this->routes as $route) {
      if ($route['method'] === $method && $this->matchPath($route['path'], $uri)) {
        $params = $this->extractParams($route['path'], $uri);
        call_user_func_array($route['handler'], $params);
        return;
      }
    }

    Response::error('Rota não encontrada', 404);
  }

  private function matchPath(string $pattern, string $uri): bool
  {
    $pattern = preg_replace('/\{[^}]+\}/', '([^/]+)', $pattern);
    return preg_match('#^' . $pattern . '$#', $uri);
  }

  private function extractParams(string $pattern, string $uri): array
  {
    $pattern = preg_replace('/\{[^}]+\}/', '([^/]+)', $pattern);
    preg_match('#^' . $pattern . '$#', $uri, $matches);
    return array_slice($matches, 1);
  }
}
