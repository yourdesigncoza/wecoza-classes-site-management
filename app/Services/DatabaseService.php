<?php

namespace WeCozaSiteManagement\Services;

/**
 * Database Service for PostgreSQL connectivity
 *
 * @package WeCozaSiteManagement\Services
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class DatabaseService {
    
    /**
     * Singleton instance
     *
     * @var DatabaseService
     */
    private static $instance = null;
    
    /**
     * PDO connection
     *
     * @var \PDO
     */
    private $connection = null;
    
    /**
     * Database configuration
     *
     * @var array
     */
    private $config;
    
    /**
     * Transaction state
     *
     * @var bool
     */
    private $in_transaction = false;
    
    /**
     * Private constructor for singleton
     */
    private function __construct() {
        $this->config = \WeCozaSiteManagement\config('app')['database']['postgresql'];
        $this->connect();
    }
    
    /**
     * Get singleton instance
     *
     * @return DatabaseService
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Establish database connection
     *
     * @throws \Exception
     */
    private function connect() {
        try {
            $dsn = sprintf(
                'pgsql:host=%s;port=%s;dbname=%s',
                $this->config['host'],
                $this->config['port'],
                $this->config['dbname']
            );
            
            $options = [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->connection = new \PDO(
                $dsn,
                $this->config['user'],
                $this->config['password'],
                $options
            );
            
            // \WeCozaSiteManagement\plugin_log('PostgreSQL connection established successfully');
            
        } catch (\PDOException $e) {
            \WeCozaSiteManagement\plugin_log('Database connection failed: ' . $e->getMessage(), 'error');
            throw new \Exception('Database connection failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Execute a query with parameters
     *
     * @param string $sql SQL query
     * @param array $params Parameters for prepared statement
     * @return \PDOStatement
     * @throws \Exception
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (\PDOException $e) {
            \WeCozaSiteManagement\plugin_log('Query failed: ' . $e->getMessage() . ' SQL: ' . $sql, 'error');
            throw new \Exception('Query failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Fetch a single row
     *
     * @param string $sql SQL query
     * @param array $params Parameters for prepared statement
     * @return array|false
     */
    public function fetchRow($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }
    
    /**
     * Fetch all rows
     *
     * @param string $sql SQL query
     * @param array $params Parameters for prepared statement
     * @return array
     */
    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get the last inserted ID
     *
     * @param string $sequence_name PostgreSQL sequence name
     * @return string
     */
    public function lastInsertId($sequence_name = null) {
        return $this->connection->lastInsertId($sequence_name);
    }
    
    /**
     * Begin transaction
     *
     * @return bool
     */
    public function beginTransaction() {
        if (!$this->in_transaction) {
            $this->in_transaction = $this->connection->beginTransaction();
        }
        return $this->in_transaction;
    }
    
    /**
     * Commit transaction
     *
     * @return bool
     */
    public function commit() {
        if ($this->in_transaction) {
            $result = $this->connection->commit();
            $this->in_transaction = false;
            return $result;
        }
        return false;
    }
    
    /**
     * Rollback transaction
     *
     * @return bool
     */
    public function rollback() {
        if ($this->in_transaction) {
            $result = $this->connection->rollBack();
            $this->in_transaction = false;
            return $result;
        }
        return false;
    }
    
    /**
     * Check if in transaction
     *
     * @return bool
     */
    public function inTransaction() {
        return $this->in_transaction;
    }
    
    /**
     * Get row count for last statement
     *
     * @param \PDOStatement $stmt
     * @return int
     */
    public function rowCount(\PDOStatement $stmt) {
        return $stmt->rowCount();
    }
    
    /**
     * Escape string for LIKE queries
     *
     * @param string $string
     * @return string
     */
    public function escapeLike($string) {
        return str_replace(['%', '_'], ['\\%', '\\_'], $string);
    }
    
    /**
     * Test database connection
     *
     * @return bool
     */
    public function testConnection() {
        try {
            $this->query('SELECT 1');
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Close connection
     */
    public function close() {
        $this->connection = null;
        self::$instance = null;
    }
    
    /**
     * Prevent cloning
     */
    private function __clone() {}
    
    /**
     * Prevent unserialization
     */
    public function __wakeup() {
        throw new \Exception("Cannot unserialize singleton");
    }
}
