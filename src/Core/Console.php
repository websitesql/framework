<?php declare(strict_types=1);

namespace WebsiteSQL\Framework\Core;

use WebsiteSQL\Framework\Console\Command;

class Console
{
    /**
     * Command handler instance
     *
     * @var Command
     */
    protected Command $command;

    /**
     * Constructor
	 * 
	 * @param array $argv Command line arguments
     */
    public function __construct(array $argv = [])
    {
        $this->command = new Command();

		// Run the command line interface
		$this->run($argv);
    }

    /**
     * Run the console application
     *
     * @param array $args Command line arguments
     * @return void
     */
    public function run(array $args = []): void
    {
        // Display welcome message
        $this->displayWelcome();

        // Get the command
        $command = $args[1] ?? 'help';
        
        // Get additional arguments
        $commandArgs = array_slice($args, 2);

        // Run the command
        $this->command->run($command, $commandArgs);
    }

    /**
     * Display welcome message
     *
     * @return void
     */
    protected function displayWelcome(): void
    {
        echo "Welcome to WebsiteSQL Framework!\n";
        echo "-------------------------------\n\n";
    }
}