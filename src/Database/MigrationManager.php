<?php declare(strict_types=1);

namespace WebsiteSQL\Database;

class MigrationManager {
	/**
	 * The database connection instance
	 * 
	 * @var DB
	 * @access private
	 */
    private $db;

	/**
	 * The name of the migrations table
	 * 
	 * @var string
	 * @access private
	 */
    private $migrationsTable = 'migrations';

	/**
	 * The migrations registered with the manager
	 * 
	 * @var array<string, array<string, callable|string>>
	 * @access private
	 */
    private $migrations = [];

    public function __construct($db) {
        $this->db = $db;
        $this->initMigrationsTable();
    }

    private function initMigrationsTable() {
        $connection = $this->db->getConnection();
        $tableExists = $connection->query("SHOW TABLES LIKE '{$this->migrationsTable}'")->rowCount() > 0;
        
        if (!$tableExists) {
            $sql = "CREATE TABLE {$this->migrationsTable} (
                id INT AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255) NOT NULL,
                batch INT NOT NULL,
                executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            $connection->exec($sql);
        }
    }

    public function registerMigration($name, $up, $down = null) {
        $this->migrations[$name] = [
            'up' => $up,
            'down' => $down
        ];
        return $this;
    }

    public function migrate($steps = null) {
        $appliedMigrations = $this->getAppliedMigrations();
        $pendingMigrations = array_diff(array_keys($this->migrations), $appliedMigrations);
        
        if (empty($pendingMigrations)) {
            echo "No pending migrations.\n";
            return;
        }
        
        $batch = $this->getLastBatch() + 1;
        
        // Limit migrations if steps provided
        if ($steps !== null && is_numeric($steps)) {
            $pendingMigrations = array_slice($pendingMigrations, 0, (int) $steps);
        }
        
        foreach ($pendingMigrations as $migration) {
            $this->executeMigration($migration, 'up');
            $this->logMigration($migration, $batch);
            echo "Migrated: {$migration}\n";
        }
    }

    public function rollback($steps = 1) {
        $migrations = $this->getMigrationsForRollback($steps);
        
        if (empty($migrations)) {
            echo "Nothing to rollback.\n";
            return;
        }
        
        foreach ($migrations as $migration) {
            $this->executeMigration($migration, 'down');
            $this->removeMigrationLog($migration);
            echo "Rolled back: {$migration}\n";
        }
    }

    public function reset() {
        $appliedMigrations = $this->getAppliedMigrations();
        $appliedMigrations = array_reverse($appliedMigrations);
        
        foreach ($appliedMigrations as $migration) {
            $this->executeMigration($migration, 'down');
            $this->removeMigrationLog($migration);
            echo "Rolled back: {$migration}\n";
        }
    }

    public function refresh() {
        $this->reset();
        $this->migrate();
    }

    public function status() {
        $appliedMigrations = $this->getAppliedMigrations();
        $result = [];
        
        foreach ($this->migrations as $name => $migration) {
            $result[] = [
                'migration' => $name,
                'status' => in_array($name, $appliedMigrations) ? 'Applied' : 'Pending'
            ];
        }
        
        return $result;
    }

    private function executeMigration($name, $direction = 'up') {
        if (!isset($this->migrations[$name])) {
            throw new \Exception("Migration {$name} does not exist");
        }
        
        $migration = $this->migrations[$name];
        $method = $migration[$direction];
        
        if (is_callable($method)) {
            $method($this->db);
        } else if (is_string($method)) {
            list($class, $method) = explode('@', $method);
            if (!class_exists($class)) {
                throw new \Exception("Migration class {$class} does not exist");
            }
            $instance = new $class();
            $instance->$method($this->db);
        }
    }

    private function logMigration($migration, $batch) {
        $this->db->insert($this->migrationsTable, [
            'migration' => $migration,
            'batch' => $batch
        ]);
    }

    private function removeMigrationLog($migration) {
        $this->db->delete($this->migrationsTable, ['migration' => $migration]);
    }

    private function getAppliedMigrations() {
        $result = $this->db->get($this->migrationsTable, 'migration');
        return array_column($result, 'migration');
    }

    private function getLastBatch() {
        $result = $this->db->query()
            ->table($this->migrationsTable)
            ->select('MAX(batch) as last_batch')
            ->first();
        
        return $result && isset($result['last_batch']) ? $result['last_batch'] : 0;
    }

    private function getMigrationsForRollback($steps) {
        $batches = $this->db->query()
            ->table($this->migrationsTable)
            ->select('DISTINCT batch')
            ->get();
        
        $batches = array_column($batches, 'batch');
        rsort($batches);
        
        $batchesToRollback = array_slice($batches, 0, $steps);
        
        if (empty($batchesToRollback)) {
            return [];
        }
        
        $result = $this->db->query()
            ->table($this->migrationsTable)
            ->select('migration')
            ->where('batch', 'IN', $batchesToRollback)
            ->get();
        
        return array_column($result, 'migration');
    }
}