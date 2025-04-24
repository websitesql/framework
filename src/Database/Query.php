<?php declare(strict_types=1);

namespace WebsiteSQL\Database;

class Query {
    private $db;
    private $table;
    private $selects = [];
    private $wheres = [];
    private $bindings = [];
    private $type = 'select';
    private $data = [];
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function table($table) {
        $this->table = $table;
        return $this;
    }
    
    public function select($columns = '*') {
        $this->type = 'select';
        
        if (is_string($columns) && $columns !== '*') {
            $this->selects = explode(',', $columns);
        } elseif (is_array($columns)) {
            $this->selects = $columns;
        } else {
            $this->selects = ['*'];
        }
        
        return $this;
    }
    
    public function insert($data) {
        $this->type = 'insert';
        $this->data = $data;
        return $this->execute();
    }
    
    public function update($data) {
        $this->type = 'update';
        $this->data = $data;
        return $this;
    }
    
    public function delete() {
        $this->type = 'delete';
        return $this;
    }
    
    public function where($column, $operator = null, $value = null) {
        if (is_array($column)) {
            foreach ($column as $key => $value) {
                $this->wheres[] = [$key, '=', $value];
                $this->bindings[] = $value;
            }
        } else {
            if ($value === null) {
                $value = $operator;
                $operator = '=';
            }
            $this->wheres[] = [$column, $operator, $value];
            $this->bindings[] = $value;
        }
        
        return $this;
    }
    
    public function get() {
        $stmt = $this->getConnection()->prepare($this->toSql());
        $stmt->execute($this->bindings);
        return $stmt->fetchAll();
    }
    
    public function first() {
        $stmt = $this->getConnection()->prepare($this->toSql());
        $stmt->execute($this->bindings);
        return $stmt->fetch();
    }
    
    public function execute() {
        $stmt = $this->getConnection()->prepare($this->toSql());
        $stmt->execute($this->bindings);
        
        if ($this->type === 'insert') {
            return $this->getConnection()->lastInsertId();
        }
        
        return $stmt->rowCount();
    }
    
    public function toSql() {
        switch ($this->type) {
            case 'select':
                return $this->buildSelectSql();
            case 'insert':
                return $this->buildInsertSql();
            case 'update':
                return $this->buildUpdateSql();
            case 'delete':
                return $this->buildDeleteSql();
        }
        
        return '';
    }
    
    private function buildSelectSql() {
        $sql = "SELECT " . implode(', ', $this->selects) . " FROM {$this->table}";
        
        if (!empty($this->wheres)) {
            $sql .= " WHERE " . $this->buildWhereClauses();
        }
        
        return $sql;
    }
    
    private function buildInsertSql() {
        $columns = array_keys($this->data);
        $values = array_fill(0, count($columns), '?');
        
        $this->bindings = array_values($this->data);
        
        return "INSERT INTO {$this->table} (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $values) . ")";
    }
    
    private function buildUpdateSql() {
        $sets = [];
        $this->bindings = [];
        
        foreach ($this->data as $column => $value) {
            $sets[] = "{$column} = ?";
            $this->bindings[] = $value;
        }
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $sets);
        
        if (!empty($this->wheres)) {
            $sql .= " WHERE " . $this->buildWhereClauses();
        }
        
        return $sql;
    }
    
    private function buildDeleteSql() {
        $sql = "DELETE FROM {$this->table}";
        
        if (!empty($this->wheres)) {
            $sql .= " WHERE " . $this->buildWhereClauses();
        }
        
        return $sql;
    }
    
    private function buildWhereClauses() {
        $clauses = [];
        
        foreach ($this->wheres as $where) {
            $clauses[] = "{$where[0]} {$where[1]} ?";
        }
        
        return implode(' AND ', $clauses);
    }
    
    private function getConnection() {
        return $this->db->getConnection();
    }
}