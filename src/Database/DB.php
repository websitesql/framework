<?php declare(strict_types=1);

namespace WebsiteSQL\Database;

use PDO;

class DB {
    private $connection = null;
    private $config = [];
    private $migrationManager = null;
    
    public function config(array $config) {
        $this->config = $config;
        return $this;
    }
    
    public function connect() {
        if ($this->connection === null) {
            $dsn = "mysql:host={$this->config['host']};dbname={$this->config['database']};charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->connection = new PDO($dsn, $this->config['user'], $this->config['password'], $options);
        }
        
        return $this->connection;
    }
    
    public function getConnection() {
        return $this->connect();
    }
    
    public function query() {
        return new Query($this);
    }
    
    // Migration methods
    public function migrations() {
        if ($this->migrationManager === null) {
            $this->migrationManager = new MigrationManager($this);
        }
        return $this->migrationManager;
    }
    
    public function migration($name, $up, $down = null) {
        return $this->migrations()->registerMigration($name, $up, $down);
    }
    
    public function migrate($steps = null) {
        return $this->migrations()->migrate($steps);
    }
    
    public function rollback($steps = 1) {
        return $this->migrations()->rollback($steps);
    }
    
    public function reset() {
        return $this->migrations()->reset();
    }
    
    public function refresh() {
        return $this->migrations()->refresh();
    }
    
    public function migrationStatus() {
        return $this->migrations()->status();
    }
    
    // Database manipulation methods for migrations
    public function create($table, $columns) {
        $sql = "CREATE TABLE {$table} (";
        $columnDefinitions = [];
        
        foreach ($columns as $name => $definition) {
            $columnDefinitions[] = "{$name} " . implode(' ', $definition);
        }
        
        $sql .= implode(', ', $columnDefinitions) . ")";
        
        $this->getConnection()->exec($sql);
        return $this;
    }
    
    public function drop($table) {
        $this->getConnection()->exec("DROP TABLE IF EXISTS {$table}");
        return $this;
    }
    
    public function addColumn($table, $column, $definition) {
        $sql = "ALTER TABLE {$table} ADD COLUMN {$column} " . implode(' ', $definition);
        $this->getConnection()->exec($sql);
        return $this;
    }
    
    public function dropColumn($table, $column) {
        $sql = "ALTER TABLE {$table} DROP COLUMN {$column}";
        $this->getConnection()->exec($sql);
        return $this;
    }
    
    public function addIndex($table, $name, $columns, $type = '') {
        if (is_array($columns)) {
            $columns = implode(', ', $columns);
        }
        
        $sql = "CREATE {$type} INDEX {$name} ON {$table} ({$columns})";
        $this->getConnection()->exec($sql);
        return $this;
    }
    
    public function dropIndex($table, $name) {
        $sql = "DROP INDEX {$name} ON {$table}";
        $this->getConnection()->exec($sql);
        return $this;
    }
    
    // Basic CRUD methods
    public function get($table, $columns = '*', $where = []) {
        return $this->query()->table($table)->select($columns)->where($where)->get();
    }
    
    public function insert($table, $data) {
        return $this->query()->table($table)->insert($data);
    }
    
    public function update($table, $data, $where) {
        return $this->query()->table($table)->update($data)->where($where)->execute();
    }
    
    public function delete($table, $where) {
        return $this->query()->table($table)->delete()->where($where)->execute();
    }
}