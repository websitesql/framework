<?php declare(strict_types=1);

namespace WebsiteSQL\Framework\Database;

use WebsiteSQL\Framework\Database\Providers\MigrationProvider;
use Medoo\Medoo;
use Exception;

class Database extends Medoo
{
    /**
     * This string will hold the path to the Migrations directory
     *
     * @var string
     */
    private string $migrationsPath;

    /**
     * Constructor
     *
     * @param array $options
     * @param string $migrationsPath
     * @return void
     * @throws Exception
     */
    public function __construct(array $options, string $migrationsPath)
    {
        // Connect to database using parent constructor
        try {
            parent::__construct($options);
        } catch (Exception $error) {
            throw new Exception('Database connection error: ' . $error->getMessage());
        }

        // Set the migrations path
        $this->migrationsPath = $migrationsPath;
    }

    /**
     * Get the MigrationProvider class
     * 
     * @return MigrationProvider
     */
    public function migrations(): MigrationProvider
    {
        return new MigrationProvider($this, $this->migrationsPath);
    }
}