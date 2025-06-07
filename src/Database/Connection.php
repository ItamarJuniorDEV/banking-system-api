<?php

namespace SistemaBancario\Database;

use PDO;
use PDOException;
use Exception;

/**
 * CLASSE DE CONEXÃO COM BANCO DE DADOS
 * 
 * Gerencia a conexão PDO com SQLite usando padrão Singleton.
 * Similar ao DB facade do Laravel.
 */
class Connection
{
    private static ?PDO $instance = null;
    private static array $config = [];

    /**
     * Obter instância da conexão (Singleton)
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            self::connect();
        }

        return self::$instance;
    }

    /**
     * Estabelecer conexão com o banco
     */
    private static function connect(): void
    {
        try {
            // Carregar configurações
            self::loadConfig();

            $config = self::$config['connections']['sqlite'];

            $dbPath = $config['database'];
            $dbDir = dirname($dbPath);

            if (!is_dir($dbDir)) {
                mkdir($dbDir, 0755, true);
            }

            $dsn = "sqlite:" . $dbPath;

            self::$instance = new PDO($dsn, null, null, $config['options']);

            self::applyPragmaSettings($config['pragma']);

            //echo "✅ Conexão com banco estabelecida: {$dbPath}\n";

        } catch (PDOException $e) {
            throw new Exception("Erro ao conectar com banco: " . $e->getMessage());
        }
    }

    /**
     * Carregar configurações do banco
     */
    private static function loadConfig(): void
    {
        $configPath = __DIR__ . '/../../config/database.php';

        if (!file_exists($configPath)) {
            throw new Exception("Arquivo de configuração do banco não encontrado");
        }

        self::$config = require $configPath;
    }

    /**
     * Aplicar configurações PRAGMA do SQLite
     */
    private static function applyPragmaSettings(array $pragmas): void
    {
        foreach ($pragmas as $pragma => $value) {
            $sql = "PRAGMA {$pragma} = {$value}";
            self::$instance->exec($sql);
        }
    }

    /**
     * Executar query simples
     */
    public static function query(string $sql, array $params = []): \PDOStatement
    {
        $pdo = self::getInstance();

        if (empty($params)) {
            return $pdo->query($sql);
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt;
    }

    /**
     * Executar INSERT e retornar último ID
     */
    public static function insert(string $sql, array $params = []): int
    {
        $stmt = self::query($sql, $params);
        return (int) self::getInstance()->lastInsertId();
    }

    /**
     * Executar UPDATE/DELETE e retornar linhas afetadas
     */
    public static function execute(string $sql, array $params = []): int
    {
        $stmt = self::query($sql, $params);
        return $stmt->rowCount();
    }

    /**
     * Buscar um registro
     */
    public static function fetchOne(string $sql, array $params = []): ?array
    {
        $stmt = self::query($sql, $params);
        $result = $stmt->fetch();

        return $result === false ? null : $result;
    }

    /**
     * Buscar múltiplos registros
     */
    public static function fetchAll(string $sql, array $params = []): array
    {
        $stmt = self::query($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Iniciar transação
     */
    public static function beginTransaction(): bool
    {
        return self::getInstance()->beginTransaction();
    }

    /**
     * Confirmar transação
     */
    public static function commit(): bool
    {
        return self::getInstance()->commit();
    }

    /**
     * Cancelar transação
     */
    public static function rollback(): bool
    {
        return self::getInstance()->rollback();
    }

    /**
     * Verificar se está em transação
     */
    public static function inTransaction(): bool
    {
        return self::getInstance()->inTransaction();
    }

    /**
     * Fechar conexão
     */
    public static function close(): void
    {
        self::$instance = null;
    }

    /**
     * Obter informações do banco
     */
    public static function getInfo(): array
    {
        $pdo = self::getInstance();

        return [
            'driver' => $pdo->getAttribute(PDO::ATTR_DRIVER_NAME),
            'version' => $pdo->getAttribute(PDO::ATTR_SERVER_VERSION),
            'client_version' => $pdo->getAttribute(PDO::ATTR_CLIENT_VERSION),
            'database_file' => self::$config['connections']['sqlite']['database'],
            'file_size' => file_exists(self::$config['connections']['sqlite']['database'])
                ? filesize(self::$config['connections']['sqlite']['database'])
                : 0,
        ];
    }
}
